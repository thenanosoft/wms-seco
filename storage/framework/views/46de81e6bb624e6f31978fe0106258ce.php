<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchases Print</title>
    <?php echo $__env->make('partials.report_theme_print', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>
    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    <?php echo $__env->make('partials.report_header', ['title' => 'Purchase Items'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <table>
        <thead>
        <tr>
            <th class="w-10 nowrap">Date</th>
            <th class="w-8">Group</th>
            <th class="w-25">Item</th>
            <th>Specification</th>
            <th class="w-8 right nowrap">Qty In</th>
            <th class="w-8 right nowrap">Price</th>
            <th class="w-10 right nowrap">Total</th>
            <th class="w-15">Supplier</th>
            <th>Ref</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($r->purchase_date); ?></td>
                <td><?php echo e($r->group_code); ?></td>
                <td><?php echo e($r->item_code); ?> - <?php echo e($r->item_name); ?></td>
                <td><?php echo e($r->specification); ?></td>
                <td class="right"><?php echo e($r->quantity); ?></td>
                <td class="right"><?php echo e(number_format($r->purchase_price, 0)); ?></td>
                <td class="right"><?php echo e(number_format($r->line_total, 0)); ?></td>
                <td><?php echo e($r->supplier_name); ?></td>
                <td><?php echo e($r->reference_no); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/print/purchases.blade.php ENDPATH**/ ?>