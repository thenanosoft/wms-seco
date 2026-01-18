<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueReturnRequest;
use App\Models\Issue;
use App\Models\IssueLine;
use App\Models\IssueReturn;
use App\Models\IssueReturnLine;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssueReturnController extends Controller
{
    public function index(Request $request)
    {
        $q = IssueReturn::query()
            ->with(['issue','creator'])
            ->orderByDesc('return_date')
            ->orderByDesc('id');

        if ($request->filled('from')) $q->whereDate('return_date','>=',$request->from);
        if ($request->filled('to')) $q->whereDate('return_date','<=',$request->to);
        if ($request->filled('issue_id')) $q->where('issue_id', $request->issue_id);

        $rows = $q->paginate(25)->withQueryString();

        $issues = Issue::query()->orderByDesc('issue_date')->limit(200)->get(['id','issue_date','reference_no','issued_to']);

        return view('issue-returns.index', compact('rows','issues'));
    }

    public function create()
    {
        $issues = Issue::query()->orderByDesc('issue_date')->limit(200)->get(['id','issue_date','reference_no','issued_to']);
        return view('issue-returns.create', compact('issues'));
    }

    public function issueLines(Issue $issue)
    {
        // Remaining qty per issue line = issued - sum(returned)
        $lines = IssueLine::query()
            ->where('issue_id', $issue->id)
            ->with(['item.group'])
            ->get()
            ->map(function ($l) {
                $returned = (float) IssueReturnLine::query()
                    ->where('issue_line_id', $l->id)
                    ->sum('quantity');

                $issued = (float)$l->quantity;
                $remaining = max(0, $issued - $returned);

                return [
                    'id' => $l->id,
                    'item_id' => $l->item_id,
                    'group_code' => $l->item->group->group_code,
                    'item_code' => $l->item->item_code,
                    'item_name' => $l->item->name,
                    'specification' => $l->specification,
                    'issue_price' => (float)$l->issue_price,
                    'issued_qty' => $issued,
                    'returned_qty' => $returned,
                    'remaining_qty' => $remaining,
                ];
            })
            ->values();

        return response()->json(['lines' => $lines]);
    }

    public function store(StoreIssueReturnRequest $request, StockService $stock)
    {
        $userId = $request->user()->id;

        // Strict validation: every line must belong to the chosen issue, and qty must be <= remaining.
        $issue = Issue::query()->findOrFail($request->issue_id);

        return DB::transaction(function () use ($request, $issue, $userId, $stock) {
            $header = IssueReturn::create([
                'return_date' => $request->return_date,
                'issue_id' => $issue->id,
                'received_from' => $request->received_from,
                'reference_no' => $request->reference_no,
                'notes' => $request->notes,
                'created_by' => $userId,
            ]);

            $seen = [];

            foreach ($request->input('lines', []) as $line) {
                $issueLineId = (int)$line['issue_line_id'];
                $qty = (float)$line['quantity'];

                if ($qty <= 0) {
                    abort(422, 'Quantity must be greater than zero.');
                }

                if (in_array($issueLineId, $seen, true)) {
                    abort(422, 'Duplicate issue line selected.');
                }
                $seen[] = $issueLineId;

                $issueLine = IssueLine::query()->where('issue_id', $issue->id)->findOrFail($issueLineId);

                $alreadyReturned = (float) IssueReturnLine::query()
                    ->where('issue_line_id', $issueLine->id)
                    ->sum('quantity');

                $remaining = max(0, (float)$issueLine->quantity - $alreadyReturned);

                if ($qty > $remaining) {
                    abort(422, "Only {$remaining} remaining for this issue line.");
                }

                $price = (float)$issueLine->issue_price;
                $lineTotal = round($price * $qty, 2);

                $savedLine = IssueReturnLine::create([
                    'issue_return_id' => $header->id,
                    'issue_line_id' => $issueLine->id,
                    'item_id' => $issueLine->item_id,
                    'specification_snapshot' => $issueLine->specification,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'line_total' => $lineTotal,
                ]);

                // Ledger entry (adds stock back)
                $stock->addIssueReturnInLedger([
                    'txn_date' => $header->return_date,
                    'ref_id' => $header->id,
                    'ref_line_id' => $savedLine->id,
                    'item_id' => $issueLine->item_id,
                    'qty_in' => $qty,
                    'unit_price' => $price,
                    'specification_snapshot' => $issueLine->specification,
                    'created_by' => $userId,
                ]);
            }

            return redirect()->route('issue-returns.index')->with('status', 'Issue return saved successfully.');
        });
    }
}
