<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Stock Summary</h1>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="<?php echo e(route('print.stock')); ?>" target="_blank"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

        <a href="<?php echo e(route('export.stock.csv')); ?>"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

        <a href="<?php echo e(route('export.stock.pdf')); ?>"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
    </div>


    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Group</th>
                    <th class="px-4 py-3 text-left">Item</th>
                    <th class="px-4 py-3 text-right">Total In</th>
                    <th class="px-4 py-3 text-right">Total Out</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3 text-left">Low Stock</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="<?php echo e($r->is_low ? 'bg-red-50' : ''); ?>">
                        <td class="px-4 py-2"><?php echo e($r->group_code); ?></td>
                        <td class="px-4 py-2"><a class="text-blue-700 hover:underline"
   href="<?php echo e(route('items.stock.show', $r->item_id)); ?>">
   <?php echo e($r->item_code); ?> – <?php echo e($r->item_name); ?>

</a></td>
                        <td class="px-4 py-2 text-right"><?php echo e($r->total_in); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e($r->total_out); ?></td>
                        <td class="px-4 py-2 text-right font-semibold">
                            <?php echo e($r->balance); ?>

                        </td>
                        <td class="px-4 py-2">
                            <?php if($r->is_low): ?>
                                <span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
                                    Low
                                </span>
                                <span> (Min: <?php echo e($r->threshold_used); ?>)</span>
                            <?php else: ?>
                                <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                    OK
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\wms\resources\views/stock/index.blade.php ENDPATH**/ ?>