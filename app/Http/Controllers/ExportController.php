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

    private function returnsQuery(Request $request)
{
    $q = \App\Models\ReturnLine::query()
        ->select([
            'return_lines.*',
            'return_transactions.return_date',
            'return_transactions.type',
            'return_transactions.party',
            'return_transactions.reference_no',
            'groups.group_code',
            'groups.group_name',
            'items.item_code',
            'items.name as item_name',
        ])
        ->join('return_transactions', 'return_transactions.id', '=', 'return_lines.return_transaction_id')
        ->join('items', 'items.id', '=', 'return_lines.item_id')
        ->join('groups', 'groups.id', '=', 'items.group_id')
        ->orderByDesc('return_transactions.return_date')
        ->orderByDesc('return_lines.id');

    if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
    if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
    if ($request->filled('from')) $q->whereDate('return_transactions.return_date', '>=', $request->from);
    if ($request->filled('to')) $q->whereDate('return_transactions.return_date', '<=', $request->to);

    return $q;
}

    private function issueReturnsQuery(Request $request)
    {
        $q = \App\Models\IssueReturnLine::query()
            ->select([
                'issue_return_lines.*',
                'issue_returns.return_date',
                'issue_returns.issue_id',
                'issue_returns.received_from',
                'issue_returns.reference_no',
                'groups.group_code',
                'groups.group_name',
                'items.item_code',
                'items.name as item_name',
            ])
            ->join('issue_returns','issue_returns.id','=','issue_return_lines.issue_return_id')
            ->join('items','items.id','=','issue_return_lines.item_id')
            ->join('groups','groups.id','=','items.group_id')
            ->orderByDesc('issue_returns.return_date')
            ->orderByDesc('issue_return_lines.id');

        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('issue_id')) $q->where('issue_returns.issue_id', $request->issue_id);
        if ($request->filled('from')) $q->whereDate('issue_returns.return_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('issue_returns.return_date', '<=', $request->to);

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

    public function printReturns(Request $request)
{
    $rows = $this->returnsQuery($request)->limit(5000)->get();
    return view('print.returns', compact('rows'));
}

    public function printIssueReturns(Request $request)
    {
        $rows = $this->issueReturnsQuery($request)->limit(5000)->get();
        return view('print.issue-returns', compact('rows'));
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

    public function csvReturns(Request $request)
{
    $rows = $this->returnsQuery($request)->limit(200000)->get();

    $filename = 'returns_' . now()->format('Ymd_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="'.$filename.'"',
    ];

    $callback = function () use ($rows) {
        $out = fopen('php://output', 'w');

        fputcsv($out, [
            'Date','Type','Group Code','Item Code','Item Name','Qty','Price','Total','Party','Reference'
        ]);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r->return_date,
                $r->type === 'IN' ? 'INWARD' : 'OUTWARD',
                $r->group_code,
                $r->item_code,
                $r->item_name,
                $r->quantity,
                number_format((float)$r->unit_price, 2, '.', ''),
                number_format((float)$r->line_total, 2, '.', ''),
                $r->party,
                $r->reference_no,
            ]);
        }

        fclose($out);
    };

    return \Illuminate\Support\Facades\Response::stream($callback, 200, $headers);
}

    // PDF

    public function pdfPurchases(Request $request)
    {
        $rows = $this->purchasesQuery($request)->limit(5000)->get();
        $pdf = Pdf::loadView('pdf.purchases', compact('rows'))->setPaper('a4', 'landscape');
        return $pdf->download('purchases_' . now()->format('Ymd_His') . '.pdf');
    }

    public function csvIssueReturns(Request $request)
    {
        $rows = $this->issueReturnsQuery($request)->limit(20000)->get();
        $filename = 'issue_returns_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Return Date','Issue ID','Group Code','Item Code','Item Name','Spec','Price','Qty','Line Total','Received From','Reference'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->return_date,
                    $r->issue_id,
                    $r->group_code,
                    $r->item_code,
                    $r->item_name,
                    $r->specification_snapshot,
                    $r->unit_price,
                    $r->quantity,
                    $r->line_total,
                    $r->received_from,
                    $r->reference_no,
                ]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
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

    public function pdfReturns(Request $request)
{
    $rows = $this->returnsQuery($request)->limit(5000)->get();
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.returns', compact('rows'))->setPaper('a4', 'landscape');
    return $pdf->download('returns_' . now()->format('Ymd_His') . '.pdf');
}

    public function pdfIssueReturns(Request $request)
    {
        $rows = $this->issueReturnsQuery($request)->limit(20000)->get();
        $pdf = Pdf::loadView('pdf.issue-returns', compact('rows'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('issue_returns_' . now()->format('Ymd_His') . '.pdf');
    }
}
