<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueReturnRequest;
use App\Models\Issue;
use App\Models\IssueLine;
use App\Models\IssueReturn;
use App\Models\IssueReturnLine;
use App\Models\StockLedger;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IssueReturnController extends Controller
{
    public function index()
    {
        $returns = IssueReturn::with(['issue','creator'])
            ->orderByDesc('return_date')
            ->paginate(20);

        return view('issue-returns.index', compact('returns'));
    }

    public function create()
    {
        // Helper should only see issues list (latest first)
        $issues = Issue::query()->orderByDesc('issue_date')->limit(200)->get();

        return view('issue-returns.create', compact('issues'));
    }

    public function fetchIssueLines(Issue $issue, StockService $stock)
    {
        // return lines with remaining qty and locked price
        $lines = IssueLine::query()
            ->where('issue_id', $issue->id)
            ->with('item.group')
            ->get()
            ->map(function ($l) {
                // already returned for this issue line (sum in issue_return_lines)
                $returned = (float) IssueReturnLine::query()
                    ->where('issue_line_id', $l->id)
                    ->sum('quantity');

                $remaining = max(0, (float)$l->quantity - $returned);

                return [
                    'issue_line_id' => $l->id,
                    'item_id' => $l->item_id,
                    'group_code' => $l->item->group->group_code,
                    'item_code' => $l->item->item_code,
                    'item_name' => $l->item->name,
                    'specification' => $l->specification,
                    'issue_price' => (float)$l->issue_price,
                    'issued_qty' => (float)$l->quantity,
                    'returned_qty' => $returned,
                    'remaining_qty' => $remaining,
                ];
            })
            ->values();

        return response()->json(['lines' => $lines]);
    }

    public function store(StoreIssueReturnRequest $request, StockService $stock)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $stock) {

            $issue = Issue::findOrFail($data['issue_id']);

            // Validate each line belongs to this issue and qty <= remaining
            foreach ($data['lines'] as $i => $line) {
                $issueLine = IssueLine::where('id', $line['issue_line_id'])
                    ->where('issue_id', $issue->id)
                    ->first();

                if (!$issueLine) {
                    throw ValidationException::withMessages([
                        "lines.$i.issue_line_id" => "Invalid issue line selection.",
                    ]);
                }

                $alreadyReturned = (float) IssueReturnLine::where('issue_line_id', $issueLine->id)->sum('quantity');
                $remaining = max(0, (float)$issueLine->quantity - $alreadyReturned);
                $qty = (float)$line['quantity'];

                if ($qty > $remaining) {
                    throw ValidationException::withMessages([
                        "lines.$i.quantity" => "Return qty exceeds remaining. Remaining: {$remaining}",
                    ]);
                }
            }

            $ret = IssueReturn::create([
                'return_date' => $data['return_date'],
                'issue_id' => $issue->id,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['lines'] as $line) {
                $issueLine = IssueLine::where('id', $line['issue_line_id'])
                    ->where('issue_id', $issue->id)
                    ->firstOrFail();

                $qty = (float)$line['quantity'];

                // LOCK price from issue line (user cannot change)
                $price = (float)$issueLine->issue_price;
                $total = round($qty * $price, 2);

                $rl = IssueReturnLine::create([
                    'issue_return_id' => $ret->id,
                    'issue_line_id' => $issueLine->id,
                    'item_id' => $issueLine->item_id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'line_total' => $total,
                ]);

                // Ledger IN
                $stock->addIssueReturnInLedger([
                    'txn_date' => $ret->return_date,
                    'ref_id' => $ret->id,
                    'ref_line_id' => $rl->id,
                    'item_id' => $rl->item_id,
                    'quantity' => $rl->quantity,
                    'unit_price' => $rl->unit_price,
                    'specification' => $issueLine->specification,
                    'created_by' => $request->user()->id,
                ]);
            }
        });

        return redirect()->route('issue-returns.index')->with('status', 'Issue return saved successfully.');
    }
}
