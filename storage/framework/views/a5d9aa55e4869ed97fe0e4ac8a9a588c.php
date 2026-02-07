<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchases PDF</title>
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
    <?php echo $__env->make('partials.report_header', ['title' => 'Purchase Items'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Supplier</th>
            <th>Group</th>
            <th>Item</th>
            <th>Spec</th>
            <th class="right">Qty In</th>
            <th class="right">Price</th>
            <th class="right">Total</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($r->purchase_date); ?></td>
                <td><?php echo e($r->supplier_name); ?></td>
                <td><?php echo e($r->group_code); ?></td>
                <td><?php echo e($r->item_code); ?> - <?php echo e($r->item_name); ?></td>
                <td><?php echo e($r->specification); ?></td>
                <td class="right"><?php echo e($r->quantity); ?></td>
                <td class="right"><?php echo e(number_format($r->purchase_price, 0)); ?></td>
                <td class="right"><?php echo e(number_format($r->line_total, 0)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/pdf/purchases.blade.php ENDPATH**/ ?>