<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Item Ledger PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color:#111; }
        .report-header { text-align: center; margin: 0 0 12px; }
        .report-header .company { font-size: 16px; font-weight: 700; }
        .report-header .title { font-size: 12px; font-weight: 700; margin-top: 6px; }
        .report-header .meta { font-size: 10px; margin-top: 2px; }

        .sub { margin: 6px 0 0; color:#444; text-align:center; }
        .muted { color:#666; }

        table { width:100%; border-collapse: collapse; margin-top:10px; }
        th, td { border:1px solid #ddd; padding:5px 6px; vertical-align: top; }
        th { background:#f4f4f4; text-align:left; }
        .right { text-align:right; }

        .badge { padding:2px 6px; border-radius: 10px; font-size: 9px; border:1px solid #ddd; display:inline-block; }
        .b-in { background:#ecfdf5; color:#047857; border-color:#a7f3d0; }
        .b-out { background:#fff1f2; color:#9f1239; border-color:#fecdd3; }
        .b-neutral { background:#f8fafc; color:#334155; border-color:#e2e8f0; }

        .totals { margin-top: 10px; text-align:center; }
        .totals .box { display:inline-block; margin:0 6px; padding:6px 10px; border:1px solid #ddd; border-radius:6px; background:#fafafa; }
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
        <tr>
            <td colspan="7" style="text-align:center; padding:10px;">No ledger entries found.</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
