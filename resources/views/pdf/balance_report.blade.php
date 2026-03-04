<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Report</title>
    @include('partials.report_theme_pdf')
</head>
<body>
    @include('partials.report_header', ['title' => 'Balance Report'])

    <div style="margin-top: -6px; font-size: 11px; text-align:center;">
        From: {{ $from ?: '-' }} | To: {{ $to ?: '-' }}
    </div>
    <div class="report-divider"></div>

    <table>
        <thead>
            <tr>
                <th class="w-10">Group</th>
                <th class="w-25">Item</th>
                <th class="w-10 right">P Qty</th>
                <th class="w-10 right">P Amt</th>
                <th class="w-10 right">I Qty</th>
                <th class="w-10 right">I Amt</th>
                <th class="w-10 right">IR Qty</th>
                <th class="w-10 right">IR Amt</th>
                <th class="w-10 right">PR Qty</th>
                <th class="w-10 right">PR Amt</th>
                <th class="w-10 right">Net Qty</th>
                <th class="w-10 right">Net Amt</th>
            </tr>
        </thead>
        <tbody>
            @php($tP=0)
            @php($tPA=0)
            @php($tI=0)
            @php($tIA=0)
            @php($tIR=0)
            @php($tIRA=0)
            @php($tPR=0)
            @php($tPRA=0)
            @php($tN=0)
            @php($tNA=0)
            @foreach($rows as $r)
                @php($tP += (int)$r->purchased_qty)
                @php($tPA += (float)($r->purchased_amount ?? 0))
                @php($tI += (int)$r->issued_qty)
                @php($tIA += (float)($r->issued_amount ?? 0))
                @php($tIR += (int)$r->issue_return_qty)
                @php($tIRA += (float)($r->issue_return_amount ?? 0))
                @php($tPR += (int)$r->purchase_return_qty)
                @php($tPRA += (float)($r->purchase_return_amount ?? 0))
                @php($tN += (int)$r->net_balance)
                @php($tNA += (float)($r->net_amount ?? 0))
                <tr>
                    <td>{{ $r->group_code ?? '' }}</td>
                    <td>{{ $r->item_code }} - {{ $r->item_name }}</td>
                    <td class="right">{{ number_format((int)$r->purchased_qty,0) }}</td>
                    <td class="right">{{ number_format((float)($r->purchased_amount ?? 0),4) }}</td>
                    <td class="right">{{ number_format((int)$r->issued_qty,0) }}</td>
                    <td class="right">{{ number_format((float)($r->issued_amount ?? 0),4) }}</td>
                    <td class="right">{{ number_format((int)$r->issue_return_qty,0) }}</td>
                    <td class="right">{{ number_format((float)($r->issue_return_amount ?? 0),4) }}</td>
                    <td class="right">{{ number_format((int)$r->purchase_return_qty,0) }}</td>
                    <td class="right">{{ number_format((float)($r->purchase_return_amount ?? 0),4) }}</td>
                    <td class="right"><strong>{{ number_format((int)$r->net_balance,0) }}</strong></td>
                    <td class="right"><strong>{{ number_format((float)($r->net_amount ?? 0),4) }}</strong></td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="right">Total</td>
                <td class="right">{{ number_format($tP,0) }}</td>
                <td class="right">{{ number_format($tPA,4) }}</td>
                <td class="right">{{ number_format($tI,0) }}</td>
                <td class="right">{{ number_format($tIA,4) }}</td>
                <td class="right">{{ number_format($tIR,0) }}</td>
                <td class="right">{{ number_format($tIRA,4) }}</td>
                <td class="right">{{ number_format($tPR,0) }}</td>
                <td class="right">{{ number_format($tPRA,4) }}</td>
                <td class="right">{{ number_format($tN,0) }}</td>
                <td class="right">{{ number_format($tNA,4) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
