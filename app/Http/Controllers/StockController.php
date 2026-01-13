<?php

namespace App\Http\Controllers;

use App\Services\StockService;

class StockController extends Controller
{
    public function index(StockService $stock)
    {
        $rows = $stock->stockSummaryWithLowFlag();
        return view('stock.index', compact('rows'));
    }
}
