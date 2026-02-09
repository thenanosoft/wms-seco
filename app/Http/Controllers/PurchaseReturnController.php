<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\PurchaseReturnLine;
use App\Models\PurchaseReturnTransaction;
use App\Models\StockBatch;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $q = PurchaseReturnLine::query()
            ->select([
                'purchase_return_lines.*',
                'purchase_return_transactions.return_date',
                'purchases.purchase_date',
                'purchases.reference_no',
                'purchases.supplier_name',
                'items.item_code',
                'items.name as item_name',
                'groups.group_code',
                'users.name as created_by_name',
            ])
            ->join('purchase_return_transactions', 'purchase_return_transactions.id', '=', 'purchase_return_lines.purchase_return_transaction_id')
            ->join('purchases', 'purchases.id', '=', 'purchase_return_transactions.purchase_id')
            ->join('items', 'items.id', '=', 'purchase_return_lines.item_id')
            ->join('groups', 'groups.id', '=', 'items.group_id')
            ->leftJoin('users', 'users.id', '=', 'purchase_return_transactions.created_by')
            ->orderByDesc('purchase_return_transactions.return_date')
            ->orderByDesc('purchase_return_lines.id');

        if ($request->filled('purchase_id')) $q->where('purchases.id', $request->purchase_id);
        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('from')) $q->whereDate('purchase_return_transactions.return_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('purchase_return_transactions.return_date', '<=', $request->to);

        $rows = $q->paginate(50)->withQueryString();

        $purchases = Purchase::query()->orderByDesc('purchase_date')->limit(200)->get();
        $groups = \App\Models\Group::query()->orderBy('group_code')->get();
        $items = \App\Models\Item::query()->orderBy('item_code')->get();

        return view('returns.purchase.index', compact('rows','purchases','groups','items'));
    }

    public function create(Request $request)
    {
        $purchasesQ = Purchase::query()->orderByDesc('purchase_date');

        if ($request->filled('q')) {
            $term = trim((string)$request->q);
            $purchasesQ->where(function($qq) use ($term) {
                if (ctype_digit($term)) {
                    $qq->orWhere('purchases.id', (int)$term);
                }
                $qq->orWhere('purchases.reference_no', 'like', "%{$term}%")
                   ->orWhere('purchases.supplier_name', 'like', "%{$term}%");
            });
        }

        $purchases = $purchasesQ->limit(500)->get();

        $selectedPurchase = null;
        $lines = [];

        if ($request->filled('purchase_id')) {
            $selectedPurchase = Purchase::query()->with(['lines.item.group'])->find($request->purchase_id);
            if ($selectedPurchase) {
                $stockSvc = app(StockService::class);

                $lines = $selectedPurchase->lines->map(function (PurchaseLine $line) use ($stockSvc) {
                    $returned = (int) PurchaseReturnLine::query()->where('purchase_line_id', $line->id)->sum('quantity');
                    $remainingFromPurchase = max(0, (int)$line->quantity - $returned);
                    $batch = StockBatch::query()->where('purchase_line_id', $line->id)->first();
                    $availableNow = (int)($batch?->qty_available ?? 0);
                    $maxReturn = max(0, min($remainingFromPurchase, $availableNow));

                    return [
                        'line_id' => $line->id,
                        'group_code' => $line->item->group->group_code,
                        'item_code' => $line->item->item_code,
                        'item_name' => $line->item->name,
                        'specification' => $line->specification,
                        'purchase_price' => (int)$line->purchase_price,
                        'purchased_qty' => (int)$line->quantity,
                        'returned_qty' => $returned,
                        'remaining_from_purchase' => $remainingFromPurchase,
                        'available_now' => $availableNow,
                        'max_return_qty' => $maxReturn,
                    ];
                })->values()->all();
            }
        }

        return view('returns.purchase.create', compact('purchases','selectedPurchase','lines'));
    }

    public function store(Request $request, StockService $stock)
    {
        $data = $request->validate([
            'return_date' => ['required','date'],
            'purchase_id' => ['required','integer','exists:purchases,id'],
            'notes' => ['nullable','string','max:255'],
            'lines' => ['required','array','min:1'],
            'lines.*.purchase_line_id' => ['required','integer','exists:purchase_lines,id'],
            // Business rule: integers only (no decimals). 0 means "skip this line".
            'lines.*.quantity' => ['required','integer','min:0'],
        ]);

        // Must return at least 1 item qty.
        $hasQty = collect($data['lines'] ?? [])->contains(fn($r) => ((int)($r['quantity'] ?? 0)) > 0);
        if (!$hasQty) {
            return back()->withErrors(['lines' => 'Please enter at least 1 return quantity.'])->withInput();
        }

        $purchase = Purchase::query()->with(['lines.item'])->findOrFail($data['purchase_id']);
        $purchaseLinesById = $purchase->lines->keyBy('id');

        return DB::transaction(function () use ($data, $purchase, $purchaseLinesById, $stock) {
            $tx = PurchaseReturnTransaction::create([
                'return_date' => $data['return_date'],
                'purchase_id' => $purchase->id,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['lines'] as $row) {
                $lineId = (int)$row['purchase_line_id'];
                $qty = (int)$row['quantity'];
                if ($qty <= 0) continue;

                /** @var PurchaseLine|null $purchaseLine */
                $purchaseLine = $purchaseLinesById->get($lineId);
                if (!$purchaseLine) {
                    return back()->withErrors(['lines' => 'Invalid purchase line selected.'])->withInput();
                }

                $alreadyReturned = (int) PurchaseReturnLine::query()->where('purchase_line_id', $purchaseLine->id)->sum('quantity');
                $remainingFromPurchase = max(0, (int)$purchaseLine->quantity - $alreadyReturned);
                $batch = StockBatch::query()->where('purchase_line_id', $purchaseLine->id)->lockForUpdate()->first();
                $availableNow = (int)($batch?->qty_available ?? 0);
                $maxReturn = max(0, min($remainingFromPurchase, $availableNow));

                if ($qty > $maxReturn) {
                    abort(422, "Return qty cannot exceed allowed return qty (Max: {$maxReturn}).");
                }

                $lineTotal = ((int)$purchaseLine->purchase_price) * $qty;
                $rLine = PurchaseReturnLine::create([
                    'purchase_return_transaction_id' => $tx->id,
                    'purchase_line_id' => $purchaseLine->id,
                    'item_id' => $purchaseLine->item_id,
                    'specification' => $purchaseLine->specification,
                    'purchase_price' => $purchaseLine->purchase_price,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ]);

                $stock->addPurchaseReturnOutLedgerEntry([
                    'txn_date' => $data['return_date'],
                    'ref_id' => $tx->id,
                    'ref_line_id' => $rLine->id,
                    'item_id' => $purchaseLine->item_id,
                    'qty_out' => $qty,
                    'unit_price' => (int)$purchaseLine->purchase_price,
                    'specification_snapshot' => $purchaseLine->specification,
                    'created_by' => auth()->id(),
                ]);

                // Decrease availability in the SAME FIFO batch (purchase line).

                if ($batch) {
                    $batch->qty_available = (int)$batch->qty_available - $qty;
                    if ($batch->qty_available < 0) {
                        $batch->qty_available = 0;
                    }
                    $batch->save();
                }
            }

            return redirect()->route('returns.purchase.index')->with('status', 'Purchase return saved.');
        });
    }

    public function edit(PurchaseReturnTransaction $purchaseReturnTransaction)
    {
        $purchaseReturnTransaction->load(['purchase','lines.purchaseLine.item.group']);

        return view('returns.purchase.edit', [
            'txn' => $purchaseReturnTransaction,
            'purchase' => $purchaseReturnTransaction->purchase,
        ]);
    }

    public function update(Request $request, PurchaseReturnTransaction $purchaseReturnTransaction, StockService $stock)
    {
        $purchaseReturnTransaction->load(['purchase','lines']);

        $data = $request->validate([
            'return_date' => ['required','date'],
            'notes' => ['nullable','string','max:255'],
            'lines' => ['required','array'],
            'lines.*.id' => ['required','integer','exists:purchase_return_lines,id'],
            'lines.*.quantity' => ['required','integer','min:0'],
        ]);

        return DB::transaction(function () use ($purchaseReturnTransaction, $data, $stock) {
            // 1) Reverse previous return (restore stock + delete ledger)
            foreach ($purchaseReturnTransaction->lines as $rl) {
                $pl = \App\Models\PurchaseLine::query()->find($rl->purchase_line_id);
                if (!$pl) continue;
                $batch = StockBatch::where('purchase_line_id', $pl->id)->lockForUpdate()->first();
                if ($batch) {
                    $batch->qty_available = (int)$batch->qty_available + (int)$rl->quantity;
                    $batch->save();
                }
            }
            StockLedger::query()->where('ref_table','purchase_return_transactions')->where('ref_id', $purchaseReturnTransaction->id)->delete();

            // 2) Update header
            $purchaseReturnTransaction->update([
                'return_date' => $data['return_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            // 3) Update each line and re-apply stock + ledger
            $purchaseReturnTransaction->load('lines');
            foreach ($data['lines'] as $row) {
                $line = $purchaseReturnTransaction->lines->firstWhere('id', (int)$row['id']);
                if (!$line) continue;
                $newQty = (int)$row['quantity'];

                // cannot return more than purchased qty for that purchase line
                $pl = \App\Models\PurchaseLine::query()->find($line->purchase_line_id);
                if ($pl) {
                    $purchased = (int)$pl->quantity;
                    $already = \App\Models\PurchaseReturnLine::query()
                        ->where('purchase_line_id', $pl->id)
                        ->where('id','!=',$line->id)
                        ->sum('quantity');
                    if ($newQty + (int)$already > $purchased) {
                        return back()->withErrors(['lines' => 'Return qty cannot exceed purchased qty.'])->withInput();
                    }
                }

                $line->quantity = $newQty;
                $line->save();
                if ($newQty <= 0) continue;

                $batch = StockBatch::where('purchase_line_id', $pl?->id)->lockForUpdate()->first();
                if ($batch) {
                    $batch->qty_available = max(0, (int)$batch->qty_available - $newQty);
                    $batch->save();
                }

                $stock->addPurchaseReturnLedgerEntry([
                    'txn_date' => $purchaseReturnTransaction->return_date,
                    'ref_id' => $purchaseReturnTransaction->id,
                    'ref_line_id' => $line->id,
                    'item_id' => $pl?->item_id,
                    'qty_out' => $newQty,
                    'unit_price' => (int)$pl?->purchase_price,
                    'specification_snapshot' => $pl?->specification,
                    'created_by' => $purchaseReturnTransaction->created_by,
                ]);
            }

            return redirect()->route('returns.purchase.index')->with('status','Purchase return updated successfully.');
        });
    }

    public function destroy(PurchaseReturnTransaction $purchaseReturnTransaction)
    {
        $purchaseReturnTransaction->load(['lines.purchaseLine']);

        return DB::transaction(function () use ($purchaseReturnTransaction) {
            // reverse return (restore stock)
            foreach ($purchaseReturnTransaction->lines as $rl) {
                $pl = $rl->purchaseLine;
                if (!$pl) continue;
                $batch = StockBatch::where('purchase_line_id', $pl->id)->lockForUpdate()->first();
                if ($batch) {
                    $batch->qty_available = (int)$batch->qty_available + (int)$rl->quantity;
                    $batch->save();
                }
            }

            StockLedger::query()->where('ref_table','purchase_return_transactions')->where('ref_id', $purchaseReturnTransaction->id)->delete();
            \App\Models\PurchaseReturnLine::query()->where('purchase_return_transaction_id', $purchaseReturnTransaction->id)->delete();
            $purchaseReturnTransaction->delete();

            return redirect()->route('returns.purchase.index')->with('status','Purchase return deleted successfully.');
        });
    }
}
