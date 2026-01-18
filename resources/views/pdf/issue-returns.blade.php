<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th,td { border: 1px solid #ddd; padding: 5px; }
        th { background: #f3f4f6; text-align: left; }
        .h { font-size: 14px; font-weight: bold; }
        .sub { color: #555; margin: 6px 0 12px; }
    </style>
</head>
<body>
    <div class="h">Issue Returns (Return Against Issue)</div>
    <div class="sub">Generated: {{ now()->format('Y-m-d H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Issue ID</th>
                <th>Group</th>
                <th>Item</th>
                <th>Spec</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Received From</th>
                <th>Ref</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ $r->return_date }}</td>
                    <td>#{{ $r->issue_id }}</td>
                    <td>{{ $r->group_code }}</td>
                    <td>{{ $r->item_code }} - {{ $r->item_name }}</td>
                    <td>{{ $r->specification_snapshot }}</td>
                    <td>{{ number_format((float)$r->unit_price, 2) }}</td>
                    <td>{{ number_format((float)$r->quantity, 3) }}</td>
                    <td>{{ number_format((float)$r->line_total, 2) }}</td>
                    <td>{{ $r->received_from }}</td>
                    <td>{{ $r->reference_no }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
