<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Returns Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        h2 { margin: 0 0 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; text-align: left; }
        .right { text-align: right; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    <h2>Returns</h2>

    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Group</th>
            <th>Item</th>
            <th class="right">Qty</th>
            <th class="right">Price</th>
            <th class="right">Total</th>
            <th>Party</th>
            <th>Ref</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($r->return_date); ?></td>
                <td><?php echo e($r->type === 'IN' ? 'INWARD' : 'OUTWARD'); ?></td>
                <td><?php echo e($r->group_code); ?></td>
                <td><?php echo e($r->item_code); ?> - <?php echo e($r->item_name); ?></td>
                <td class="right"><?php echo e($r->quantity); ?></td>
                <td class="right"><?php echo e(number_format($r->unit_price, 0)); ?></td>
                <td class="right"><?php echo e(number_format($r->line_total, 0)); ?></td>
                <td><?php echo e($r->party); ?></td>
                <td><?php echo e($r->reference_no); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\laragon\www\wms\resources\views/print/returns.blade.php ENDPATH**/ ?>