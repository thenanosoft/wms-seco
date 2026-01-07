<?php

namespace App\Http\Controllers;

use App\Models\IssueLine;
use App\Models\PurchaseLine;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    private function purchasesQuery(Request $request)
    {
        $q = PurchaseLine::query()
            ->select([
                'purchase_lines.*',
                'purchases.purchase_date',
                'purchases.supplier_name',
                'purchases.reference_no',
                'groups.group_code',
                'groups.group_name',
                'items.item_code',
                'items.name as item_name',
            ])
            ->join('purchases', 'purchases.id', '=', 'purchase_lines.purchase_id')
            ->join('items', 'items.id', '=', 'purchase_lines.item_id')
            ->join('groups', 'groups.id', '=', 'items.group_id')
            ->orderByDesc('purchases.purchase_date')
            ->orderByDesc('purchase_lines.id');

        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('from')) $q->whereDate('purchases.purchase_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('purchases.purchase_date', '<=', $request->to);

        return $q;
    }

    private function issuesQuery(Request $request)
    {
        $q = IssueLine::query()
            ->select([
                'issue_lines.*',
                'issues.issue_date',
                'issues.issued_to',
                'issues.reference_no',
                'groups.group_code',
                'groups.group_name',
                'items.item_code',
                'items.name as item_name',
            ])
            ->join('issues', 'issues.id', '=', 'issue_lines.issue_id')
            ->join('items', 'items.id', '=', 'issue_lines.item_id')
            ->join('groups', 'groups.id', '=', 'items.group_id')
            ->orderByDesc('issues.issue_date')
            ->orderByDesc('issue_lines.id');

        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('from')) $q->whereDate('issues.issue_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('issues.issue_date', '<=', $request->to);

        return $q;
    }

    // PRINT

    public function printPurchases(Request $request)
    {
        $rows = $this->purchasesQuery($request)->limit(5000)->get();
        return view('print.purchases', compact('rows'));
    }

    public function printIssues(Request $request)
    {
        $rows = $this->issuesQuery($request)->limit(5000)->get();
        return view('print.issues', compact('rows'));
    }

    public function printStock(StockService $stock)
    {
        $rows = $stock->stockSummary();
        return view('print.stock', compact('rows'));
    }

    // CSV

    public function csvPurchases(Request $request)
    {
        $rows = $this->purchasesQuery($request)->limit(200000)->get();

        $filename = 'purchases_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Date','Group Code','Group Name','Item Code','Item Name','Specification','Qty In','Price','Total','Supplier','Reference'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->purchase_date,
                    $r->group_code,
                    $r->group_name,
                    $r->item_code,
                    $r->item_name,
                    $r->specification,
                    $r->quantity,
                    number_format((float)$r->purchase_price, 2, '.', ''),
                    number_format((float)$r->line_total, 2, '.', ''),
                    $r->supplier_name,
                    $r->reference_no,
                ]);
            }

            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function csvIssues(Request $request)
    {
        $rows = $this->issuesQuery($request)->limit(200000)->get();

        $filename = 'issues_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Date','Group Code','Group Name','Item Code','Item Name','Specification','Qty Out','Price','Total','Issued To','Reference'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->issue_date,
                    $r->group_code,
                    $r->group_name,
                    $r->item_code,
                    $r->item_name,
                    $r->specification,
                    $r->quantity,
                    number_format((float)$r->issue_price, 2, '.', ''),
                    number_format((float)$r->line_total, 2, '.', ''),
                    $r->issued_to,
                    $r->reference_no,
                ]);
            }

            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function csvStock(StockService $stock)
    {
        $rows = $stock->stockSummary();
        $filename = 'stock_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Group Code','Item Code','Item Name','Total In','Total Out','Balance']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->group_code,
                    $r->item_code,
                    $r->item_name,
                    $r->total_in,
                    $r->total_out,
                    $r->balance,
                ]);
            }

            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    // PDF

    public function pdfPurchases(Request $request)
    {
        $rows = $this->purchasesQuery($request)->limit(5000)->get();
        $pdf = Pdf::loadView('pdf.purchases', compact('rows'))->setPaper('a4', 'landscape');
        return $pdf->download('purchases_' . now()->format('Ymd_His') . '.pdf');
    }

    public function pdfIssues(Request $request)
    {
        $rows = $this->issuesQuery($request)->limit(5000)->get();
        $pdf = Pdf::loadView('pdf.issues', compact('rows'))->setPaper('a4', 'landscape');
        return $pdf->download('issues_' . now()->format('Ymd_His') . '.pdf');
    }

    public function pdfStock(StockService $stock)
    {
        $rows = $stock->stockSummary();
        $pdf = Pdf::loadView('pdf.stock', compact('rows'))->setPaper('a4', 'landscape');
        return $pdf->download('stock_' . now()->format('Ymd_His') . '.pdf');
    }
}
