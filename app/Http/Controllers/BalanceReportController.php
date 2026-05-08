<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BalanceReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $range] = $this->resolveDateRange($request);
        $rows = $this->getRows($request, $from, $to);

        $groups = DB::table('groups')->orderBy('group_code')->get(['id', 'group_code']);
        $allItems = DB::table('items')->orderBy('item_code')->get(['id', 'group_id', 'item_code', 'name']);

        $selectedGroupId = $request->filled('group_id') ? (int)$request->group_id : null;
        $items = $selectedGroupId
            ? $allItems->where('group_id', $selectedGroupId)->values()
            : $allItems;

        return view('reports.balance', compact('rows', 'groups', 'items', 'allItems', 'from', 'to', 'range'));
    }

    public function csv(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $data = $this->getRows($request, $from, $to);

        $filename = 'balance_report_' . now()->format('Ymd_His') . '.csv';
        return response()->streamDownload(function () use ($data, $from, $to) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [config('app.name')]);
            fputcsv($out, ['Balance Report']);
            fputcsv($out, ['From', $from ?: '-', 'To', $to ?: '-']);
            fputcsv($out, ['Generated', now()->format('Y-m-d H:i:s'), 'By', auth()->user()->role . ' - ' . auth()->user()->name]);
            fputcsv($out, []);
            fputcsv($out, [
                'Group', 'Item Code', 'Item Name',
                'Opening Qty',
                'Purchased Qty', 'Purchased Amount',
                'Issued Qty', 'Issued Amount',
                'Closing Qty', 'Per Item Price', 'Net Amount'
            ]);
            foreach ($data as $r) {
                fputcsv($out, [
                    (string)($r->group_code ?? ''),
                    (string)$r->item_code,
                    (string)$r->item_name,
                    (float)$r->opening_qty,
                    (float)$r->purchased_qty,
                    (float)($r->purchased_amount ?? 0),
                    (float)$r->issued_qty,
                    (float)($r->issued_amount ?? 0),
                    (float)$r->closing_qty,
                    (float)($r->per_item_price ?? 0),
                    (float)($r->net_amount ?? 0),
                ]);
            }
            fclose($out);
        }, $filename);
    }

    public function pdf(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $rows = $this->getRows($request, $from, $to);

        $pdf = Pdf::loadView('pdf.balance_report', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('balance_report_' . now()->format('Ymd_His') . '.pdf');
    }

    public function xls(Request $request)
    {
        // Excel-compatible HTML table (no external package required).
        [$from, $to] = $this->resolveDateRange($request);
        $rows = $this->getRows($request, $from, $to);

        $html = view('reports.balance_xls', compact('rows', 'from', 'to'))->render();
        $filename = 'balance_report_' . now()->format('Ymd_His') . '.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolveDateRange(Request $request): array
    {
        $range = (string)$request->get('range', 'all');
        $dateTouched = $request->boolean('date_touched');
        $tz = config('app.timezone') ?: 'Asia/Karachi';
        $now = now($tz);

        $from = null;
        $to = null;

        // If user manually selects dates, force custom behavior even if custom wasn't selected.
        if (($range === 'custom' || $dateTouched) && ($request->filled('from') || $request->filled('to'))) {
            $from = $request->get('from') ?: null;
            $to = $request->get('to') ?: null;
            return [$from, $to, 'custom'];
        }

        // Backward compatibility: old links may only send from/to without range.
        if (!$request->filled('range') && ($request->filled('from') || $request->filled('to'))) {
            $from = $request->get('from') ?: null;
            $to = $request->get('to') ?: null;
            return [$from, $to, 'custom'];
        }

        // On initial load (no explicit filters applied), show full list.
        $hasAnyFilterInput = $request->hasAny(['apply', 'range', 'group_id', 'item_id', 'q', 'from', 'to']);
        if (!$hasAnyFilterInput || $range === '' || $range === 'all') {
            return [null, null, 'all'];
        }

        if ($range === 'today') {
            // Only today's entries
            $from = $now->toDateString();
            $to = $now->toDateString();
        } elseif ($range === 'weekly') {
            // Last 7 days including today
            $from = $now->copy()->subDays(6)->toDateString();
            $to = $now->toDateString();
        } elseif ($range === 'monthly') {
            // Last 30 days including today
            $from = $now->copy()->subDays(29)->toDateString();
            $to = $now->toDateString();
        } elseif ($range === 'yearly') {
            // Last 365 days including today
            $from = $now->copy()->subDays(364)->toDateString();
            $to = $now->toDateString();
        }

        return [$from, $to, $range];
    }

    private function getRows(Request $request, ?string $from, ?string $to)
    {
        $query = $this->buildRowsQuery($request, $from, $to);
        $rows = $query->get();
        $asOfDate = $to ?: now((string)(config('app.timezone') ?: 'Asia/Karachi'))->toDateString();
        $itemIds = $rows->pluck('item_id')->map(fn($id) => (int)$id)->filter()->values()->all();
        $closingValuationByItem = $this->buildClosingValuationMap($itemIds, $asOfDate);

        return $this->mapRows($rows, $closingValuationByItem);
    }

    private function buildRowsQuery(Request $request, ?string $from, ?string $to)
    {
        $query = DB::table('stock_ledger')
            ->join('items', 'items.id', '=', 'stock_ledger.item_id')
            ->leftJoin('groups', 'groups.id', '=', 'items.group_id')
            ->select([
                'items.id as item_id',
                'items.item_code',
                'items.name as item_name',
                'groups.group_code',
                DB::raw("(SELECT sl2.unit_price
                    FROM stock_ledger sl2
                    WHERE sl2.item_id = items.id
                      AND sl2.unit_price IS NOT NULL
                      AND sl2.unit_price > 0
                    ORDER BY sl2.txn_date DESC, sl2.id DESC
                    LIMIT 1) as latest_known_price"),
            ]);

        // For opening/closing position, include historical rows up to TO date.
        $query->when($to, fn($q) => $q->whereDate('stock_ledger.txn_date', '<=', $to));

        if ($from) {
            $query->selectRaw(
                "SUM(CASE WHEN stock_ledger.txn_date < ? THEN (CAST(stock_ledger.qty_in AS DECIMAL(24,8)) - CAST(stock_ledger.qty_out AS DECIMAL(24,8))) ELSE 0 END) as opening_qty",
                [$from]
            );
        } else {
            $query->selectRaw("0 as opening_qty");
        }

        if ($from && $to) {
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' AND stock_ledger.txn_date >= ? AND stock_ledger.txn_date <= ? THEN stock_ledger.qty_in ELSE 0 END) as purchased_qty", [$from, $to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' AND stock_ledger.txn_date >= ? AND stock_ledger.txn_date <= ? THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchased_amount", [$from, $to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' AND stock_ledger.txn_date >= ? AND stock_ledger.txn_date <= ? THEN stock_ledger.qty_out ELSE 0 END) as issued_qty", [$from, $to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' AND stock_ledger.txn_date >= ? AND stock_ledger.txn_date <= ? THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issued_amount", [$from, $to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' AND stock_ledger.txn_date >= ? AND stock_ledger.txn_date <= ? THEN stock_ledger.qty_in ELSE 0 END) as issue_return_qty", [$from, $to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' AND stock_ledger.txn_date >= ? AND stock_ledger.txn_date <= ? THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issue_return_amount", [$from, $to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' AND stock_ledger.txn_date >= ? AND stock_ledger.txn_date <= ? THEN stock_ledger.qty_out ELSE 0 END) as purchase_return_qty", [$from, $to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' AND stock_ledger.txn_date >= ? AND stock_ledger.txn_date <= ? THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchase_return_amount", [$from, $to]);
        } elseif ($from) {
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' AND stock_ledger.txn_date >= ? THEN stock_ledger.qty_in ELSE 0 END) as purchased_qty", [$from]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' AND stock_ledger.txn_date >= ? THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchased_amount", [$from]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' AND stock_ledger.txn_date >= ? THEN stock_ledger.qty_out ELSE 0 END) as issued_qty", [$from]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' AND stock_ledger.txn_date >= ? THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issued_amount", [$from]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' AND stock_ledger.txn_date >= ? THEN stock_ledger.qty_in ELSE 0 END) as issue_return_qty", [$from]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' AND stock_ledger.txn_date >= ? THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issue_return_amount", [$from]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' AND stock_ledger.txn_date >= ? THEN stock_ledger.qty_out ELSE 0 END) as purchase_return_qty", [$from]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' AND stock_ledger.txn_date >= ? THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchase_return_amount", [$from]);
        } elseif ($to) {
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' AND stock_ledger.txn_date <= ? THEN stock_ledger.qty_in ELSE 0 END) as purchased_qty", [$to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' AND stock_ledger.txn_date <= ? THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchased_amount", [$to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' AND stock_ledger.txn_date <= ? THEN stock_ledger.qty_out ELSE 0 END) as issued_qty", [$to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' AND stock_ledger.txn_date <= ? THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issued_amount", [$to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' AND stock_ledger.txn_date <= ? THEN stock_ledger.qty_in ELSE 0 END) as issue_return_qty", [$to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' AND stock_ledger.txn_date <= ? THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issue_return_amount", [$to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' AND stock_ledger.txn_date <= ? THEN stock_ledger.qty_out ELSE 0 END) as purchase_return_qty", [$to]);
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' AND stock_ledger.txn_date <= ? THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchase_return_amount", [$to]);
        } else {
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' THEN stock_ledger.qty_in ELSE 0 END) as purchased_qty");
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchased_amount");
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' THEN stock_ledger.qty_out ELSE 0 END) as issued_qty");
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issued_amount");
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' THEN stock_ledger.qty_in ELSE 0 END) as issue_return_qty");
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issue_return_amount");
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' THEN stock_ledger.qty_out ELSE 0 END) as purchase_return_qty");
            $query->selectRaw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchase_return_amount");
        }

        if ($request->filled('group_id')) {
            $query->where('items.group_id', (int)$request->group_id);
        }
        if ($request->filled('item_id')) {
            $query->where('items.id', (int)$request->item_id);
        }
        if ($request->filled('q')) {
            $q = trim((string)$request->q);
            $query->where(function ($w) use ($q) {
                $w->where('items.item_code', 'like', "%{$q}%")
                  ->orWhere('items.name', 'like', "%{$q}%")
                  ->orWhere('groups.group_code', 'like', "%{$q}%");
            });
        }

        return $query
            ->groupBy('items.id', 'items.item_code', 'items.name', 'groups.group_code')
            ->orderBy('groups.group_code')
            ->orderBy('items.item_code');
    }

    private function buildClosingValuationMap(array $itemIds, string $asOfDate): array
    {
        if (empty($itemIds)) {
            return [];
        }

        $batchRows = DB::table('stock_batches as b')
            ->leftJoin('purchase_return_lines as prl', 'prl.purchase_line_id', '=', 'b.purchase_line_id')
            ->leftJoin('purchase_return_transactions as prt', 'prt.id', '=', 'prl.purchase_return_transaction_id')
            ->leftJoin('issue_lines as il', 'il.purchase_line_id', '=', 'b.purchase_line_id')
            ->leftJoin('issues as iss', 'iss.id', '=', 'il.issue_id')
            ->leftJoin('issue_return_lines as irl', 'irl.issue_line_id', '=', 'il.id')
            ->leftJoin('issue_return_transactions as irt', 'irt.id', '=', 'irl.issue_return_transaction_id')
            ->whereIn('b.item_id', $itemIds)
            ->whereDate('b.purchase_date', '<=', $asOfDate)
            ->groupBy('b.id', 'b.item_id', 'b.qty_purchased', 'b.unit_price')
            ->selectRaw(
                'b.item_id,
                b.qty_purchased,
                b.unit_price,
                COALESCE(SUM(CASE WHEN prt.return_date <= ? THEN prl.quantity ELSE 0 END),0) as purchase_return_out,
                COALESCE(SUM(CASE WHEN iss.issue_date <= ? THEN il.quantity ELSE 0 END),0) as issue_out,
                COALESCE(SUM(CASE WHEN irt.return_date <= ? THEN irl.quantity ELSE 0 END),0) as issue_return_in',
                [$asOfDate, $asOfDate, $asOfDate]
            )
            ->get();

        $valuationByItem = [];
        foreach ($batchRows as $r) {
            $remainingQty = (float)$r->qty_purchased
                - (float)$r->issue_out
                + (float)$r->issue_return_in
                - (float)$r->purchase_return_out;

            if ($remainingQty <= 0) {
                continue;
            }

            $unitPrice = $r->unit_price !== null ? (float)$r->unit_price : 0.0;
            $valuationByItem[(int)$r->item_id] = ($valuationByItem[(int)$r->item_id] ?? 0.0) + ($remainingQty * $unitPrice);
        }

        return $valuationByItem;
    }

    private function mapRows($rows, array $closingValuationByItem = [])
    {
        return $rows
            ->map(function ($r) use ($closingValuationByItem) {
                $opening = (float)($r->opening_qty ?? 0);
                $p = (float)$r->purchased_qty;
                $i = (float)$r->issued_qty;
                $ir = (float)$r->issue_return_qty;
                $pr = (float)$r->purchase_return_qty;
                $rangeNetQty = $p + $ir - $i - $pr;
                $r->opening_qty = $opening;
                $r->closing_qty = $opening + $rangeNetQty;
                $r->net_balance = $rangeNetQty;
                $closingValuation = (float)($closingValuationByItem[(int)$r->item_id] ?? 0.0);
                $r->net_amount = $closingValuation;
                $latestKnownPrice = (float)($r->latest_known_price ?? 0);
                $r->per_item_price = $r->closing_qty > 0.0 ? ($r->net_amount / $r->closing_qty) : $latestKnownPrice;
                return $r;
            });
    }
}
