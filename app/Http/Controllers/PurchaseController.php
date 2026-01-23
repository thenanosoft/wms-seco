<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Group;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $q = Purchase::query()
            ->with('creator')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');

        // Filters
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
        // Items will be fetched via AJAX endpoint, but for MVP we preload all items (OK for small dataset)
        $items = Item::query()
            ->orderBy('item_code')
            ->get(['id','group_id','item_code','name','default_spec']);

        return view('purchase.create', compact('groups', 'items'));
    }

    public function store(StorePurchaseRequest $request, StockService $stock)
    {
        $validated = $request->validated();

        $userId = $request->user()->id;

        return DB::transaction(function () use ($validated, $userId, $stock) {

            $purchase = Purchase::create([
                'purchase_date' => $validated['purchase_date'],
                'supplier_name' => $validated['supplier_name'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($validated['lines'] as $line) {
                $qty = (int) $line['quantity'];
                $price = (int) $line['purchase_price'];
                $total = $qty * $price;

                $purchaseLine = PurchaseLine::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $line['item_id'],
                    'specification' => $line['specification'] ?? null,
                    'purchase_price' => $price,
                    'quantity' => $qty,
                    'line_total' => $total,
                ]);

                $stock->addPurchaseLedgerEntry([
                    'txn_date' => $purchase->purchase_date,
                    'ref_id' => $purchase->id,
                    'ref_line_id' => $purchaseLine->id,
                    'item_id' => (int) $line['item_id'],
                    'qty_in' => $qty,
                    'unit_price' => $price,
                    'specification_snapshot' => $line['specification'] ?? null,
                    'created_by' => $userId,
                ]);
            }

            return redirect()
                ->route('purchases.index')
                ->with('status', 'Purchase saved successfully.');
        });
    }
    public function itemsIndex(Request $request)
{
    $q = \App\Models\PurchaseLine::query()
        ->select([
            'purchase_lines.*',
            'purchases.purchase_date',
            'groups.group_code',
            'groups.group_name',
            'items.item_code',
            'items.name as item_name',
        ])
        ->join('purchases', 'purchases.id', '=', 'purchase_lines.purchase_id')
        ->join('items', 'items.id', '=', 'purchase_lines.item_id')
        ->join('groups', 'groups.id', '=', 'items.group_id')
        ->orderByDesc('purchases.purchase_date')
        ->orderByDesc('purchase_lines.id');

    // Filters
    if ($request->filled('group_id')) {
        $q->where('groups.id', $request->group_id);
    }

    if ($request->filled('item_id')) {
        $q->where('items.id', $request->item_id);
    }

    if ($request->filled('from')) {
        $q->whereDate('purchases.purchase_date', '>=', $request->from);
    }

    if ($request->filled('to')) {
        $q->whereDate('purchases.purchase_date', '<=', $request->to);
    }

    $rows = $q->paginate(20)->withQueryString();

    $groups = \App\Models\Group::orderBy('group_code')->get();
    $items = \App\Models\Item::orderBy('item_code')->get();

    return view('purchase.items-index', compact('rows', 'groups', 'items'));
}

}
