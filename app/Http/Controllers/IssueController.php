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
                $it->available_stock = round((float)($it->available_stock ?? 0), 4);
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
                    'qty_available' => round((float)$b->qty_available, 4),
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
            'lines.*.quantity' => ['required','numeric','min:0.0001', 'regex:/^\d+(\.\d{1,4})?$/'],
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
                    qty: round((float)$line['quantity'], 4),
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
            $retQty = (float)($returned[$line->id] ?? 0);
            $remQty = round((float)$line->quantity - $retQty, 4);
            if ($remQty < 0) $remQty = 0;

            $price = $line->issue_price;
            $netTotal = ($price === null) ? 0 : round($remQty * (float)$price, 4);

            return (object)[
                'id' => $line->id,
                'item_id' => $line->item_id,
                'item_code' => optional($line->item)->item_code,
                'item_name' => optional($line->item)->name,
                'quantity' => (float)$line->quantity,
                'returned_qty' => $retQty,
                'remaining_qty' => $remQty,
                'issue_price' => $price,
                'specification' => $line->specification,
                'net_line_total' => $netTotal,
            ];
        });

        $totals = (object)[
            'total_qty' => round($lines->sum('quantity'), 4),
            'total_returned' => round($lines->sum('returned_qty'), 4),
            'total_remaining' => round($lines->sum('remaining_qty'), 4),
            'total_net_amount' => round($lines->sum('net_line_total'), 4),
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
            'lines.*.new_quantity' => ['nullable','numeric','min:0', 'regex:/^\d+(\.\d{1,4})?$/'],
            'lines.*.remove' => ['nullable','boolean'],

            // New lines added during edit
            'new_lines' => ['nullable','array'],
            'new_lines.*.group_id' => ['required_with:new_lines','integer','exists:groups,id'],
            'new_lines.*.item_id' => ['required_with:new_lines','integer','exists:items,id'],
            'new_lines.*.specification' => ['nullable','string','max:255'],
            'new_lines.*.quantity' => ['required_with:new_lines','numeric','min:0', 'regex:/^\d+(\.\d{1,4})?$/'],
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

                $retQty = (float)($returned[$line->id] ?? 0);
                $remove = (bool)($row['remove'] ?? false);
                $newQty = isset($row['new_quantity']) ? round((float)$row['new_quantity'], 4) : (float)$line->quantity;

                if ($remove) {
                    if ($retQty > 0) {
                        return back()->withErrors(['edit' => 'Cannot remove a line that already has returns.']);
                    }
                    // Reverse entire issued qty back to same batch
                    $batch = StockBatch::where('purchase_line_id', $line->purchase_line_id)->first();
                    if ($batch) {
                        $batch->qty_available = round((float)$batch->qty_available + (float)$line->quantity, 4);
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

                $oldQty = (float)$line->quantity;
                $diff = $newQty - $oldQty;

                if ($diff < 0) {
                    // Decrease: reverse qty back to same batch
                    $giveBack = abs($diff);
                    $batch = StockBatch::where('purchase_line_id', $line->purchase_line_id)->lockForUpdate()->first();
                    if ($batch) {
                        $batch->qty_available = round((float)$batch->qty_available + $giveBack, 4);
                        $batch->save();
                    }

                    $line->quantity = $newQty;
                    $line->line_total = round($newQty * (float)$line->issue_price, 4);
                    $line->save();

                    // Update ledger row in-place
                    \App\Models\StockLedger::query()
                        ->where('ref_table', 'issues')
                        ->where('ref_id', $issue->id)
                        ->where('ref_line_id', $line->id)
                        ->update([
                            'txn_date' => $issue->issue_date,
                            'qty_out' => round((float)$line->quantity, 4),
                            'unit_price' => round((float)$line->issue_price, 4),
                            'specification_snapshot' => $line->specification,
                        ]);
                    continue;
                }

                if ($diff > 0) {
                    // Increase: first try from same batch (keeps same price), then FIFO for remaining.
                    $remaining = $diff;

                    $batch = StockBatch::where('purchase_line_id', $line->purchase_line_id)->lockForUpdate()->first();
                    if ($batch && (float)$batch->qty_available > 0) {
                        $avail = (float)$batch->qty_available;
                        $takeSame = round(min($remaining, $avail), 4);
                        if ($takeSame > 0) {
                            $batch->qty_available = round($avail - $takeSame, 4);
                            $batch->save();

                            $line->quantity = round($oldQty + $takeSame, 4);
                            $line->line_total = round((float)$line->quantity * (float)$line->issue_price, 4);
                            $line->save();

                            \App\Models\StockLedger::query()
                                ->where('ref_table', 'issues')
                                ->where('ref_id', $issue->id)
                                ->where('ref_line_id', $line->id)
                                ->update([
                                    'txn_date' => $issue->issue_date,
                                    'qty_out' => round((float)$line->quantity, 4),
                                    'unit_price' => round((float)$line->issue_price, 4),
                                    'specification_snapshot' => $line->specification,
                                ]);

                            $remaining = round($remaining - $takeSame, 4);
                        }
                    }

                    if ($remaining > 0) {
                        // Allocate remaining as NEW issue lines using FIFO
                        $stock->issueItemFIFO(
                            issue: $issue,
                            itemId: (int)$line->item_id,
                            qty: $remaining,
                            specification: $line->specification,
                        );
                    }
                }

            }

            // Add new items added during edit (FIFO allocation creates new issue lines + ledgers)
            foreach (($data['new_lines'] ?? []) as $nl) {
                $qty = round((float)($nl['quantity'] ?? 0), 4);
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
                        'qty_out' => round((float)$l->quantity, 4),
                        'unit_price' => round((float)$l->issue_price, 4),
                        'specification_snapshot' => $l->specification,
                        'created_by' => $issue->created_by,
                    ]);
                } else {
                    $ledger->update([
                        'txn_date' => $issue->issue_date,
                        'item_id' => $l->item_id,
                        'qty_out' => round((float)$l->quantity, 4),
                        'unit_price' => round((float)$l->issue_price, 4),
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
                        $batch->qty_available = round((float)$batch->qty_available - (float)$rl->quantity, 4);
                        if ($batch->qty_available < 0) $batch->qty_available = 0;
                        $batch->save();
                    }
                }
            }

            // Reverse issue (add stock back)
            foreach ($issue->lines as $l) {
                $batch = StockBatch::where('purchase_line_id', $l->purchase_line_id)->first();
                if ($batch) {
                    $batch->qty_available = round((float)$batch->qty_available + (float)$l->quantity, 4);
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