<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Print</title>
    <?php echo $__env->make('partials.report_theme_print', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>
    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    <?php echo $__env->make('partials.report_header', ['title' => 'Stock Summary'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <table>
        <thead>
        <tr>
            <th class="w-10">Group</th>
            <th class="w-40">Item</th>
            <th class="w-15 right nowrap">Total In</th>
            <th class="w-15 right nowrap">Total Out</th>
            <th class="w-15 right nowrap">Balance</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($r->group_code); ?></td>
                <td><?php echo e($r->item_code); ?> - <?php echo e($r->item_name); ?></td>
                <td class="right"><?php echo e($r->total_in); ?></td>
                <td class="right"><?php echo e($r->total_out); ?></td>
                <td class="right"><b><?php echo e($r->balance); ?></b></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/print/stock.blade.php ENDPATH**/ ?>