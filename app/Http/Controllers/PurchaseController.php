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
        $purchases = Purchase::query()
            ->with('creator')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('purchase.index', compact('purchases'));
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
                $qty = (float) $line['quantity'];
                $price = (float) $line['purchase_price'];
                $total = round($qty * $price, 2);

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
}
