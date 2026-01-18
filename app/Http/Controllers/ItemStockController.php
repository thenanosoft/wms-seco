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

    $base = StockLedger::query()
        ->with('creator')
        ->where('item_id', $item->id)
        ->when($from, fn ($q) => $q->whereDate('txn_date', '>=', $from))
        ->when($to, fn ($q) => $q->whereDate('txn_date', '<=', $to))
        ->orderByDesc('txn_date')
        ->orderByDesc('id');

    $purchaseHistory = (clone $base)
        ->where('txn_type', 'PURCHASE')
        ->paginate(30, ['*'], 'p');

    $saleHistory = (clone $base)
        ->where('txn_type', 'ISSUE')
        ->paginate(30, ['*'], 's');

    $issueReturnHistory = (clone $base)
        ->where('txn_type', 'ISSUE_RETURN_IN')
        ->paginate(30, ['*'], 'r');

    return view('items.stock-show', [
        'item' => $item->load('group'),
        'summary' => $summary,
        'purchaseHistory' => $purchaseHistory,
        'saleHistory' => $saleHistory,
        'issueReturnHistory' => $issueReturnHistory,
        'from' => $from,
        'to' => $to,
    ]);
}

}
