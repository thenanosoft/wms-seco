<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseLine;
use App\Models\IssueLine;
use App\Models\IssueReturnLine;
use App\Models\PurchaseReturnLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $last30Start = now()->subDays(29)->toDateString();
        $period = in_array($request->string('period')->toString(), ['today', 'monthly', 'all'], true)
            ? $request->string('period')->toString()
            : 'today';

        $periodRanges = [
            'today' => [$today, $today],
            'monthly' => [$last30Start, $today],
            'all' => [null, null],
        ];

        $periodStats = [];
        foreach ($periodRanges as $key => [$from, $to]) {
            $purchase = PurchaseLine::query()
                ->join('purchases', 'purchases.id', '=', 'purchase_lines.purchase_id')
                ->when($from, fn($q) => $q->whereDate('purchases.purchase_date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('purchases.purchase_date', '<=', $to))
                ->selectRaw('COALESCE(SUM(purchase_lines.quantity),0) as qty, COALESCE(SUM(purchase_lines.line_total),0) as total')
                ->first();

            $issue = IssueLine::query()
                ->join('issues', 'issues.id', '=', 'issue_lines.issue_id')
                ->when($from, fn($q) => $q->whereDate('issues.issue_date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('issues.issue_date', '<=', $to))
                ->selectRaw('COALESCE(SUM(issue_lines.quantity),0) as qty, COALESCE(SUM(issue_lines.line_total),0) as total')
                ->first();

            $returnIn = IssueReturnLine::query()
                ->join('issue_return_transactions', 'issue_return_transactions.id', '=', 'issue_return_lines.issue_return_transaction_id')
                ->when($from, fn($q) => $q->whereDate('issue_return_transactions.return_date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('issue_return_transactions.return_date', '<=', $to))
                ->selectRaw('COALESCE(SUM(issue_return_lines.quantity),0) as qty, COALESCE(SUM(issue_return_lines.line_total),0) as total')
                ->first();

            $returnOut = PurchaseReturnLine::query()
                ->join('purchase_return_transactions', 'purchase_return_transactions.id', '=', 'purchase_return_lines.purchase_return_transaction_id')
                ->when($from, fn($q) => $q->whereDate('purchase_return_transactions.return_date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('purchase_return_transactions.return_date', '<=', $to))
                ->selectRaw('COALESCE(SUM(purchase_return_lines.quantity),0) as qty, COALESCE(SUM(purchase_return_lines.line_total),0) as total')
                ->first();

            $purchaseTotal = (float)($purchase->total ?? 0);
            $issueTotal = (float)($issue->total ?? 0);
            $returnInTotal = (float)($returnIn->total ?? 0);
            $returnOutTotal = (float)($returnOut->total ?? 0);

            $periodStats[$key] = (object)[
                'purchase_qty' => (float)($purchase->qty ?? 0),
                'purchase_total' => $purchaseTotal,
                'issue_qty' => (float)($issue->qty ?? 0),
                'issue_total' => $issueTotal,
                'return_in_qty' => (float)($returnIn->qty ?? 0),
                'return_in_total' => $returnInTotal,
                'return_out_qty' => (float)($returnOut->qty ?? 0),
                'return_out_total' => $returnOutTotal,
                'net_total' => ($purchaseTotal + $returnInTotal) - ($issueTotal + $returnOutTotal),
            ];
        }

        $stockPerItem = DB::table('stock_ledger')
            ->selectRaw('item_id, COALESCE(SUM(qty_in),0) - COALESCE(SUM(qty_out),0) as available_qty')
            ->groupBy('item_id')
            ->get();

        $totalAvailableQty = (float)$stockPerItem
            ->filter(fn($r) => (float)$r->available_qty > 0)
            ->sum(fn($r) => (float)$r->available_qty);

        $itemsInStock = (int)$stockPerItem
            ->filter(fn($r) => (float)$r->available_qty > 0)
            ->count();

        $itemsCount = Item::count();

        $stockValue = DB::table('stock_ledger')
            ->selectRaw('COALESCE(SUM((CAST(qty_in AS DECIMAL(24,8)) - CAST(qty_out AS DECIMAL(24,8))) * IFNULL(unit_price,0)),0) as v')
            ->value('v');
        $balanceValue = (float)$stockValue;

        $pendingPriceBatches = (int)DB::table('stock_batches')
            ->where('qty_available', '>', 0)
            ->whereNull('unit_price')
            ->count();

        return view('dashboard.index', [
            'period' => $period,
            'periodStats' => $periodStats,
            'currentStats' => $periodStats[$period],
            'itemsCount' => $itemsCount,
            'itemsInStock' => $itemsInStock,
            'totalAvailableQty' => $totalAvailableQty,
            'balanceValue' => $balanceValue,
            'pendingPriceBatches' => $pendingPriceBatches,
        ]);
    }
}
