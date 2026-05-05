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
            ->when($request->filled('group_id'), function ($q) use ($request) {
                $q->where('i.group_id', (int)$request->input('group_id'));
            })
            ->when($request->filled('item_id'), function ($q) use ($request) {
                $q->where('b.item_id', (int)$request->input('item_id'));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string)$request->input('q'));
                $q->where(function ($w) use ($term) {
                    $w->where('i.item_code', 'like', "%{$term}%")
                      ->orWhere('i.name', 'like', "%{$term}%")
                      ->orWhere('g.group_code', 'like', "%{$term}%");
                });
            })
            ->when($request->boolean('pending_only'), function ($q) {
                $q->whereNull('b.unit_price');
            })
            ->groupBy('b.id','b.item_id','b.purchase_line_id','b.purchase_date','b.qty_purchased','b.unit_price','i.item_code','i.name','g.id','g.group_code')
            ->selectRaw('b.id as batch_id, b.item_id, b.purchase_line_id, b.purchase_date,
                i.item_code, i.name as item_name, g.id as group_id, g.group_code,
                b.qty_purchased,
                COALESCE(SUM(CASE WHEN prt.return_date <= ? THEN prl.quantity ELSE 0 END),0) as purchase_return_out,
                COALESCE(SUM(CASE WHEN iss.issue_date <= ? THEN il.quantity ELSE 0 END),0) as issue_out,
                COALESCE(SUM(CASE WHEN irt.return_date <= ? THEN irl.quantity ELSE 0 END),0) as issue_return_in,
                b.unit_price
            ', [$asOf, $asOf, $asOf]);

        $batches = $batchRows->get()->map(function ($r) {
            $remaining = round((float)$r->qty_purchased - (float)$r->issue_out + (float)$r->issue_return_in - (float)$r->purchase_return_out, 4);
            if ($remaining < 0) {
                $remaining = 0; // safety
            }
            $pending = $r->unit_price === null;
            $price = $pending ? 0 : round((float)$r->unit_price, 4);
            $value = round($remaining * $price, 4);

            $r->remaining_qty = $remaining;
            $r->price_pending = $pending;
            $r->unit_price_display = $price;
            $r->value = $value;
            return $r;
        })->filter(function ($r) {
            return (float)$r->remaining_qty > 0;
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
                'qty' => (float)$qty,
                'value' => (float)$value,
                'avg_rate' => (float)$qty > 0 ? ((float)$value / (float)$qty) : 0.0,
                'pending_batches' => (int)$pendingBatches,
            ];
        })->sortBy(['group_code','item_code'])->values();

        $grandTotal = (float)$summary->sum('value');
        $totalQty = (float)$summary->sum('qty');
        $pendingBatches = (int)$batches->where('price_pending', true)->count();
        $pendingItems = (int)$summary->where('pending_batches', '>', 0)->count();

        $groupSummary = $summary->groupBy('group_code')->map(function ($rows, $groupCode) {
            return (object)[
                'group_code' => (string)$groupCode,
                'items_count' => (int)$rows->count(),
                'qty' => (float)$rows->sum('qty'),
                'value' => (float)$rows->sum('value'),
                'pending_items' => (int)$rows->where('pending_batches', '>', 0)->count(),
            ];
        })->sortByDesc('value')->values();

        $topItems = $summary->sortByDesc('value')->take(10)->values()->map(function ($row) use ($grandTotal) {
            $row->value_share = $grandTotal > 0 ? ((float)$row->value / $grandTotal) * 100 : 0;
            return $row;
        });

        $groups = DB::table('groups')->orderBy('group_code')->get(['id', 'group_code', 'group_name']);
        $allItems = DB::table('items')->orderBy('item_code')->get(['id', 'group_id', 'item_code', 'name']);
        $selectedGroupId = $request->filled('group_id') ? (int)$request->input('group_id') : null;
        $items = $selectedGroupId
            ? $allItems->where('group_id', $selectedGroupId)->values()
            : $allItems;

        return view('reports.valuation', [
            'asOf' => $asOf,
            'summary' => $summary,
            'grandTotal' => $grandTotal,
            'batches' => $batches,
            'totalQty' => $totalQty,
            'pendingBatches' => $pendingBatches,
            'pendingItems' => $pendingItems,
            'groupSummary' => $groupSummary,
            'topItems' => $topItems,
            'groups' => $groups,
            'items' => $items,
            'allItems' => $allItems,
        ]);
    }
}
