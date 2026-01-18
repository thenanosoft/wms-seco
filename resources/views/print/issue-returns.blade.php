<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Issue Returns - Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th,td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        .h { font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="h">Issue Returns (Return Against Issue)</div>
    <div style="margin: 6px 0 12px; color:#555">Printed: {{ now()->format('Y-m-d H:i') }}</div>

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

    <script>
        window.print();
    </script>
</body>
</html>
