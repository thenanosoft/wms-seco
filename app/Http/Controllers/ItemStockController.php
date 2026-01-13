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

        $history = StockLedger::query()
            ->where('item_id', $item->id)
            ->orderByDesc('txn_date')
            ->orderByDesc('id')
            ->paginate(50);

        return view('items.stock-show', [
            'item' => $item->load('group'),
            'summary' => $summary,
            'history' => $history,
        ]);
    }
}
