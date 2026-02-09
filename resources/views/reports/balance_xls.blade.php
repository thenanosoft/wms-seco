<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Report</title>
</head>
<body>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <td colspan="13" align="center"><strong>{{ config('app.name') }}</strong></td>
        </tr>
        <tr>
            <td colspan="13" align="center"><strong>Balance Report</strong></td>
        </tr>
        <tr>
            <td colspan="13" align="center">From: {{ $from ?: '-' }} | To: {{ $to ?: '-' }} | Generated: {{ now()->format('Y-m-d H:i:s') }} | By: {{ auth()->user()->role }} - {{ auth()->user()->name }}</td>
        </tr>
        <tr>
            <th>Group</th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Purchased Qty</th>
            <th>Purchased Amount</th>
            <th>Issued Qty</th>
            <th>Issued Amount</th>
            <th>Issue Return Qty</th>
            <th>Issue Return Amount</th>
            <th>Purchase Return Qty</th>
            <th>Purchase Return Amount</th>
            <th>Net Balance</th>
            <th>Net Amount</th>
        </tr>
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
                <td>{{ $r->item_code }}</td>
                <td>{{ $r->item_name }}</td>
                <td align="right">{{ (int)$r->purchased_qty }}</td>
                <td align="right">{{ (float)($r->purchased_amount ?? 0) }}</td>
                <td align="right">{{ (int)$r->issued_qty }}</td>
                <td align="right">{{ (float)($r->issued_amount ?? 0) }}</td>
                <td align="right">{{ (int)$r->issue_return_qty }}</td>
                <td align="right">{{ (float)($r->issue_return_amount ?? 0) }}</td>
                <td align="right">{{ (int)$r->purchase_return_qty }}</td>
                <td align="right">{{ (float)($r->purchase_return_amount ?? 0) }}</td>
                <td align="right"><strong>{{ (int)$r->net_balance }}</strong></td>
                <td align="right"><strong>{{ (float)($r->net_amount ?? 0) }}</strong></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="3" align="right"><strong>Totals</strong></td>
            <td align="right"><strong>{{ $tP }}</strong></td>
            <td align="right"><strong>{{ $tPA }}</strong></td>
            <td align="right"><strong>{{ $tI }}</strong></td>
            <td align="right"><strong>{{ $tIA }}</strong></td>
            <td align="right"><strong>{{ $tIR }}</strong></td>
            <td align="right"><strong>{{ $tIRA }}</strong></td>
            <td align="right"><strong>{{ $tPR }}</strong></td>
            <td align="right"><strong>{{ $tPRA }}</strong></td>
            <td align="right"><strong>{{ $tN }}</strong></td>
            <td align="right"><strong>{{ $tNA }}</strong></td>
        </tr>
    </table>
</body>
</html>
