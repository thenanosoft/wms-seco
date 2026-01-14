<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockLedger;
use App\Services\StockService;

class ItemStockController extends Controller
{
    public function show(Item $item, StockService $stock)
    {
        $summary = $stock->getAvailableStockDetailed($item->id);

        $from = request('from');
        $to   = request('to');

        // Base query (filtered) for ledger
        $base = StockLedger::query()
            ->where('item_id', $item->id)
            ->when($from, fn ($q) => $q->whereDate('txn_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('txn_date', '<=', $to))
            ->orderByDesc('txn_date')
            ->orderByDesc('id');

        // Purchase price history
        $purchaseHistory = (clone $base)
            ->where('txn_type', 'PURCHASE')
            ->paginate(30, ['*'], 'p');

        // Issue / Sale price history
        $saleHistory = (clone $base)
            ->where('txn_type', 'ISSUE')
            ->paginate(30, ['*'], 's');

        // Optional: full ledger for audit (if your blade needs it)
        // Keep it light to avoid heavy load
        $history = (clone $base)->paginate(50, ['*'], 'h');

        return view('items.stock-show', [
            'item' => $item->load('group'),
            'summary' => $summary,
            'purchaseHistory' => $purchaseHistory,
            'saleHistory' => $saleHistory,
            'history' => $history,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
