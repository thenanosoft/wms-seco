<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Item Ledger PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color:#111; }
        .title { font-size: 16px; font-weight: bold; margin: 0 0 4px; }
        .sub { margin: 0 0 2px; color:#444; }
        .muted { color:#666; }
        .box { border:1px solid #ddd; padding:6px 8px; margin-top:8px; }
        table { width:100%; border-collapse: collapse; margin-top:8px; }
        th, td { border:1px solid #ddd; padding:5px 6px; vertical-align: top; }
        th { background:#f4f4f4; text-align:left; }
        .right { text-align:right; }
        .badge { padding:2px 6px; border-radius: 10px; font-size: 9px; border:1px solid #ddd; display:inline-block; }
        .b-in { background:#ecfdf5; color:#047857; border-color:#a7f3d0; }
        .b-out { background:#fff1f2; color:#9f1239; border-color:#fecdd3; }
        .b-neutral { background:#f8fafc; color:#334155; border-color:#e2e8f0; }
        .row { width:100%; }
        .col { display:inline-block; vertical-align: top; }
        .col-60 { width:60%; }
        .col-40 { width:39%; text-align:right; }
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
        if (str_contains($t, 'PURCHASE') && !str_contains($t, 'RETURN')) return ['Purchase','b-in'];
        if (str_contains($t, 'ISSUE') && !str_contains($t, 'RETURN')) return ['Issue','b-out'];
        if (str_contains($t, 'ISSUE_RETURN')) return ['Issue Return','b-in'];
        if (str_contains($t, 'PURCHASE_RETURN')) return ['Purchase Return','b-out'];
        return [$type ?: 'N/A','b-neutral'];
    };
?>

<div class="row">
    <div class="col col-60">
        <div class="title">Item Ledger</div>
        <div class="sub"><span class="muted">Generated:</span> <?php echo e(now()->format('Y-m-d H:i')); ?></div>
        <div class="sub">
            <span class="muted">Item:</span> <?php echo e($item?->item_code); ?> - <?php echo e($item?->name); ?>

        </div>
        <?php if($group): ?>
            <div class="sub">
                <span class="muted">Group:</span> <?php echo e($group->group_code); ?><?php echo e($group->group_name ? ' - '.$group->group_name : ''); ?>

            </div>
        <?php endif; ?>
    </div>
    <div class="col col-40">
        <div class="box">
            <div><b>Total In:</b> <?php echo e((int)$totalIn); ?></div>
            <div><b>Total Out:</b> <?php echo e((int)$totalOut); ?></div>
            <div><b>Balance:</b> <?php echo e((int)$balance); ?></div>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:75px;">Date</th>
            <th style="width:95px;">Type</th>
            <th class="right" style="width:55px;">In</th>
            <th class="right" style="width:55px;">Out</th>
            <th class="right" style="width:70px;">Price</th>
            <th style="width:95px;">Ref</th>
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
<?php /**PATH C:\laragon\www\wms\resources\views/pdf/item_ledger.blade.php ENDPATH**/ ?>