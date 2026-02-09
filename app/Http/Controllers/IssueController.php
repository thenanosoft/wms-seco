<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\IssueLine;
use App\Models\IssueReturnLine;
use App\Models\IssueReturnTransaction;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\Item;
use App\Models\Group;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $q = Issue::query()->withCount('lines')->orderByDesc('issue_date')->orderByDesc('id');

        if ($request->filled('from')) $q->whereDate('issue_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('issue_date', '<=', $request->to);
        if ($request->filled('issued_to')) $q->where('issued_to', 'like', '%' . $request->issued_to . '%');
        if ($request->filled('reference')) $q->where('reference_no', 'like', '%' . $request->reference . '%');

        $issues = $q->paginate(25)->withQueryString();

        return view('issue.index', compact('issues'));
    }

    public function create()
    {
        $groups = Group::query()->orderBy('group_code')->get();
        // IMPORTANT: Issue screen needs live available stock + the NEXT FIFO batch price for display.
        // Actual issuing is still done by FIFO in StockService::issueItemFIFO().
        // We compute availability from stock_batches.qty_available (source of truth).

        $availableStockSub = StockBatch::query()
            ->selectRaw('COALESCE(SUM(qty_available),0)')
            ->whereColumn('stock_batches.item_id', 'items.id');

        $nextBatchPriceSub = StockBatch::query()
            ->select('unit_price')
            ->whereColumn('stock_batches.item_id', 'items.id')
            ->where('qty_available', '>', 0)
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->limit(1);

        $items = Item::query()
            ->select('items.*')
            ->selectSub($availableStockSub, 'available_stock')
            ->selectSub($nextBatchPriceSub, 'fifo_next_price')
            ->orderBy('items.item_code')
            ->get()
            ->map(function ($it) {
                $it->available_stock = (int)($it->available_stock ?? 0);
                $it->fifo_price_pending = $it->fifo_next_price === null;
                // keep as numeric for JS calculations
                $it->fifo_next_price = $it->fifo_next_price === null ? 0 : (float)$it->fifo_next_price;
                return $it;
            });

        // Provide FIFO batches preview to frontend (for accurate estimated totals on entry)
        $batches = StockBatch::query()
            ->where('qty_available', '>', 0)
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->get(['id','item_id','qty_available','unit_price','purchase_date']);

        $batchesByItem = $batches->groupBy('item_id');
        $items = $items->map(function ($it) use ($batchesByItem) {
            $list = $batchesByItem->get($it->id, collect());
            $it->fifo_batches = $list->map(function ($b) {
                return [
                    'id' => $b->id,
                    'qty_available' => (int)$b->qty_available,
                    'unit_price' => $b->unit_price === null ? 0 : (float)$b->unit_price,
                    'purchase_date' => (string)$b->purchase_date,
                ];
            })->values();
            return $it;
        });

        return view('issue.create', compact('groups', 'items'));
    }

    public function store(Request $request, StockService $stock)
    {
        $data = $request->validate([
            'issue_date' => ['required','date'],
            'issued_to' => ['nullable','string','max:120'],
            'reference_no' => ['nullable','string','max:120'],
            'notes' => ['nullable','string','max:255'],
            'lines' => ['required','array','min:1'],
            'lines.*.item_id' => ['required','integer','exists:items,id'],
            'lines.*.quantity' => ['required','integer','min:1'],
            'lines.*.specification' => ['nullable','string','max:255'],
        ]);

        return DB::transaction(function () use ($data, $stock) {
            $issue = Issue::create([
                'issue_date' => $data['issue_date'],
                'issued_to' => $data['issued_to'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['lines'] as $line) {
                $stock->issueItemFIFO(
                    issue: $issue,
                    itemId: (int)$line['item_id'],
                    qty: (int)$line['quantity'],
                    specification: $line['specification'] ?? null,
                );
            }

            return redirect()->route('issues.show', $issue)->with('status', 'Issue created successfully.');
        });
    }

    public function show(Issue $issue)
    {
        $issue->load(['lines.item.group']);
        $returned = \App\Models\IssueReturnLine::query()
            ->selectRaw('issue_line_id, SUM(quantity) as returned_qty')
            ->whereIn('issue_line_id', $issue->lines->pluck('id')->all())
            ->groupBy('issue_line_id')
            ->pluck('returned_qty', 'issue_line_id');

        // Prepare view-safe data (avoid Blade-side PHP blocks, prevent parse errors)
        $lines = $issue->lines->map(function ($line) use ($returned) {
            $retQty = (int)($returned[$line->id] ?? 0);
            $remQty = (int)$line->quantity - $retQty;
            if ($remQty < 0) $remQty = 0;

            $price = $line->issue_price;
            $netTotal = ($price === null) ? 0 : ($remQty * (int)$price);

            return (object)[
                'id' => $line->id,
                'item_id' => $line->item_id,
                'item_code' => optional($line->item)->item_code,
                'item_name' => optional($line->item)->name,
                'quantity' => (int)$line->quantity,
                'returned_qty' => $retQty,
                'remaining_qty' => $remQty,
                'issue_price' => $price,
                'specification' => $line->specification,
                'net_line_total' => (int)$netTotal,
            ];
        });

        $totals = (object)[
            'total_qty' => (int)$lines->sum('quantity'),
            'total_returned' => (int)$lines->sum('returned_qty'),
            'total_remaining' => (int)$lines->sum('remaining_qty'),
            'total_net_amount' => (int)$lines->sum('net_line_total'),
        ];

        return view('issue.show', compact('issue', 'returned', 'lines', 'totals'));
    }

    /**
     * Admin only: edit issue (except price). You can:
     * - update header fields
     * - decrease quantities
     * - remove lines if no return exists on that line
     */
    public function edit(Issue $issue)
    {
        $issue->load(['lines.item.group']);

        // Needed for "Add new item" rows on edit screen
        $groups = \App\Models\Group::query()->orderBy('group_code')->get();
        $items  = \App\Models\Item::query()->orderBy('item_code')->get();

        $returned = IssueReturnLine::query()
            ->selectRaw('issue_line_id, SUM(quantity) as returned_qty')
            ->whereIn('issue_line_id', $issue->lines->pluck('id')->all())
            ->groupBy('issue_line_id')
            ->pluck('returned_qty', 'issue_line_id');

        return view('issue.edit', compact('issue', 'returned', 'groups', 'items'));
    }

    public function update(Request $request, Issue $issue, StockService $stock)
    {
        $data = $request->validate([
            'issue_date' => ['required','date'],
            'issued_to' => ['nullable','string','max:120'],
            'reference_no' => ['nullable','string','max:120'],
            'notes' => ['nullable','string','max:255'],
            'lines' => ['required','array'],
            'lines.*.id' => ['required','integer','exists:issue_lines,id'],
            'lines.*.new_quantity' => ['nullable','integer','min:0'],
            'lines.*.remove' => ['nullable','boolean'],

            // New lines added during edit
            'new_lines' => ['nullable','array'],
            'new_lines.*.group_id' => ['required_with:new_lines','integer','exists:groups,id'],
            'new_lines.*.item_id' => ['required_with:new_lines','integer','exists:items,id'],
            'new_lines.*.specification' => ['nullable','string','max:255'],
            'new_lines.*.quantity' => ['required_with:new_lines','integer','min:0'],
        ]);

        $issue->load('lines');

        return DB::transaction(function () use ($issue, $data, $stock) {
            // Map returned qty per line (cannot reduce below this)
            $returned = IssueReturnLine::query()
                ->selectRaw('issue_line_id, SUM(quantity) as returned_qty')
                ->whereIn('issue_line_id', $issue->lines->pluck('id')->all())
                ->groupBy('issue_line_id')
                ->pluck('returned_qty', 'issue_line_id');

            foreach ($data['lines'] as $row) {
                $line = $issue->lines->firstWhere('id', (int)$row['id']);
                if (!$line) continue;

                $retQty = (int)($returned[$line->id] ?? 0);
                $remove = (bool)($row['remove'] ?? false);
                $newQty = isset($row['new_quantity']) ? (int)$row['new_quantity'] : $line->quantity;

                if ($remove) {
                    if ($retQty > 0) {
                        return back()->withErrors(['edit' => 'Cannot remove a line that already has returns.']);
                    }
                    // Reverse entire issued qty back to same batch
                    $batch = StockBatch::where('purchase_line_id', $line->purchase_line_id)->first();
                    if ($batch) {
                        $batch->qty_available = (int)$batch->qty_available + (int)$line->quantity;
                        $batch->save();
                    }
                    // Delete matching ledger row for this line only (do not rebuild whole issue ledger)
                    \App\Models\StockLedger::query()
                        ->where('ref_table', 'issues')
                        ->where('ref_id', $issue->id)
                        ->where('ref_line_id', $line->id)
                        ->delete();

                    $line->delete();
                    continue;
                }
                if ($newQty < $retQty) {
                    return back()->withErrors(['edit' => 'New quantity cannot be less than already returned quantity.']);
                }

                $oldQty = (int)$line->quantity;
                $diff = $newQty - $oldQty;

                if ($diff < 0) {
                    // Decrease: reverse qty back to same batch
                    $giveBack = abs($diff);
                    $batch = StockBatch::where('purchase_line_id', $line->purchase_line_id)->lockForUpdate()->first();
                    if ($batch) {
                        $batch->qty_available = (int)$batch->qty_available + $giveBack;
                        $batch->save();
                    }

                    $line->quantity = $newQty;
                    $line->line_total = $newQty * (int)$line->issue_price;
                    $line->save();

                    // Update ledger row in-place
                    \App\Models\StockLedger::query()
                        ->where('ref_table', 'issues')
                        ->where('ref_id', $issue->id)
                        ->where('ref_line_id', $line->id)
                        ->update([
                            'txn_date' => $issue->issue_date,
                            'qty_out' => (int)$line->quantity,
                            'unit_price' => (int)$line->issue_price,
                            'specification_snapshot' => $line->specification,
                        ]);
                    continue;
                }

                if ($diff > 0) {
                    // Increase: first try from same batch (keeps same price), then FIFO for remaining.
                    $remaining = $diff;

                    $batch = StockBatch::where('purchase_line_id', $line->purchase_line_id)->lockForUpdate()->first();
                    if ($batch && (int)$batch->qty_available > 0) {
                        $takeSame = min($remaining, (int)$batch->qty_available);
                        if ($takeSame > 0) {
                            $batch->qty_available = (int)$batch->qty_available - $takeSame;
                            $batch->save();

                            $line->quantity = $oldQty + $takeSame;
                            $line->line_total = (int)$line->quantity * (int)$line->issue_price;
                            $line->save();

                            \App\Models\StockLedger::query()
                                ->where('ref_table', 'issues')
                                ->where('ref_id', $issue->id)
                                ->where('ref_line_id', $line->id)
                                ->update([
                                    'txn_date' => $issue->issue_date,
                                    'qty_out' => (int)$line->quantity,
                                    'unit_price' => (int)$line->issue_price,
                                    'specification_snapshot' => $line->specification,
                                ]);

                            $remaining -= $takeSame;
                        }
                    }

                    if ($remaining > 0) {
                        // Allocate remaining as NEW issue lines using FIFO
                        $stock->issueItemFIFO(
                            issue: $issue,
                            itemId: (int)$line->item_id,
                            qty: (int)$remaining,
                            specification: $line->specification,
                        );
                    }
                }

            }

            // Add new items added during edit (FIFO allocation creates new issue lines + ledgers)
            foreach (($data['new_lines'] ?? []) as $nl) {
                $qty = (int)($nl['quantity'] ?? 0);
                if ($qty <= 0) continue;

                $stock->issueItemFIFO(
                    issue: $issue,
                    itemId: (int)$nl['item_id'],
                    qty: $qty,
                    specification: $nl['specification'] ?? null,
                );
            }

            // Update issue header
            $issue->update([
                'issue_date' => $data['issue_date'],
                'issued_to' => $data['issued_to'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Keep timeline stable: update existing ledger rows in-place (do not delete/recreate).
            \App\Models\StockLedger::query()
                ->where('ref_table', 'issues')
                ->where('ref_id', $issue->id)
                ->update(['txn_date' => $issue->issue_date]);

            // Ensure every remaining line has a ledger row and stays in sync.
            $issue->load('lines');
            foreach ($issue->lines as $l) {
                $ledger = \App\Models\StockLedger::query()
                    ->where('ref_table', 'issues')
                    ->where('ref_id', $issue->id)
                    ->where('ref_line_id', $l->id)
                    ->first();

                if (!$ledger) {
                    $stock->addIssueLedgerEntry([
                        'txn_date' => $issue->issue_date,
                        'ref_id' => $issue->id,
                        'ref_line_id' => $l->id,
                        'item_id' => $l->item_id,
                        'qty_out' => (int)$l->quantity,
                        'unit_price' => (int)$l->issue_price,
                        'specification_snapshot' => $l->specification,
                        'created_by' => $issue->created_by,
                    ]);
                } else {
                    $ledger->update([
                        'txn_date' => $issue->issue_date,
                        'item_id' => $l->item_id,
                        'qty_out' => (int)$l->quantity,
                        'unit_price' => (int)$l->issue_price,
                        'specification_snapshot' => $l->specification,
                    ]);
                }
            }

            return redirect()->route('issues.show', $issue)->with('status', 'Issue updated successfully.');
        });
    }

    public function destroy(Issue $issue)
    {
        $issue->load('lines');

        return DB::transaction(function () use ($issue) {
            $lineIds = $issue->lines->pluck('id')->all();

            // Reverse issue returns first (remove returned stock)
            $returnLines = IssueReturnLine::query()->whereIn('issue_line_id', $lineIds)->get();
            foreach ($returnLines as $rl) {
                $line = $issue->lines->firstWhere('id', $rl->issue_line_id);
                if ($line) {
                    $batch = StockBatch::where('purchase_line_id', $line->purchase_line_id)->first();
                    if ($batch) {
                        $batch->qty_available = (int)$batch->qty_available - (int)$rl->quantity;
                        if ($batch->qty_available < 0) $batch->qty_available = 0;
                        $batch->save();
                    }
                }
            }

            // Reverse issue (add stock back)
            foreach ($issue->lines as $l) {
                $batch = StockBatch::where('purchase_line_id', $l->purchase_line_id)->first();
                if ($batch) {
                    $batch->qty_available = (int)$batch->qty_available + (int)$l->quantity;
                    $batch->save();
                }
            }

            // Delete return transactions + ledger
            $returnTxnIds = $returnLines->pluck('issue_return_transaction_id')->unique()->values()->all();
            if (!empty($returnTxnIds)) {
                IssueReturnLine::query()->whereIn('issue_return_transaction_id', $returnTxnIds)->delete();
                IssueReturnTransaction::query()->whereIn('id', $returnTxnIds)->delete();
                StockLedger::query()->where('ref_table', 'issue_return_transactions')->whereIn('ref_id', $returnTxnIds)->delete();
            }

            StockLedger::query()->where('ref_table', 'issues')->where('ref_id', $issue->id)->delete();

            IssueLine::query()->where('issue_id', $issue->id)->delete();
            $issue->delete();

            return redirect()->route('issues.index')->with('status', 'Issue deleted successfully.');
        });
    }
}