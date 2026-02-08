<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchases PDF</title>
    @include('partials.report_theme_pdf')
</head>
<body>
    @include('partials.report_header', ['title' => 'Purchase Items'])
    <table>
        <thead>
        <tr>
            <th class="w-10 nowrap">Date</th>
            <th class="w-15">Supplier</th>
            <th class="w-8">Group</th>
            <th class="w-25">Item</th>
            <th class="w-15">Spec</th>
            <th class="w-8 right nowrap">Qty In</th>
            <th class="w-8 right nowrap">Price</th>
            <th class="w-10 right nowrap">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
            <tr>
                <td>{{ $r->purchase_date }}</td>
                <td>{{ $r->supplier_name }}</td>
                <td>{{ $r->group_code }}</td>
                <td>{{ $r->item_code }} - {{ $r->item_name }}</td>
                <td>{{ $r->specification }}</td>
                <td class="right">{{ $r->quantity }}</td>
                <td class="right">{{ number_format($r->purchase_price, 0) }}</td>
                <td class="right">{{ number_format($r->line_total, 0) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
