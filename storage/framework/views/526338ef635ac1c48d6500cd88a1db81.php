<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Issues PDF</title>
    <?php echo $__env->make('partials.report_theme_pdf', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>
    <?php echo $__env->make('partials.report_header', ['title' => 'Issue Items'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <table>
        <thead>
        <tr>
            <th class="w-10 nowrap">Date</th>
            <th class="w-15">Issued To</th>
            <th class="w-8">Group</th>
            <th class="w-25">Item</th>
            <th class="w-15">Spec</th>
            <th class="w-8 right nowrap">Qty Out</th>
            <th class="w-8 right nowrap">Price</th>
            <th class="w-10 right nowrap">Total</th>
            <th class="w-10 nowrap">Ref</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($r->issue_date); ?></td>
                <td><?php echo e($r->issued_to); ?></td>
                <td><?php echo e($r->group_code); ?></td>
                <td><?php echo e($r->item_code); ?> - <?php echo e($r->item_name); ?></td>
                <td><?php echo e($r->specification); ?></td>
                <td class="right"><?php echo e($r->quantity); ?></td>
                <td class="right"><?php echo e(number_format((float)$r->issue_price, 0)); ?></td>
                <td class="right"><?php echo e(number_format((float)$r->line_total, 0)); ?></td>
                <td><?php echo e($r->reference_no); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/pdf/issues.blade.php ENDPATH**/ ?>