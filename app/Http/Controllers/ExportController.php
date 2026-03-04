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

    private function printedByLabel(): string
    {
        $u = auth()->user();
        $username = $u?->username ?: ($u?->name ?: '');
        $role = (string)($u?->role ?? '');
        $roleLabel = $role === 'admin' ? 'Admin' : ($role === 'store_helper' ? 'Store Helper' : ucfirst(str_replace('_',' ', $role)));

        return trim($roleLabel . ($username ? ' - ' . $username : ''));
    }

    private function streamCsv(string $filename, string $title, array $headerRow, \Closure $rowWriter)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return Response::stream(function () use ($title, $headerRow, $rowWriter) {
            $out = fopen('php://output', 'w');

            // Meta rows (company, datetime, printed by)
            fputcsv($out, [config('app.name')]);
            fputcsv($out, [$title]);
            fputcsv($out, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($out, ['Printed By', $this->printedByLabel()]);
            fputcsv($out, []);

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
                'items.name as item_name',            ])
            ->join('purchases', 'purchases.id', '=', 'purchase_lines.purchase_id')
            ->join('items', 'items.id', '=', 'purchase_lines.item_id')
            ->join('groups', 'groups.id', '=', 'items.group_id')
            ->orderByDesc('purchases.purchase_date')
            ->orderByDesc('purchase_lines.id');

        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('from')) $q->whereDate('purchases.purchase_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('purchases.purchase_date', '<=', $request->to);
        if ($request->filled('purchase_id')) $q->where('purchases.id', (int)$request->purchase_id);
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
                \DB::raw('(SELECT COALESCE(SUM(quantity),0) FROM issue_return_lines irl WHERE irl.issue_line_id = issue_lines.id) as returned_qty'),            ])
            ->join('issues', 'issues.id', '=', 'issue_lines.issue_id')
            ->join('items', 'items.id', '=', 'issue_lines.item_id')
            ->join('groups', 'groups.id', '=', 'items.group_id')
            ->orderByDesc('issues.issue_date')
            ->orderByDesc('issue_lines.id');

        if ($request->filled('group_id')) $q->where('groups.id', $request->group_id);
        if ($request->filled('item_id')) $q->where('items.id', $request->item_id);
        if ($request->filled('from')) $q->whereDate('issues.issue_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('issues.issue_date', '<=', $request->to);
        if ($request->filled('issue_id')) $q->where('issues.id', (int)$request->issue_id);
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
            'items.name as item_name',        ])
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

            // Meta rows
            fputcsv($out, [config('app.name')]);
            fputcsv($out, ['Purchases']);
            fputcsv($out, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($out, ['Printed By', $this->printedByLabel()]);
            fputcsv($out, []);

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
                    number_format((float)$r->purchase_price, 4, '.', ''),
                    number_format((float)$r->line_total, 4, '.', ''),
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

            // Meta rows
            fputcsv($out, [config('app.name')]);
            fputcsv($out, ['Issues']);
            fputcsv($out, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($out, ['Printed By', $this->printedByLabel()]);
            fputcsv($out, []);

            fputcsv($out, [
                'Date','Group Code','Group Name','Item Code','Item Name','Specification','Qty Out','Returned','Net Qty','Price','Net Total','Issued To','Reference'
            ]);

            $tQty=0; $tRet=0; $tNet=0; $tAmount=0;
            foreach ($rows as $r) {
                $ret = (int)($r->returned_qty ?? 0);
                $net = max(0, (int)$r->quantity - $ret);
                $price = $r->issue_price === null ? null : (float)$r->issue_price;
                $netTotal = $price === null ? 0 : ($net * $price);

                $tQty += (int)$r->quantity;
                $tRet += $ret;
                $tNet += $net;
                $tAmount += $netTotal;

                fputcsv($out, [
                    $r->issue_date,
                    $r->group_code,
                    $r->group_name,
                    $r->item_code,
                    $r->item_name,
                    $r->specification,
                    (int)$r->quantity,
                    $ret,
                    $net,
                    $price === null ? 'PENDING' : number_format($price, 4, '.', ''),
                    number_format($netTotal, 4, '.', ''),
                    $r->issued_to,
                    $r->reference_no,
                ]);
            }

            // Totals row
            fputcsv($out, []);
            fputcsv($out, ['TOTALS','','','','','',$tQty,$tRet,$tNet,'',number_format($tAmount, 4, '.', ''),'','']);

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

            // Meta rows
            fputcsv($out, [config('app.name')]);
            fputcsv($out, ['Stock Summary']);
            fputcsv($out, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($out, ['Printed By', $this->printedByLabel()]);
            fputcsv($out, []);

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

        // Meta rows
        fputcsv($out, [config('app.name')]);
        fputcsv($out, ['Returns']);
        fputcsv($out, ['Generated At', now()->format('Y-m-d H:i:s')]);
        fputcsv($out, ['Printed By', $this->printedByLabel()]);
        fputcsv($out, []);

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
                number_format((float)$r->unit_price, 4, '.', ''),
                number_format((float)$r->line_total, 4, '.', ''),
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
                // Column name in DB is issue_price
                'issue_return_lines.issue_price as unit_price',
                'groups.group_code',
                'items.item_code',
                'items.name as item_name',            ])
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
            'Issue Returns',
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
                            number_format((float)$r->unit_price,4,'.',''),
                            number_format(((float)$r->unit_price*(float)$r->quantity),4,'.',''),
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
                // Column name in DB is purchase_price
                'purchase_return_lines.purchase_price as unit_price',
                'groups.group_code',
                'items.item_code',
                'items.name as item_name',            ])
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
            'Purchase Returns',
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
                            number_format((float)$r->unit_price,4,'.',''),
                            number_format(((float)$r->unit_price*(float)$r->quantity),4,'.',''),
                            $r->reference_no,
                            $r->notes,
                        ]);
                    }
                });
            }
        );
    }

    public function printItemLedger(int $itemId)
{
    $rows = StockLedger::query()
        ->with(['item.group'])
        ->where('item_id', $itemId)
        ->orderBy('txn_date')
        ->orderBy('id')
        ->get();

    return view('print.item_ledger', compact('rows'));
}

public function pdfItemLedger(int $itemId)
{
    $rows = StockLedger::query()
        ->with(['item.group'])
        ->where('item_id', $itemId)
        ->orderBy('txn_date')
        ->orderBy('id')
        ->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
        'pdf.item_ledger',
        compact('rows')
    )->setPaper('a4', 'landscape');

    return $pdf->download(
        'item_ledger_' . $itemId . '_' . now()->format('Ymd_His') . '.pdf'
    );
}



public function csvFullHistory(Request $request)
{
    // Full file from Stock Ledger with extra business fields
    // It includes: supplier (purchase), issued_to (issue), and reference numbers where available.
    set_time_limit(0);

    // Quick date presets
    if ($request->filled('date_preset') && !$request->filled('from') && !$request->filled('to')) {
        $preset = $request->string('date_preset')->toString();
        if ($preset === 'today') {
            $request->merge(['from' => now()->toDateString(), 'to' => now()->toDateString()]);
        } elseif ($preset === 'week') {
            $request->merge(['from' => now()->startOfWeek()->toDateString(), 'to' => now()->endOfWeek()->toDateString()]);
        } elseif ($preset === 'month') {
            $request->merge(['from' => now()->startOfMonth()->toDateString(), 'to' => now()->endOfMonth()->toDateString()]);
        }
    }


    // Simple type filter used by Settings screen
    if ($request->filled('type') && !$request->filled('txn_types')) {
        $t = $request->string('type')->toString();
        $map = [
            'purchase' => 'PURCHASE',
            'issue' => 'ISSUE',
            'issue_return' => 'ISSUE_RETURN_IN',
            'purchase_return' => 'PURCHASE_RETURN_OUT',
        ];
        if (isset($map[$t])) {
            $request->merge(['txn_types' => $map[$t]]);
        }
    }

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
            'groups.id as group_id',
            'groups.group_code',
            'items.id as item_id',
            'items.item_code',
            'items.name as item_name',            'purchases.supplier_name',
            'purchases.reference_no as purchase_ref_no',
            'issues.issued_to',
            'issues.reference_no as issue_ref_no',
        ])
        ->join('items', 'items.id', '=', 'stock_ledger.item_id')
        ->join('groups', 'groups.id', '=', 'items.group_id')
        ->leftJoin('purchases', function ($join) {
            $join->on('purchases.id', '=', 'stock_ledger.ref_id')
                ->where('stock_ledger.ref_table', '=', 'purchases');
        })
        ->leftJoin('issues', function ($join) {
            $join->on('issues.id', '=', 'stock_ledger.ref_id')
                ->where('stock_ledger.ref_table', '=', 'issues');
        })
        ->orderBy('stock_ledger.txn_date')
        ->orderBy('stock_ledger.id');

    if ($request->filled('group_id')) {
        $query->where('groups.id', (int)$request->group_id);
    }
    if ($request->filled('item_id')) {
        $query->where('items.id', (int)$request->item_id);
    }
    if ($request->filled('supplier')) {
        $query->where('purchases.supplier_name', $request->supplier);
    }
    if ($request->filled('issued_to')) {
        $query->where('issues.issued_to', $request->issued_to);
    }

    if ($request->filled('q')) {
        $term = $request->string('q')->toString();
        $query->where(function ($w) use ($term) {
            $like = '%' . $term . '%';
            $w->where('purchases.supplier_name', 'like', $like)
              ->orWhere('purchases.reference_no', 'like', $like)
              ->orWhere('issues.issued_to', 'like', $like)
              ->orWhere('issues.reference_no', 'like', $like)
              ->orWhere('stock_ledger.ref_id', 'like', $like);
        });
    }

    // Transaction type filter (purchase / issue / returns etc.)
    if ($request->filled('txn_types')) {
        $types = collect(explode(',', (string)$request->txn_types))
            ->map(fn($x) => trim($x))
            ->filter();
        if ($types->isNotEmpty()) {
            $query->whereIn('stock_ledger.txn_type', $types->all());
        }
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

        // UTF-8 BOM for Excel
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Date',
            'Type',
            'Group Code',
            'Item Code',
            'Item Name',
            'Supplier',
            'Issued To',
            'Purchase Ref',
            'Issue Ref',
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
                $qtyIn  = (float)($r->qty_in ?? 0);
                $qtyOut = (float)($r->qty_out ?? 0);
                $price  = (float)($r->unit_price ?? 0);
                $lineTotal = ($qtyIn > 0 ? $qtyIn : $qtyOut) * $price;

                fputcsv($out, [
                    (string)$r->txn_date,
                    (string)$r->txn_type,
                    (string)$r->group_code,
                    (string)$r->item_code,
                    (string)$r->item_name,
                    (string)($r->supplier_name ?? ''),
                    (string)($r->issued_to ?? ''),
                    (string)($r->purchase_ref_no ?? ''),
                    (string)($r->issue_ref_no ?? ''),
                    $qtyIn ?: '',
                    $qtyOut ?: '',
                    number_format($price, 4, '.', ''),
                    number_format($lineTotal, 4, '.', ''),
                    (string)$r->ref_table,
                    (string)$r->ref_id,
                    (string)($r->notes ?? ''),
                ]);
            }
        });

        fclose($out);
    }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
}

}
