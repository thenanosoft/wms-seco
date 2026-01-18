<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseLine;
use App\Models\IssueLine;
use App\Models\IssueReturnLine;
use App\Models\PurchaseReturnLine;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $purchase = PurchaseLine::query()
            ->join('purchases','purchases.id','=','purchase_lines.purchase_id')
            ->whereDate('purchases.purchase_date', $today)
            ->selectRaw('COALESCE(SUM(purchase_lines.quantity),0) as qty, COALESCE(SUM(purchase_lines.line_total),0) as total')
            ->first();

        $issue = IssueLine::query()
            ->join('issues','issues.id','=','issue_lines.issue_id')
            ->whereDate('issues.issue_date', $today)
            ->selectRaw('COALESCE(SUM(issue_lines.quantity),0) as qty, COALESCE(SUM(issue_lines.line_total),0) as total')
            ->first();

        $returnIn = IssueReturnLine::query()
            ->join('issue_return_transactions','issue_return_transactions.id','=','issue_return_lines.issue_return_transaction_id')
            ->whereDate('issue_return_transactions.return_date', $today)
            ->selectRaw('COALESCE(SUM(issue_return_lines.quantity),0) as qty, COALESCE(SUM(issue_return_lines.line_total),0) as total')
            ->first();

        $returnOut = PurchaseReturnLine::query()
            ->join('purchase_return_transactions','purchase_return_transactions.id','=','purchase_return_lines.purchase_return_transaction_id')
            ->whereDate('purchase_return_transactions.return_date', $today)
            ->selectRaw('COALESCE(SUM(purchase_return_lines.quantity),0) as qty, COALESCE(SUM(purchase_return_lines.line_total),0) as total')
            ->first();

        // Payment/value summary (simple, audit-safe):
        // In value = sum(qty_in * unit_price), Out value = sum(qty_out * unit_price)
        $inValue = (float) StockLedger::query()->selectRaw('COALESCE(SUM(qty_in * unit_price),0) as v')->value('v');
        $outValue = (float) StockLedger::query()->selectRaw('COALESCE(SUM(qty_out * unit_price),0) as v')->value('v');
        $balanceValue = round($inValue - $outValue, 2);

        $itemsCount = Item::count();

        return view('dashboard.index', compact('purchase','issue','returnIn','returnOut','itemsCount','inValue','outValue','balanceValue'));
    }

    
}
