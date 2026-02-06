<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Item Ledger</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color:#111; }
        .header { margin-bottom: 12px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .sub { margin: 4px 0 0; color:#444; }
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

<?php
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
?>

<div class="header">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
        <div>
            <p class="title">Item Ledger</p>
            <p class="sub">
                <span class="muted">Generated:</span> <?php echo e(now()->format('Y-m-d H:i')); ?>

            </p>
            <p class="sub">
                <span class="muted">Item:</span>
                <?php echo e($item?->item_code); ?> - <?php echo e($item?->name); ?>

                <?php if($group): ?>
                    <span class="muted">| Group:</span> <?php echo e($group->group_code); ?> <?php echo e($group->group_name ? ' - '.$group->group_name : ''); ?>

                <?php endif; ?>
            </p>
        </div>

        <div class="no-print">
            <button onclick="window.print()" style="padding:8px 12px; border:1px solid #ddd; border-radius:6px; background:#111; color:#fff; cursor:pointer;">
                Print
            </button>
        </div>
    </div>

    <div class="totals">
        <div><b>Total In:</b> <?php echo e((int)$totalIn); ?></div>
        <div><b>Total Out:</b> <?php echo e((int)$totalOut); ?></div>
        <div><b>Balance:</b> <?php echo e((int)$balance); ?></div>
    </div>
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
        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                [$tLabel, $tClass] = $label($r->txn_type);
                $ref = trim((string)($r->ref_table ?? '')) !== '' ? ($r->ref_table.' #'.$r->ref_id) : ('#'.$r->ref_id);
            ?>
            <tr>
                <td><?php echo e(optional($r->txn_date)->format('Y-m-d')); ?></td>
                <td><span class="badge <?php echo e($tClass); ?>"><?php echo e($tLabel); ?></span></td>
                <td class="right"><?php echo e((int)($r->qty_in ?? 0)); ?></td>
                <td class="right"><?php echo e((int)($r->qty_out ?? 0)); ?></td>
                <td class="right"><?php echo e(number_format((float)($r->unit_price ?? 0), 0)); ?></td>
                <td class="muted"><?php echo e($ref); ?></td>
                <td><?php echo e($r->notes ?? ''); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="muted">No ledger records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
<?php /**PATH C:\laragon\www\wms\resources\views/print/item_ledger.blade.php ENDPATH**/ ?>