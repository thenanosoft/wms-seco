<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Models\Group;
use App\Models\Item;
use App\Models\Issue;
use App\Models\IssueLine;
use App\Services\StockService;
use App\Services\FifoService;
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
            $stockInfo = $stock->getAvailableStockDetailed($item->id);
            $fifoNext = $stock->getNextFifoIssueUnitPrice($item->id);

            return [
                'id' => $item->id,
                'group_id' => $item->group_id,
                'item_code' => $item->item_code,
                'name' => $item->name,
                'default_spec' => $item->default_spec,
                // UI display only
                'fifo_next_price' => (int)($fifoNext['price'] ?? 0),
                'fifo_price_pending' => (bool)($fifoNext['pending'] ?? false),
                'available_stock' => (int)($stockInfo['available'] ?? 0),
            ];
        });

    return view('issue.create', [
        'groups' => $groups,
        'items' => $items,
    ]);
}


    public function store(StoreIssueRequest $request, StockService $stock, FifoService $fifo)
    {
        $validated = $request->validated();
        $userId = $request->user()->id;

        return DB::transaction(function () use ($validated, $userId, $stock, $fifo) {
            // Validate stock and FIFO availability.
            foreach ($validated['lines'] as $i => $line) {
                $itemId = (int) $line['item_id'];
                $qty = (int) $line['quantity'];

                $available = (int) $stock->getAvailableStock($itemId);
                if ($qty > $available) {
                    throw ValidationException::withMessages([
                        "lines.$i.quantity" => "Not enough stock. Available: {$available}",
                    ]);
                }

                if (empty($fifo->allocateBatchesForIssue($itemId, $qty))) {
                    throw ValidationException::withMessages([
                        "lines.$i.quantity" => "Not enough FIFO batches available for this item.",
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

            // IMPORTANT: create IssueLine rows per FIFO batch allocation.
            foreach ($validated['lines'] as $line) {
                $itemId = (int) $line['item_id'];
                $qtyNeeded = (int) $line['quantity'];

                $allocations = $fifo->allocateBatchesForIssue($itemId, $qtyNeeded);
                if (empty($allocations)) {
                    throw ValidationException::withMessages(['lines' => 'Not enough FIFO stock available.']);
                }

                foreach ($allocations as $a) {
                    /** @var \App\Models\StockBatch $batch */
                    $batch = $a['batch'];
                    $takeQty = (int) $a['qty'];

                    $batch->qty_available = (int) $batch->qty_available - $takeQty;
                    if ($batch->qty_available < 0) {
                        throw ValidationException::withMessages(['lines' => 'Batch stock became negative.']);
                    }
                    $batch->save();

                    $price = $batch->unit_price !== null ? (int) $batch->unit_price : 0; // 0 = pending price
                    $total = $takeQty * $price;

                    $issueLine = IssueLine::create([
                        'issue_id' => $issue->id,
                        'purchase_line_id' => $batch->purchase_line_id,
                        'item_id' => $itemId,
                        'specification' => $line['specification'] ?? $batch->specification,
                        'issue_price' => $price,
                        'quantity' => $takeQty,
                        'line_total' => $total,
                    ]);

                    $stock->addIssueLedgerEntry([
                        'txn_date' => $issue->issue_date,
                        'ref_id' => $issue->id,
                        'ref_line_id' => $issueLine->id,
                        'item_id' => $itemId,
                        'qty_out' => $takeQty,
                        'unit_price' => $price,
                        'specification_snapshot' => $issueLine->specification,
                        'created_by' => $userId,
                    ]);
                }
            }

            return redirect()->route('issues.index')->with('status', 'Issue saved successfully.');
        });
    }
}
