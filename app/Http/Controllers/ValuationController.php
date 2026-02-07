<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValuationController extends Controller
{
    public function index(Request $request)
    {
        $asOf = $request->input('date');
        if (!$asOf) {
            $asOf = now()->toDateString();
        }

        // Batch level calculation as of a date
        // remaining_qty = qty_purchased - issues_out + issue_returns_in - purchase_returns_out
        $batchRows = DB::table('stock_batches as b')
            ->join('items as i', 'i.id', '=', 'b.item_id')
            ->join('groups as g', 'g.id', '=', 'i.group_id')
            ->leftJoin('purchase_return_lines as prl', 'prl.purchase_line_id', '=', 'b.purchase_line_id')
            ->leftJoin('purchase_return_transactions as prt', 'prt.id', '=', 'prl.purchase_return_transaction_id')
            ->leftJoin('issue_lines as il', 'il.purchase_line_id', '=', 'b.purchase_line_id')
            ->leftJoin('issues as iss', 'iss.id', '=', 'il.issue_id')
            ->leftJoin('issue_return_lines as irl', 'irl.issue_line_id', '=', 'il.id')
            ->leftJoin('issue_return_transactions as irt', 'irt.id', '=', 'irl.issue_return_transaction_id')
            ->whereDate('b.purchase_date', '<=', $asOf)
            ->groupBy('b.id','b.item_id','b.purchase_line_id','b.purchase_date','b.qty_purchased','b.unit_price','i.item_code','i.name','g.group_code')
            ->selectRaw('b.id as batch_id, b.item_id, b.purchase_line_id, b.purchase_date,
                i.item_code, i.name as item_name, g.group_code,
                b.qty_purchased,
                COALESCE(SUM(CASE WHEN prt.return_date <= ? THEN prl.quantity ELSE 0 END),0) as purchase_return_out,
                COALESCE(SUM(CASE WHEN iss.issue_date <= ? THEN il.quantity ELSE 0 END),0) as issue_out,
                COALESCE(SUM(CASE WHEN irt.return_date <= ? THEN irl.quantity ELSE 0 END),0) as issue_return_in,
                b.unit_price
            ', [$asOf, $asOf, $asOf]);

        $batches = $batchRows->get()->map(function ($r) {
            $remaining = (int)$r->qty_purchased - (int)$r->issue_out + (int)$r->issue_return_in - (int)$r->purchase_return_out;
            if ($remaining < 0) {
                $remaining = 0; // safety
            }
            $pending = $r->unit_price === null;
            $price = $pending ? 0 : (int)$r->unit_price;
            $value = $remaining * $price;

            $r->remaining_qty = $remaining;
            $r->price_pending = $pending;
            $r->unit_price_display = $price;
            $r->value = $value;
            return $r;
        })->filter(function ($r) {
            return (int)$r->remaining_qty > 0;
        })->values();

        // Item summary
        $summary = $batches->groupBy('item_id')->map(function ($rows) {
            $first = $rows->first();
            $qty = $rows->sum('remaining_qty');
            $value = $rows->sum('value');
            $pendingBatches = $rows->where('price_pending', true)->count();

            return (object)[
                'item_id' => $first->item_id,
                'group_code' => $first->group_code,
                'item_code' => $first->item_code,
                'item_name' => $first->item_name,
                'qty' => (int)$qty,
                'value' => (int)$value,
                'pending_batches' => (int)$pendingBatches,
            ];
        })->sortBy(['group_code','item_code'])->values();

        $grandTotal = (int)$summary->sum('value');

        return view('reports.valuation', [
            'asOf' => $asOf,
            'summary' => $summary,
            'grandTotal' => $grandTotal,
            'batches' => $batches,
        ]);
    }
}
