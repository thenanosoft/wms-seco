<?php

namespace App\Http\Controllers;

use App\Models\IssueLine;
use App\Models\PurchaseLine;
use App\Models\IssueReturnLine;
use App\Models\PurchaseReturnLine;
use App\Models\StockLedger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{

    private function streamCsv(string $filename, array $headerRow, \Closure $rowWriter)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return Response::stream(function () use ($headerRow, $rowWriter) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headerRow);
            $rowWriter($out);
            fclose($out);
        }, 200, $headers);
    }
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

    // ==========================
    // Stable v2 (no manual returns)
    // ==========================
    public function csvIssueReturns(Request $request)
    {
        $q = IssueReturnLine::query()
            ->select([
                'issue_return_transactions.return_date',
                'issue_return_transactions.reference_no',
                'issue_return_transactions.notes',
                'issue_return_transactions.created_by',
                'issue_return_lines.quantity',
                'issue_return_lines.unit_price',
                'groups.group_code',
                'items.item_code',
                'items.name as item_name',
            ])
            ->join('issue_return_transactions','issue_return_transactions.id','=','issue_return_lines.issue_return_transaction_id')
            ->join('issue_lines','issue_lines.id','=','issue_return_lines.issue_line_id')
            ->join('items','items.id','=','issue_return_lines.item_id')
            ->join('groups','groups.id','=','items.group_id')
            ->orderByDesc('issue_return_transactions.return_date')
            ->orderByDesc('issue_return_lines.id');

        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('from')) $q->whereDate('issue_return_transactions.return_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('issue_return_transactions.return_date', '<=', $request->to);

        return $this->streamCsv(
            'issue_returns_' . now()->format('Ymd_His') . '.csv',
            ['Date','Group Code','Item Code','Item','Qty','Unit Price','Total','Reference','Notes'],
            function($out) use ($q) {
                $q->chunk(1000, function($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r->return_date,
                            $r->group_code,
                            $r->item_code,
                            $r->item_name,
                            (string)$r->quantity,
                            number_format((float)$r->unit_price,2,'.',''),
                            number_format(((float)$r->unit_price*(float)$r->quantity),2,'.',''),
                            $r->reference_no,
                            $r->notes,
                        ]);
                    }
                });
            }
        );
    }

    public function csvPurchaseReturns(Request $request)
    {
        $q = PurchaseReturnLine::query()
            ->select([
                'purchase_return_transactions.return_date',
                'purchase_return_transactions.reference_no',
                'purchase_return_transactions.notes',
                'purchase_return_transactions.created_by',
                'purchase_return_lines.quantity',
                'purchase_return_lines.unit_price',
                'groups.group_code',
                'items.item_code',
                'items.name as item_name',
            ])
            ->join('purchase_return_transactions','purchase_return_transactions.id','=','purchase_return_lines.purchase_return_transaction_id')
            ->join('purchase_lines','purchase_lines.id','=','purchase_return_lines.purchase_line_id')
            ->join('items','items.id','=','purchase_return_lines.item_id')
            ->join('groups','groups.id','=','items.group_id')
            ->orderByDesc('purchase_return_transactions.return_date')
            ->orderByDesc('purchase_return_lines.id');

        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('from')) $q->whereDate('purchase_return_transactions.return_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('purchase_return_transactions.return_date', '<=', $request->to);

        return $this->streamCsv(
            'purchase_returns_' . now()->format('Ymd_His') . '.csv',
            ['Date','Group Code','Item Code','Item','Qty','Unit Price','Total','Reference','Notes'],
            function($out) use ($q) {
                $q->chunk(1000, function($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r->return_date,
                            $r->group_code,
                            $r->item_code,
                            $r->item_name,
                            (string)$r->quantity,
                            number_format((float)$r->unit_price,2,'.',''),
                            number_format(((float)$r->unit_price*(float)$r->quantity),2,'.',''),
                            $r->reference_no,
                            $r->notes,
                        ]);
                    }
                });
            }
        );
    }

public function csvFullHistory(Request $request)
{
    $query = StockLedger::query()
        ->select([
            'stock_ledger.txn_date',
            'stock_ledger.txn_type',
            'stock_ledger.qty_in',
            'stock_ledger.qty_out',
            'stock_ledger.unit_price',
            'stock_ledger.ref_table',
            'stock_ledger.ref_id',
            'stock_ledger.notes',
            'groups.group_code',
            'items.item_code',
            'items.name as item_name',
        ])
        ->join('items', 'items.id', '=', 'stock_ledger.item_id')
        ->join('groups', 'groups.id', '=', 'items.group_id')
        ->orderBy('stock_ledger.txn_date')
        ->orderBy('stock_ledger.id');

    if ($request->filled('group_id')) {
        $query->where('groups.id', $request->group_id);
    }

    if ($request->filled('item_id')) {
        $query->where('items.id', $request->item_id);
    }

    if ($request->filled('from')) {
        $query->whereDate('stock_ledger.txn_date', '>=', $request->from);
    }

    if ($request->filled('to')) {
        $query->whereDate('stock_ledger.txn_date', '<=', $request->to);
    }

    $filename = 'full_history_' . now()->format('Ymd_His') . '.csv';

    return response()->streamDownload(function () use ($query) {

        $out = fopen('php://output', 'w');

        // Header row
        fputcsv($out, [
            'Date',
            'Type',
            'Group Code',
            'Item Code',
            'Item Name',
            'Qty In',
            'Qty Out',
            'Unit Price',
            'Line Total',
            'Ref Table',
            'Ref ID',
            'Notes',
        ]);

        $query->chunk(2000, function ($rows) use ($out) {
            foreach ($rows as $r) {
                $qtyIn  = (float) ($r->qty_in ?? 0);
                $qtyOut = (float) ($r->qty_out ?? 0);
                $price  = (float) ($r->unit_price ?? 0);

                $lineTotal = ($qtyIn > 0 ? $qtyIn : $qtyOut) * $price;

                fputcsv($out, [
                    $r->txn_date,
                    $r->txn_type,
                    $r->group_code,
                    $r->item_code,
                    $r->item_name,
                    $qtyIn ?: '',
                    $qtyOut ?: '',
                    number_format($price, 2, '.', ''),
                    number_format($lineTotal, 2, '.', ''),
                    $r->ref_table,
                    $r->ref_id,
                    $r->notes,
                ]);
            }
        });

        fclose($out);

    }, $filename, [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}

}
