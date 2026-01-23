<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Models\Group;
use App\Models\Item;
use App\Models\Issue;
use App\Models\IssueLine;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $q = Issue::query()
            ->with('creator')
            ->orderByDesc('issue_date')
            ->orderByDesc('id');

        if ($request->filled('from')) {
            $q->whereDate('issue_date', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $q->whereDate('issue_date', '<=', $request->string('to')->toString());
        }
        if ($request->filled('issued_to')) {
            $q->where('issued_to', 'like', '%' . $request->string('issued_to')->toString() . '%');
        }
        if ($request->filled('reference')) {
            $q->where('reference', 'like', '%' . $request->string('reference')->toString() . '%');
        }

        $issues = $q->paginate(15)->withQueryString();

        return view('issue.index', compact('issues'));
    }

    public function show(Issue $issue)
    {
        $issue->load(['creator', 'lines.item.group']);
        return view('issue.show', compact('issue'));
    }

    public function create(\App\Services\StockService $stock)
{
    $groups = Group::query()
        ->orderBy('group_code')
        ->get(['id','group_code','group_name']);

    $items = Item::query()
        ->orderBy('item_code')
        ->get()
        ->map(function ($item) use ($stock) {
            $price = $stock->getLastPurchasePrice($item->id);
            $stockInfo = $stock->getAvailableStockDetailed($item->id);

            return [
                'id' => $item->id,
                'group_id' => $item->group_id,
                'item_code' => $item->item_code,
                'name' => $item->name,
                'default_spec' => $item->default_spec,
                'last_price' => $price,
                'available_stock' => $stockInfo['available'],
            ];
        });

    return view('issue.create', [
        'groups' => $groups,
        'items' => $items,
    ]);
}


    public function store(StoreIssueRequest $request, StockService $stock)
    {
        $validated = $request->validated();
        $userId = $request->user()->id;

        return DB::transaction(function () use ($validated, $userId, $stock) {

            // Stock availability check (audit-safe)
            foreach ($validated['lines'] as $i => $line) {
                $itemId = (int) $line['item_id'];
                $qty = (float) $line['quantity'];

                $available = $stock->getAvailableStock($itemId);

                if ($qty > $available) {
                    throw ValidationException::withMessages([
                        "lines.$i.quantity" => "Not enough stock. Available: {$available}",
                    ]);
                }
            }

            $issue = Issue::create([
                'issue_date' => $validated['issue_date'],
                'issued_to' => $validated['issued_to'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($validated['lines'] as $line) {
                $qty = (int) $line['quantity'];
                $price = (int) ($line['issue_price'] ?? 0);
                $total = $qty * $price;

                $issueLine = IssueLine::create([
                    'issue_id' => $issue->id,
                    'item_id' => $line['item_id'],
                    'specification' => $line['specification'] ?? null,
                    'issue_price' => $price,
                    'quantity' => $qty,
                    'line_total' => $total,
                ]);

                $stock->addIssueLedgerEntry([
                    'txn_date' => $issue->issue_date,
                    'ref_id' => $issue->id,
                    'ref_line_id' => $issueLine->id,
                    'item_id' => (int) $line['item_id'],
                    'qty_out' => $qty,
                    'unit_price' => $price,
                    'specification_snapshot' => $line['specification'] ?? null,
                    'created_by' => $userId,
                ]);
            }

            return redirect()
                ->route('issues.index')
                ->with('status', 'Issue saved successfully.');
        });
    }
}
