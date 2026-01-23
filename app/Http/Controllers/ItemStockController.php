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

        $request = request();
        $type = $request->query('type');
        $from = $request->query('from');
        $to = $request->query('to');

        $q = StockLedger::query()
            ->where('item_id', $item->id)
            ->orderByDesc('txn_date')
            ->orderByDesc('id');

        if ($type) {
            $q->where('txn_type', $type);
        }
        if ($from) {
            $q->whereDate('txn_date', '>=', $from);
        }
        if ($to) {
            $q->whereDate('txn_date', '<=', $to);
        }

        $history = $q->paginate(50)->appends($request->query());

        return view('items.stock-show', [
            'item' => $item->load('group'),
            'summary' => $summary,
            'history' => $history,
            'filterType' => $type,
            'filterFrom' => $from,
            'filterTo' => $to,
        ]);
    }
}
