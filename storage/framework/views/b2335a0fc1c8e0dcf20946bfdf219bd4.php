<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #111; }
        h2 { margin: 0 0 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; }
        th { background: #f3f3f3; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2>Stock Summary</h2>
    <table>
        <thead>
        <tr>
            <th>Group</th>
            <th>Item</th>
            <th class="right">Total In</th>
            <th class="right">Total Out</th>
            <th class="right">Balance</th>
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
<?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/pdf/stock.blade.php ENDPATH**/ ?>