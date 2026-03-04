<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Returns Print</title>
    @include('partials.report_theme_print')
</head>
<body>
    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    @include('partials.report_header', ['title' => 'Returns'])

    <table>
        <thead>
        <tr>
            <th class="w-10 nowrap">Date</th>
            <th class="w-12">Type</th>
            <th class="w-8">Group</th>
            <th class="w-30">Item</th>
            <th class="w-8 right nowrap">Qty</th>
            <th class="w-8 right nowrap">Price</th>
            <th class="w-10 right nowrap">Total</th>
            <th>Party</th>
            <th>Ref</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
            <tr>
                <td>{{ $r->return_date }}</td>
                <td>{{ $r->type === 'IN' ? 'INWARD' : 'OUTWARD' }}</td>
                <td>{{ $r->group_code }}</td>
                <td>{{ $r->item_code }} - {{ $r->item_name }}</td>
                <td class="right">{{ $r->quantity }}</td>
                <td class="right">{{ number_format((float)$r->unit_price, 4) }}</td>
                <td class="right">{{ number_format((float)$r->line_total, 4) }}</td>
                <td>{{ $r->party }}</td>
                <td>{{ $r->reference_no }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
