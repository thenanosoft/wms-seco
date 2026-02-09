<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Issues PDF</title>
    @include('partials.report_theme_pdf')
</head>
<body>
    @include('partials.report_header', ['title' => 'Issue Items'])

    <table>
        <thead>
        <tr>
            <th class="w-10 nowrap">Date</th>
            <th class="w-15">Issued To</th>
            <th class="w-8">Group</th>
            <th class="w-25">Item</th>
            <th class="w-15">Spec</th>
            <th class="w-8 right nowrap">Qty Out</th>
            <th class="w-8 right nowrap">Returned</th>
            <th class="w-8 right nowrap">Net Qty</th>
            <th class="w-8 right nowrap">Price</th>
            <th class="w-10 right nowrap">Net Total</th>
            <th class="w-10 nowrap">Ref</th>
        </tr>
        </thead>
        <tbody>
        @php($tQty=0)
        @php($tRet=0)
        @php($tNet=0)
        @php($tAmount=0)
        @foreach($rows as $r)
            @php($ret=(int)($r->returned_qty ?? 0))
            @php($net=max(0,(int)$r->quantity - $ret))
            @php($netTotal = ($r->issue_price === null) ? 0 : ($net * (int)$r->issue_price))
            @php($tQty += (int)$r->quantity)
            @php($tRet += $ret)
            @php($tNet += $net)
            @php($tAmount += (int)$netTotal)
            <tr>
                <td>{{ $r->issue_date }}</td>
                <td>{{ $r->issued_to }}</td>
                <td>{{ $r->group_code }}</td>
                <td>{{ $r->item_code }} - {{ $r->item_name }}</td>
                <td>{{ $r->specification }}</td>
                <td class="right">{{ $r->quantity }}</td>
                <td class="right">{{ $ret }}</td>
                <td class="right">{{ $net }}</td>
                <td class="right">
                    @if($r->issue_price === null)
                        Pending
                    @else
                        {{ number_format((float)$r->issue_price, 0) }}
                    @endif
                </td>
                <td class="right">{{ number_format((float)$netTotal, 0) }}</td>
                <td>{{ $r->reference_no }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
                <td colspan="5" class="right">Total</td>
                <td class="right">{{ number_format($tQty,0) }}</td>
                <td class="right">{{ number_format($tRet,0) }}</td>
                <td class="right">{{ number_format($tNet,0) }}</td>
                <td></td>
                <td class="right">{{ number_format($tAmount,0) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
