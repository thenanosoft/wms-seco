<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseLine;
use App\Models\IssueLine;
use App\Models\ReturnLine;
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

        $returnIn = ReturnLine::query()
            ->join('return_transactions','return_transactions.id','=','return_lines.return_transaction_id')
            ->whereDate('return_transactions.return_date', $today)
            ->where('return_transactions.type','IN')
            ->selectRaw('COALESCE(SUM(return_lines.quantity),0) as qty, COALESCE(SUM(return_lines.line_total),0) as total')
            ->first();

        $returnOut = ReturnLine::query()
            ->join('return_transactions','return_transactions.id','=','return_lines.return_transaction_id')
            ->whereDate('return_transactions.return_date', $today)
            ->where('return_transactions.type','OUT')
            ->selectRaw('COALESCE(SUM(return_lines.quantity),0) as qty, COALESCE(SUM(return_lines.line_total),0) as total')
            ->first();

        $itemsCount = Item::count();

        return view('dashboard.index', compact('purchase','issue','returnIn','returnOut','itemsCount'));
    }

    
}
