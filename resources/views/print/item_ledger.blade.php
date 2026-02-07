<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Item Ledger</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color:#111; }
        .report-header { text-align: center; margin: 0 0 12px; }
        .report-header .company { font-size: 16px; font-weight: 700; }
        .report-header .title { font-size: 13px; font-weight: 700; margin-top: 6px; }
        .report-header .meta { font-size: 11px; margin-top: 2px; }
        .sub { margin: 6px 0 0; color:#444; text-align:center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
        th { background: #f4f4f4; text-align: left; }
        .right { text-align: right; }
        .muted { color:#666; }
        .badge { display:inline-block; padding:2px 8px; border-radius: 999px; font-size: 11px; border:1px solid #ddd; }
        .b-in { background:#ecfdf5; color:#047857; border-color:#a7f3d0; }
        .b-out { background:#fff1f2; color:#9f1239; border-color:#fecdd3; }
        .b-neutral { background:#f8fafc; color:#334155; border-color:#e2e8f0; }
        .totals { margin-top: 10px; display:flex; gap:16px; font-size: 12px; }
        .totals div { padding:6px 10px; border:1px solid #ddd; border-radius: 6px; background:#fafafa; }
        @media print { .no-print { display:none; } }
    </style>
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
        // Adjust these mappings if your txn_type values differ
        if (str_contains($t, 'PURCHASE') && !str_contains($t, 'RETURN')) return ['Purchase','b-in'];
        if (str_contains($t, 'ISSUE') && !str_contains($t, 'RETURN')) return ['Issue','b-out'];
        if (str_contains($t, 'ISSUE_RETURN')) return ['Issue Return','b-in'];
        if (str_contains($t, 'PURCHASE_RETURN')) return ['Purchase Return','b-out'];
        return [$type ?: 'N/A','b-neutral'];
    };
@endphp

<div class="no-print" style="margin-bottom:10px;">
    <button onclick="window.print()" style="padding:8px 12px; border:1px solid #ddd; border-radius:6px; background:#111; color:#fff; cursor:pointer;">Print</button>
</div>

@include('partials.report_header', ['title' => 'Item Ledger'])

<p class="sub">
    <span class="muted">Item:</span> {{ $item?->item_code }} - {{ $item?->name }}
    @if($group)
        <span class="muted">| Group:</span> {{ $group->group_code }}{{ $group->group_name ? ' - '.$group->group_name : '' }}
    @endif
</p>

<div class="totals" style="justify-content:center;">
    <div><b>Total In:</b> {{ (int)$totalIn }}</div>
    <div><b>Total Out:</b> {{ (int)$totalOut }}</div>
    <div><b>Balance:</b> {{ (int)$balance }}</div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:110px;">Date</th>
            <th style="width:130px;">Type</th>
            <th class="right" style="width:70px;">Qty In</th>
            <th class="right" style="width:70px;">Qty Out</th>
            <th class="right" style="width:90px;">Unit Price</th>
            <th style="width:130px;">Ref</th>
            <th>Notes</th>
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
                <td class="right">{{ number_format((float)($r->unit_price ?? 0), 0) }}</td>
                <td class="muted">{{ $ref }}</td>
                <td>{{ $r->notes ?? '' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">No ledger records found.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
