<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchases PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #111; }
        .report-header { text-align: center; margin: 0 0 12px; }
        .report-header .company { font-size: 16px; font-weight: 700; }
        .report-header .title { font-size: 12px; font-weight: 700; margin-top: 6px; }
        .report-header .meta { font-size: 10px; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; }
        th { background: #f3f3f3; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
    @include('partials.report_header', ['title' => 'Purchase Items'])
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Supplier</th>
            <th>Group</th>
            <th>Item</th>
            <th>Spec</th>
            <th class="right">Qty In</th>
            <th class="right">Price</th>
            <th class="right">Total</th>
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
