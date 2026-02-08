<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Issues Print</title>
    @include('partials.report_theme_print')
</head>
<body>
    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    @include('partials.report_header', ['title' => 'Issue Items'])

    <table>
        <thead>
        <tr>
            <th class="w-10 nowrap">Date</th>
            <th class="w-8">Group</th>
            <th class="w-25">Item</th>
            <th>Specification</th>
            <th class="w-8 right nowrap">Qty Out</th>
            <th class="w-8 right nowrap">Price</th>
            <th class="w-10 right nowrap">Total</th>
            <th class="w-15">Issued To</th>
            <th class="w-10 nowrap">Ref</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
            <tr>
                <td>{{ $r->issue_date }}</td>
                <td>{{ $r->group_code }}</td>
                <td>{{ $r->item_code }} - {{ $r->item_name }}</td>
                <td>{{ $r->specification }}</td>
                <td class="right">{{ $r->quantity }}</td>
                <td class="right">{{ number_format($r->issue_price, 0) }}</td>
                <td class="right">{{ number_format($r->line_total, 0) }}</td>
                <td>{{ $r->issued_to }}</td>
                <td>{{ $r->reference_no }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
