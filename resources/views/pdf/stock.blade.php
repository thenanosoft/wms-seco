<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock PDF</title>
    @include('partials.report_theme_pdf')
</head>
<body>
    @include('partials.report_header', ['title' => 'Stock Summary'])
    <table>
        <thead>
        <tr>
            <th class="w-10">Group</th>
            <th class="w-40">Item</th>
            <th class="w-15 right nowrap">Total In</th>
            <th class="w-15 right nowrap">Total Out</th>
            <th class="w-15 right nowrap">Balance</th>
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
