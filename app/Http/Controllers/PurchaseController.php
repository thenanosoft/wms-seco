<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Group;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\PurchaseReturnLine;
use App\Services\FifoService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $q = Purchase::query()
            ->with('creator')
            ->withCount([
                'lines as pending_prices_count' => function ($qq) {
                    $qq->whereNull('purchase_price');
                },
            ])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');

        if ($request->filled('from')) {
            $q->whereDate('purchase_date', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $q->whereDate('purchase_date', '<=', $request->string('to')->toString());
        }
        if ($request->filled('supplier')) {
            $q->where('supplier_name', 'like', '%' . $request->string('supplier')->toString() . '%');
        }
        if ($request->filled('ref')) {
            $q->where('reference_no', 'like', '%' . $request->string('ref')->toString() . '%');
        }

        $purchases = $q->paginate(15)->appends($request->query());

        $suppliers = Purchase::query()
            ->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->select('supplier_name')
            ->distinct()
            ->orderBy('supplier_name')
            ->pluck('supplier_name');

        return view('purchase.index', compact('purchases', 'suppliers'));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['creator', 'lines.item.group']);
        return view('purchase.show', compact('purchase'));
    }

    public function create()
    {
        $groups = Group::query()->orderBy('group_code')->get(['id','group_code','group_name']);
        $items = Item::query()->orderBy('item_code')->get(['id','group_id','item_code','name','default_spec']);

        return view('purchase.create', compact('groups', 'items'));
    }

    public function store(StorePurchaseRequest $request, StockService $stock, FifoService $fifo)
    {
        $validated = $request->validated();
        $userId = $request->user()->id;

        return DB::transaction(function () use ($validated, $userId, $stock, $fifo) {
            $purchase = Purchase::create([
                'purchase_date' => $validated['purchase_date'],
                'supplier_name' => $validated['supplier_name'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($validated['lines'] as $line) {
                $qty = (int)$line['quantity'];
                $price = ($line['purchase_price'] === null || $line['purchase_price'] === '') ? null : (int)$line['purchase_price'];
                $total = $price !== null ? ($qty * $price) : 0;

                $purchaseLine = PurchaseLine::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => (int)$line['item_id'],
                    'specification' => $line['specification'] ?? null,
                    'purchase_price' => $price,
                    'quantity' => $qty,
                    'line_total' => $total,
                ]);

                // FIFO batch (price can be pending)
                $fifo->createBatchFromPurchaseLine($purchaseLine, $purchase->purchase_date->format('Y-m-d'));

                // Stock ledger IN
                $stock->addPurchaseLedgerEntry([
                    'txn_date' => $purchase->purchase_date,
                    'ref_id' => $purchase->id,
                    'ref_line_id' => $purchaseLine->id,
                    'item_id' => (int)$line['item_id'],
                    'qty_in' => $qty,
                    'unit_price' => $price !== null ? $price : 0,
                    'specification_snapshot' => $line['specification'] ?? null,
                    'created_by' => $userId,
                ]);
            }

            return redirect()->route('purchases.index')->with('status', 'Purchase saved successfully.');
        });
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load(['lines.item.group']);
        $groups = Group::query()->orderBy('group_code')->get(['id','group_code','group_name']);
        $items = Item::query()->orderBy('item_code')->get(['id','group_id','item_code','name','default_spec']);

        $existingLines = $purchase->lines->map(function (PurchaseLine $line) {
            return [
                'id' => $line->id,
                'group_id' => $line->item?->group_id,
                'item_id' => $line->item_id,
                'item_label' => ($line->item?->item_code ?? '') . ' - ' . ($line->item?->name ?? ''),
                'specification' => $line->specification,
                'purchase_price' => $line->purchase_price,
                'quantity' => $line->quantity,
            ];
        })->values();

        return view('purchase.edit', compact('purchase','groups','items','existingLines'));
    }


    public function update(Request $request, Purchase $purchase, StockService $stock, FifoService $fifo)
    {
        $data = $request->validate([
            'purchase_date' => ['required','date'],
            'supplier_name' => ['nullable','string','max:255'],
            'reference_no' => ['nullable','string','max:255'],
            'notes' => ['nullable','string','max:2000'],

            'lines' => ['required','array','min:1'],
            'lines.*.id' => ['nullable','integer','exists:purchase_lines,id'],
            'lines.*.group_id' => ['required','integer','exists:groups,id'],
            'lines.*.item_id' => ['required','integer','exists:items,id'],
            'lines.*.specification' => ['nullable','string','max:2000'],
            'lines.*.purchase_price' => ['nullable','integer','min:0'],
            'lines.*.quantity' => ['required','integer','min:1'],
        ]);

        return DB::transaction(function () use ($data, $purchase, $stock, $fifo) {
            $purchase->update([
                'purchase_date' => $data['purchase_date'],
                'supplier_name' => $data['supplier_name'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $existingLines = $purchase->lines()->get()->keyBy('id');

            foreach ($data['lines'] as $row) {
                $lineId = $row['id'] ?? null;
                $qty = (int)$row['quantity'];
                $price = ($row['purchase_price'] === null || $row['purchase_price'] === '') ? null : (int)$row['purchase_price'];

                if ($lineId) {
                    /** @var PurchaseLine $line */
                    $line = $existingLines->get((int)$lineId);
                    if (!$line) abort(422, 'Invalid purchase line.');

                    $batch = StockBatch::query()->where('purchase_line_id', $line->id)->lockForUpdate()->first();
                    if (!$batch) {
                        $batch = $fifo->createBatchFromPurchaseLine($line, $purchase->purchase_date->format('Y-m-d'));
                    }

                    // Quantity protection (cannot reduce below consumed)
                    $fifo->updateBatchQuantityFromPurchaseLine($batch, $qty);

                    $newItemId = (int)$row['item_id'];
                    $consumed = (int)$batch->qty_purchased - (int)$batch->qty_available;
                    if ($newItemId !== (int)$line->item_id && $consumed > 0) {
                        abort(422, 'Cannot change item on a purchase line that already has issued/consumed stock.');
                    }

                    $line->item_id = $newItemId;
                    $line->specification = $row['specification'] ?? null;
                    $line->quantity = $qty;
                    $line->purchase_price = $price;
                    $line->line_total = $price !== null ? ($qty * $price) : 0;
                    $line->save();

                    // Update batch meta
                    $batch->purchase_date = $purchase->purchase_date->format('Y-m-d');
                    $batch->item_id = $line->item_id;
                    $batch->specification = $line->specification;
                    // unit_price updated below via propagate
                    $batch->save();

                    // Update ledger purchase qty and snapshot
                    $purchaseLedger = StockLedger::query()
                        ->where('ref_table','purchases')
                        ->where('ref_line_id', $line->id)
                        ->lockForUpdate()
                        ->first();

                    if ($purchaseLedger) {
                        $purchaseLedger->txn_date = $purchase->purchase_date;
                        $purchaseLedger->qty_in = $qty;
                        $purchaseLedger->specification_snapshot = $line->specification;
                        $purchaseLedger->unit_price = $price !== null ? $price : 0;
                        $purchaseLedger->save();
                    }

                    // Propagate price to issued + return records
                    if ($price !== null) {
                        $fifo->propagateBatchPrice($line->id, $price);
                    }
                } else {
                    // Add new item later to same purchase
                    $total = $price !== null ? ($qty * $price) : 0;
                    $newLine = PurchaseLine::create([
                        'purchase_id' => $purchase->id,
                        'item_id' => (int)$row['item_id'],
                        'specification' => $row['specification'] ?? null,
                        'purchase_price' => $price,
                        'quantity' => $qty,
                        'line_total' => $total,
                    ]);

                    $fifo->createBatchFromPurchaseLine($newLine, $purchase->purchase_date->format('Y-m-d'));

                    $stock->addPurchaseLedgerEntry([
                        'txn_date' => $purchase->purchase_date,
                        'ref_id' => $purchase->id,
                        'ref_line_id' => $newLine->id,
                        'item_id' => (int)$row['item_id'],
                        'qty_in' => $qty,
                        'unit_price' => $price !== null ? $price : 0,
                        'specification_snapshot' => $row['specification'] ?? null,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            return redirect()->route('purchases.show', $purchase)->with('status', 'Purchase updated successfully.');
        });
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->load('lines');

        return DB::transaction(function () use ($purchase) {
            $lineIds = $purchase->lines->pluck('id')->all();

            $hasConsumed = StockBatch::query()
                ->whereIn('purchase_line_id', $lineIds)
                ->whereRaw('qty_available < qty_purchased')
                ->exists();

            $hasPurchaseReturns = PurchaseReturnLine::query()
                ->whereIn('purchase_line_id', $lineIds)
                ->exists();

            if ($hasConsumed || $hasPurchaseReturns) {
                return back()->withErrors([
                    'delete' => 'Cannot delete this purchase because stock from it was already issued/returned. Only empty (unused) purchases can be deleted.'
                ]);
            }

            StockLedger::query()
                ->where('ref_table', 'purchases')
                ->where('ref_id', $purchase->id)
                ->delete();

            $purchase->delete();

            return redirect()->route('purchases.index')->with('status', 'Purchase deleted successfully.');
        });
    }

    public function itemsIndex(Request $request)
    {
        $q = PurchaseLine::query()
            ->select([
                'purchase_lines.*',
                'purchases.purchase_date',
                'purchases.reference_no',
                'purchases.supplier_name',
                'items.item_code',
                'items.name as item_name',
                'groups.group_code',
            ])
            ->join('purchases', 'purchases.id', '=', 'purchase_lines.purchase_id')
            ->join('items', 'items.id', '=', 'purchase_lines.item_id')
            ->join('groups', 'groups.id', '=', 'items.group_id')
            ->orderByDesc('purchases.purchase_date')
            ->orderByDesc('purchase_lines.id');

        if ($request->filled('purchase_id')) $q->where('purchases.id', $request->purchase_id);
        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('from')) $q->whereDate('purchases.purchase_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('purchases.purchase_date', '<=', $request->to);

        $rows = $q->paginate(50)->withQueryString();

        $purchases = Purchase::query()->orderByDesc('purchase_date')->limit(200)->get();
        $groups = Group::query()->orderBy('group_code')->get();
        $items = Item::query()->orderBy('item_code')->get();

        return view('purchase.items-index', compact('rows','purchases','groups','items'));
    }
}
