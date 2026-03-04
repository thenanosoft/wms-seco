<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Item Ledger</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <?php echo $__env->make('partials.report_theme_print', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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

<div class="no-print" style="margin-bottom:10px;">
    <button onclick="window.print()" style="padding:8px 12px; border:1px solid #ddd; border-radius:6px; background:#111; color:#fff; cursor:pointer;">Print</button>
</div>

<?php echo $__env->make('partials.report_header', ['title' => 'Item Ledger'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<p class="sub">
    <span class="muted">Item:</span> <?php echo e($item?->item_code); ?> - <?php echo e($item?->name); ?>

    <?php if($group): ?>
        <span class="muted">| Group:</span> <?php echo e($group->group_code); ?><?php echo e($group->group_name ? ' - '.$group->group_name : ''); ?>

    <?php endif; ?>
</p>

<div class="totals" style="justify-content:center;">
    <div><b>Total In:</b> <?php echo e((int)$totalIn); ?></div>
    <div><b>Total Out:</b> <?php echo e((int)$totalOut); ?></div>
    <div><b>Balance:</b> <?php echo e((int)$balance); ?></div>
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
                <td class="right"><?php echo e(number_format((float)($r->unit_price ?? 0), 4)); ?></td>
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
<?php /**PATH /Users/Data/Development/web/laravel/wms/resources/views/print/item_ledger.blade.php ENDPATH**/ ?>