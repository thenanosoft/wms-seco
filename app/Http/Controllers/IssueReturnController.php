<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\IssueLine;
use App\Models\IssueReturnLine;
use App\Models\IssueReturnTransaction;
use App\Models\StockBatch;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockLedger;

class IssueReturnController extends Controller
{
    public function index(Request $request)
    {
        $q = IssueReturnLine::query()
            ->select([
                'issue_return_lines.*',
                'issue_return_transactions.return_date',
                'issues.issue_date',
                'issues.reference_no',
                'issues.issued_to',
                'items.item_code',
                'items.name as item_name',
                'groups.group_code',
                'users.name as created_by_name',
            ])
            ->join('issue_return_transactions', 'issue_return_transactions.id', '=', 'issue_return_lines.issue_return_transaction_id')
            ->join('issues', 'issues.id', '=', 'issue_return_transactions.issue_id')
            ->join('items', 'items.id', '=', 'issue_return_lines.item_id')
            ->join('groups', 'groups.id', '=', 'items.group_id')
            ->leftJoin('users', 'users.id', '=', 'issue_return_transactions.created_by')
            ->orderByDesc('issue_return_transactions.return_date')
            ->orderByDesc('issue_return_lines.id');

        if ($request->filled('issue_id')) $q->where('issues.id', $request->issue_id);
        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('from')) $q->whereDate('issue_return_transactions.return_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('issue_return_transactions.return_date', '<=', $request->to);

        $rows = $q->paginate(50)->withQueryString();

        $issues = Issue::query()->orderByDesc('issue_date')->limit(200)->get();
        $groups = \App\Models\Group::query()->orderBy('group_code')->get();
        $items = \App\Models\Item::query()->orderBy('item_code')->get();

        return view('returns.issue.index', compact('rows','issues','groups','items'));
    }

    public function create(Request $request)
    {
        $issues = Issue::query()
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        $selectedIssue = null;
        $lines = [];

        if ($request->filled('issue_id')) {
            $selectedIssue = Issue::query()->with(['lines.item.group'])->find($request->issue_id);
            if ($selectedIssue) {
                $lines = $selectedIssue->lines->map(function (IssueLine $line) {
                    $returned = (int) IssueReturnLine::query()->where('issue_line_id', $line->id)->sum('quantity');
                    $remaining = max(0, (int)$line->quantity - $returned);
                    return [
                        'line_id' => $line->id,
                        'group_code' => $line->item->group->group_code,
                        'item_code' => $line->item->item_code,
                        'item_name' => $line->item->name,
                        'specification' => $line->specification,
                        'issue_price' => (int)$line->issue_price,
                        'issued_qty' => (int)$line->quantity,
                        'returned_qty' => $returned,
                        'remaining_qty' => $remaining,
                    ];
                })->values()->all();
            }
        }

        return view('returns.issue.create', compact('issues','selectedIssue','lines'));
    }

    public function store(Request $request, StockService $stock)
    {
        $data = $request->validate([
            'return_date' => ['required','date'],
            'issue_id' => ['required','integer','exists:issues,id'],
            'notes' => ['nullable','string','max:255'],
            'lines' => ['required','array','min:1'],
            'lines.*.issue_line_id' => ['required','integer','exists:issue_lines,id'],
            // Business rule: integers only (no decimals). 0 means "skip this line".
            'lines.*.quantity' => ['required','integer','min:0'],
        ]);

        // Must return at least 1 item qty.
        $hasQty = collect($data['lines'] ?? [])->contains(fn($r) => ((int)($r['quantity'] ?? 0)) > 0);
        if (!$hasQty) {
            return back()->withErrors(['lines' => 'Please enter at least 1 return quantity.'])->withInput();
        }

        $issue = Issue::query()->with(['lines.item'])->findOrFail($data['issue_id']);
        $issueLinesById = $issue->lines->keyBy('id');

        return DB::transaction(function () use ($data, $issue, $issueLinesById, $stock) {
            $tx = IssueReturnTransaction::create([
                'return_date' => $data['return_date'],
                'issue_id' => $issue->id,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['lines'] as $row) {
                $lineId = (int)$row['issue_line_id'];
                $qty = (int)$row['quantity'];
                if ($qty <= 0) continue;

                /** @var IssueLine|null $issueLine */
                $issueLine = $issueLinesById->get($lineId);
                if (!$issueLine) {
                    abort(422, 'Invalid issue line selected.');
                }

                $alreadyReturned = (int) IssueReturnLine::query()->where('issue_line_id', $issueLine->id)->sum('quantity');
                $remaining = max(0, (int)$issueLine->quantity - $alreadyReturned);

                if ($qty > $remaining) {
                    abort(422, "Return qty cannot exceed remaining issued qty (Remaining: {$remaining}).");
                }

                $lineTotal = ((int)$issueLine->issue_price) * $qty;
                $rLine = IssueReturnLine::create([
                    'issue_return_transaction_id' => $tx->id,
                    'issue_line_id' => $issueLine->id,
                    'item_id' => $issueLine->item_id,
                    'specification' => $issueLine->specification,
                    'issue_price' => $issueLine->issue_price,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ]);

                $stock->addIssueReturnInLedgerEntry([
                    'txn_date' => $data['return_date'],
                    'ref_id' => $tx->id,
                    'ref_line_id' => $rLine->id,
                    'item_id' => $issueLine->item_id,
                    'qty_in' => $qty,
                    'unit_price' => (int)$issueLine->issue_price,
                    'specification_snapshot' => $issueLine->specification,
                    'created_by' => auth()->id(),
                ]);

                // Restore stock back to the SAME FIFO batch it was issued from.
                if ($issueLine->purchase_line_id) {
                    $batch = StockBatch::query()
                        ->where('purchase_line_id', $issueLine->purchase_line_id)
                        ->lockForUpdate()
                        ->first();

                    if ($batch) {
                        $batch->qty_available = (int)$batch->qty_available + $qty;
                        if ($batch->qty_available > $batch->qty_purchased) {
                            $batch->qty_available = $batch->qty_purchased;
                        }
                        $batch->save();
                    }
                }
            }

            return redirect()->route('returns.issue.index')->with('status', 'Issue return saved.');
        });
    }

    public function edit(IssueReturnTransaction $issueReturnTransaction)
    {
        $issueReturnTransaction->load(['issue','lines.issueLine.item.group']);

        return view('returns.issue.edit', [
            'txn' => $issueReturnTransaction,
            'issue' => $issueReturnTransaction->issue,
        ]);
    }

    public function update(Request $request, IssueReturnTransaction $issueReturnTransaction, StockService $stock)
    {
        $issueReturnTransaction->load(['issue','lines']);

        $data = $request->validate([
            'return_date' => ['required','date'],
            'notes' => ['nullable','string','max:255'],
            'lines' => ['required','array'],
            'lines.*.id' => ['required','integer','exists:issue_return_lines,id'],
            'lines.*.quantity' => ['required','integer','min:0'],
        ]);

        return DB::transaction(function () use ($issueReturnTransaction, $data, $stock) {
            // 1) Reverse current return quantities from batches
            $issueReturnTransaction->lines->load('issueLine');
            foreach ($issueReturnTransaction->lines as $rl) {
                $issueLine = $rl->issueLine;
                if (!$issueLine) continue;
                $batch = StockBatch::where('purchase_line_id', $issueLine->purchase_line_id)->lockForUpdate()->first();
                if ($batch) {
                    $batch->qty_available = max(0, (int)$batch->qty_available - (int)$rl->quantity);
                    $batch->save();
                }
            }

            // delete ledger entries for this return txn only
            StockLedger::query()->where('ref_table','issue_return_transactions')->where('ref_id', $issueReturnTransaction->id)->delete();

            // 2) Update header
            $issueReturnTransaction->update([
                'return_date' => $data['return_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            // 3) Update each return line quantity and re-apply stock + ledger
            foreach ($data['lines'] as $row) {
                $line = $issueReturnTransaction->lines->firstWhere('id', (int)$row['id']);
                if (!$line) continue;

                $newQty = (int)$row['quantity'];

                // cannot return more than issued for that issue line
                $issueLine = \App\Models\IssueLine::query()->find($line->issue_line_id);
                if ($issueLine) {
                    $issuedQty = (int)$issueLine->quantity;
                    // total returned excluding current line
                    $already = \App\Models\IssueReturnLine::query()
                        ->where('issue_line_id', $issueLine->id)
                        ->where('id','!=',$line->id)
                        ->sum('quantity');
                    if ($newQty + (int)$already > $issuedQty) {
                        return back()->withErrors(['lines' => 'Return qty cannot exceed issued qty.'])->withInput();
                    }
                }

                $line->quantity = $newQty;
                $line->save();

                if ($newQty <= 0) continue;

                // restore back to original batch
                $batch = StockBatch::where('purchase_line_id', $issueLine?->purchase_line_id)->lockForUpdate()->first();
                if ($batch) {
                    $batch->qty_available = (int)$batch->qty_available + $newQty;
                    $batch->save();
                }

                // ledger (in)
                $stock->addIssueReturnLedgerEntry([
                    'txn_date' => $issueReturnTransaction->return_date,
                    'ref_id' => $issueReturnTransaction->id,
                    'ref_line_id' => $line->id,
                    'item_id' => $issueLine?->item_id,
                    'qty_in' => $newQty,
                    'unit_price' => (int)$issueLine?->issue_price,
                    'specification_snapshot' => $issueLine?->specification,
                    'created_by' => $issueReturnTransaction->created_by,
                ]);
            }

            return redirect()->route('returns.issue.index')->with('status','Issue return updated successfully.');
        });
    }

    public function destroy(IssueReturnTransaction $issueReturnTransaction)
    {
        $issueReturnTransaction->load(['lines.issueLine']);

        return DB::transaction(function () use ($issueReturnTransaction) {
            foreach ($issueReturnTransaction->lines as $rl) {
                $issueLine = $rl->issueLine;
                if (!$issueLine) continue;
                $batch = StockBatch::where('purchase_line_id', $issueLine->purchase_line_id)->lockForUpdate()->first();
                if ($batch) {
                    $batch->qty_available = max(0, (int)$batch->qty_available - (int)$rl->quantity);
                    $batch->save();
                }
            }

            StockLedger::query()->where('ref_table','issue_return_transactions')->where('ref_id', $issueReturnTransaction->id)->delete();
            \App\Models\IssueReturnLine::query()->where('issue_return_transaction_id', $issueReturnTransaction->id)->delete();
            $issueReturnTransaction->delete();

            return redirect()->route('returns.issue.index')->with('status','Issue return deleted successfully.');
        });
    }
}
