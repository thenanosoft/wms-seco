<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock PDF</title>
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
    @include('partials.report_header', ['title' => 'Stock Summary'])
    <table>
        <thead>
        <tr>
            <th>Group</th>
            <th>Item</th>
            <th class="right">Total In</th>
            <th class="right">Total Out</th>
            <th class="right">Balance</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
            <tr>
                <td>{{ $r->group_code }}</td>
                <td>{{ $r->item_code }} - {{ $r->item_name }}</td>
                <td class="right">{{ $r->total_in }}</td>
                <td class="right">{{ $r->total_out }}</td>
                <td class="right"><b>{{ $r->balance }}</b></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
