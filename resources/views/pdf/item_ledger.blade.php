<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Item Ledger PDF</title>
    @include('partials.report_theme_pdf')
</head>
<body>

@php
    $first = $rows->first();
    $item = $first?->item;
    $group = $item?->group;

    $totalIn = (float) $rows->sum(fn($r) => (float)($r->qty_in ?? 0));
    $totalOut = (float) $rows->sum(fn($r) => (float)($r->qty_out ?? 0));
    $balance = $totalIn - $totalOut;

    $label = function($type) {
        $t = strtoupper((string)$type);
        if (str_contains($t, 'PURCHASE') && !str_contains($t, 'RETURN')) return ['Purchase','b-in'];
        if (str_contains($t, 'ISSUE') && !str_contains($t, 'RETURN')) return ['Issue','b-out'];
        if (str_contains($t, 'ISSUE_RETURN')) return ['Issue Return','b-in'];
        if (str_contains($t, 'PURCHASE_RETURN')) return ['Purchase Return','b-out'];
        return [$type ?: 'N/A','b-neutral'];
    };
@endphp

@include('partials.report_header', ['title' => 'Item Ledger'])

<div class="sub">
    <span class="muted">Item:</span> {{ $item?->item_code }} - {{ $item?->name }}
    @if($group)
        <span class="muted"> | Group:</span> {{ $group->group_code }}{{ $group->group_name ? ' - '.$group->group_name : '' }}
    @endif
</div>

<div class="totals">
    <span class="box"><b>Total In:</b> {{ (int)$totalIn }}</span>
    <span class="box"><b>Total Out:</b> {{ (int)$totalOut }}</span>
    <span class="box"><b>Balance:</b> {{ (int)$balance }}</span>
</div>

<table>
    <thead>
    <tr>
        <th class="w-12 nowrap">Date</th>
        <th class="w-12">Type</th>
        <th class="w-8 right nowrap">Qty In</th>
        <th class="w-8 right nowrap">Qty Out</th>
        <th class="w-10 right nowrap">Unit Price</th>
        <th class="w-12 nowrap">Ref</th>
        <th class="w-30">Notes</th>
    </tr>
    </thead>
    <tbody>
    @forelse($rows as $r)
        @php
            [$tLabel, $tClass] = $label($r->txn_type);
            $ref = trim((string)($r->ref_table ?? '')) !== '' ? ($r->ref_table.' #'.$r->ref_id) : ('#'.$r->ref_id);
        @endphp
        <tr>
            <td>{{ optional($r->txn_date)->format('Y-m-d') }}</td>
            <td><span class="badge {{ $tClass }}">{{ $tLabel }}</span></td>
            <td class="right">{{ (int)($r->qty_in ?? 0) }}</td>
            <td class="right">{{ (int)($r->qty_out ?? 0) }}</td>
            <td class="right">{{ number_format((float)($r->unit_price ?? 0), 4) }}</td>
            <td class="muted">{{ $ref }}</td>
            <td>{{ $r->notes ?? '' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7" style="text-align:center; padding:10px;">No ledger entries found.</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
