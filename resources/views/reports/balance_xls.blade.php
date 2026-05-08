<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Report</title>
</head>
<body>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <td colspan="11" align="center"><strong>{{ config('app.name') }}</strong></td>
        </tr>
        <tr>
            <td colspan="11" align="center"><strong>Balance Report</strong></td>
        </tr>
        <tr>
            <td colspan="11" align="center">From: {{ $from ?: '-' }} | To: {{ $to ?: '-' }} | Generated: {{ now()->format('Y-m-d H:i:s') }} | By: {{ auth()->user()->role }} - {{ auth()->user()->name }}</td>
        </tr>
        <tr>
            <th>Group</th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Opening Qty</th>
            <th>Purchased Qty</th>
            <th>Purchased Amount</th>
            <th>Issued Qty</th>
            <th>Issued Amount</th>
            <th>Closing Qty</th>
            <th>Per Item Price</th>
            <th>Net Amount</th>
        </tr>
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
                <td>{{ $r->item_code }}</td>
                <td>{{ $r->item_name }}</td>
                <td align="right">{{ (float)($r->opening_qty ?? 0) }}</td>
                <td align="right">{{ (float)$r->purchased_qty }}</td>
                <td align="right">{{ (float)($r->purchased_amount ?? 0) }}</td>
                <td align="right">{{ (float)$r->issued_qty }}</td>
                <td align="right">{{ (float)($r->issued_amount ?? 0) }}</td>
                <td align="right"><strong>{{ (float)($r->closing_qty ?? 0) }}</strong></td>
                <td align="right">{{ (float)($r->per_item_price ?? 0) }}</td>
                <td align="right"><strong>{{ (float)($r->net_amount ?? 0) }}</strong></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="3" align="right"><strong>Totals</strong></td>
            <td align="right"><strong>{{ $tO }}</strong></td>
            <td align="right"><strong>{{ $tP }}</strong></td>
            <td align="right"><strong>{{ $tPA }}</strong></td>
            <td align="right"><strong>{{ $tI }}</strong></td>
            <td align="right"><strong>{{ $tIA }}</strong></td>
            <td align="right"><strong>{{ $tC }}</strong></td>
            <td align="right"><strong>{{ count($rows) > 0 ? ($tUnit / count($rows)) : 0 }}</strong></td>
            <td align="right"><strong>{{ $tNA }}</strong></td>
        </tr>
    </table>
</body>
</html>
