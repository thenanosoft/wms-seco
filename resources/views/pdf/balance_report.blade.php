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
                <th class="w-10 right">O Qty</th>
                <th class="w-10 right">P Qty</th>
                <th class="w-10 right">P Amt</th>
                <th class="w-10 right">I Qty</th>
                <th class="w-10 right">I Amt</th>
                <th class="w-10 right">C Qty</th>
                <th class="w-10 right">Price</th>
                <th class="w-10 right">Net Amt</th>
            </tr>
        </thead>
        <tbody>
            @php($tO=0)
            @php($tP=0)
            @php($tPA=0)
            @php($tI=0)
            @php($tIA=0)
            @php($tC=0)
            @php($tUnit=0)
            @php($tNA=0)
            @foreach($rows as $r)
                @php($tO += (float)($r->opening_qty ?? 0))
                @php($tP += (float)$r->purchased_qty)
                @php($tPA += (float)($r->purchased_amount ?? 0))
                @php($tI += (float)$r->issued_qty)
                @php($tIA += (float)($r->issued_amount ?? 0))
                @php($tC += (float)($r->closing_qty ?? 0))
                @php($tUnit += (float)($r->per_item_price ?? 0))
                @php($tNA += (float)($r->net_amount ?? 0))
                <tr>
                    <td>{{ $r->group_code ?? '' }}</td>
                    <td>{{ $r->item_code }} - {{ $r->item_name }}</td>
                    <td class="right">{{ number_format((float)($r->opening_qty ?? 0),4) }}</td>
                    <td class="right">{{ number_format((float)$r->purchased_qty,4) }}</td>
                    <td class="right">{{ number_format((float)($r->purchased_amount ?? 0),4) }}</td>
                    <td class="right">{{ number_format((float)$r->issued_qty,4) }}</td>
                    <td class="right">{{ number_format((float)($r->issued_amount ?? 0),4) }}</td>
                    <td class="right"><strong>{{ number_format((float)($r->closing_qty ?? 0),4) }}</strong></td>
                    <td class="right">{{ number_format((float)($r->per_item_price ?? 0),4) }}</td>
                    <td class="right"><strong>{{ number_format((float)($r->net_amount ?? 0),4) }}</strong></td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="right">Total</td>
                <td class="right">{{ number_format($tO,4) }}</td>
                <td class="right">{{ number_format($tP,4) }}</td>
                <td class="right">{{ number_format($tPA,4) }}</td>
                <td class="right">{{ number_format($tI,4) }}</td>
                <td class="right">{{ number_format($tIA,4) }}</td>
                <td class="right">{{ number_format($tC,4) }}</td>
                <td class="right">{{ number_format(count($rows) > 0 ? ($tUnit / count($rows)) : 0,4) }}</td>
                <td class="right">{{ number_format($tNA,4) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
