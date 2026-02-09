<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BalanceReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $query = DB::table('stock_ledger')
            ->join('items', 'items.id', '=', 'stock_ledger.item_id')
            ->leftJoin('groups', 'groups.id', '=', 'items.group_id')
            ->select([
                'items.id as item_id',
                'items.item_code',
                'items.name as item_name',
                'groups.group_code',
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' THEN stock_ledger.qty_in ELSE 0 END) as purchased_qty"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchased_amount"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' THEN stock_ledger.qty_out ELSE 0 END) as issued_qty"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issued_amount"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' THEN stock_ledger.qty_in ELSE 0 END) as issue_return_qty"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issue_return_amount"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' THEN stock_ledger.qty_out ELSE 0 END) as purchase_return_qty"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchase_return_amount"),
            ])
            ->when($from, fn($q) => $q->whereDate('stock_ledger.txn_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('stock_ledger.txn_date', '<=', $to));

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

        $rows = $query
            ->groupBy('items.id', 'items.item_code', 'items.name', 'groups.group_code')
            ->orderBy('groups.group_code')
            ->orderBy('items.item_code')
            ->get()
            ->map(function ($r) {
                $p = (int)$r->purchased_qty;
                $i = (int)$r->issued_qty;
                $ir = (int)$r->issue_return_qty;
                $pr = (int)$r->purchase_return_qty;
                $r->net_balance = $p + $ir - $i - $pr;

                $pa = (float)($r->purchased_amount ?? 0);
                $ia = (float)($r->issued_amount ?? 0);
                $ira = (float)($r->issue_return_amount ?? 0);
                $pra = (float)($r->purchase_return_amount ?? 0);
                $r->net_amount = $pa + $ira - $ia - $pra;
                return $r;
            });

        $groups = DB::table('groups')->orderBy('group_code')->get(['id', 'group_code']);
        $items = DB::table('items')->orderBy('item_code')->get(['id', 'item_code', 'name']);

        return view('reports.balance', compact('rows', 'groups', 'items', 'from', 'to'));
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
                'Purchased Qty', 'Purchased Amount',
                'Issued Qty', 'Issued Amount',
                'Issue Return Qty', 'Issue Return Amount',
                'Purchase Return Qty', 'Purchase Return Amount',
                'Net Balance', 'Net Amount'
            ]);
            foreach ($data as $r) {
                fputcsv($out, [
                    (string)($r->group_code ?? ''),
                    (string)$r->item_code,
                    (string)$r->item_name,
                    (int)$r->purchased_qty,
                    (float)($r->purchased_amount ?? 0),
                    (int)$r->issued_qty,
                    (float)($r->issued_amount ?? 0),
                    (int)$r->issue_return_qty,
                    (float)($r->issue_return_amount ?? 0),
                    (int)$r->purchase_return_qty,
                    (float)($r->purchase_return_amount ?? 0),
                    (int)$r->net_balance,
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
        $range = $request->get('range', 'today');
        $tz = config('app.timezone') ?: 'Asia/Karachi';
        $now = now($tz);

        $from = null;
        $to = null;

        if ($range === 'today') {
            $from = $now->toDateString();
            $to = $now->toDateString();
        } elseif ($range === 'weekly') {
            $from = $now->copy()->startOfWeek()->toDateString();
            $to = $now->copy()->endOfWeek()->toDateString();
        } elseif ($range === 'monthly') {
            $from = $now->copy()->startOfMonth()->toDateString();
            $to = $now->copy()->endOfMonth()->toDateString();
        } elseif ($range === 'yearly') {
            $from = $now->copy()->startOfYear()->toDateString();
            $to = $now->copy()->endOfYear()->toDateString();
        } elseif ($range === 'custom') {
            $from = $request->get('from') ?: null;
            $to = $request->get('to') ?: null;
        } elseif ($request->filled('from') || $request->filled('to')) {
            // Backward compatible
            $from = $request->get('from') ?: null;
            $to = $request->get('to') ?: null;
        }

        return [$from, $to];
    }

    private function getRows(Request $request, ?string $from, ?string $to)
    {
        // Reuse index query logic (without extra UI datasets)
        $tmp = new Request($request->all());
        $tmp->merge(['range' => $request->get('range', 'today'), 'from' => $from, 'to' => $to]);

        $query = DB::table('stock_ledger')
            ->join('items', 'items.id', '=', 'stock_ledger.item_id')
            ->leftJoin('groups', 'groups.id', '=', 'items.group_id')
            ->select([
                'items.id as item_id',
                'items.item_code',
                'items.name as item_name',
                'groups.group_code',
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' THEN stock_ledger.qty_in ELSE 0 END) as purchased_qty"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE' THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchased_amount"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' THEN stock_ledger.qty_out ELSE 0 END) as issued_qty"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE' THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issued_amount"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' THEN stock_ledger.qty_in ELSE 0 END) as issue_return_qty"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'ISSUE_RETURN_IN' THEN (stock_ledger.qty_in * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as issue_return_amount"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' THEN stock_ledger.qty_out ELSE 0 END) as purchase_return_qty"),
                DB::raw("SUM(CASE WHEN stock_ledger.txn_type = 'PURCHASE_RETURN_OUT' THEN (stock_ledger.qty_out * IFNULL(stock_ledger.unit_price,0)) ELSE 0 END) as purchase_return_amount"),
            ])
            ->when($from, fn($q) => $q->whereDate('stock_ledger.txn_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('stock_ledger.txn_date', '<=', $to));

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
            ->orderBy('items.item_code')
            ->get()
            ->map(function ($r) {
                $p = (int)$r->purchased_qty;
                $i = (int)$r->issued_qty;
                $ir = (int)$r->issue_return_qty;
                $pr = (int)$r->purchase_return_qty;
                $r->net_balance = $p + $ir - $i - $pr;
                $pa = (float)($r->purchased_amount ?? 0);
                $ia = (float)($r->issued_amount ?? 0);
                $ira = (float)($r->issue_return_amount ?? 0);
                $pra = (float)($r->purchase_return_amount ?? 0);
                $r->net_amount = $pa + $ira - $ia - $pra;
                return $r;
            });
    }
}
