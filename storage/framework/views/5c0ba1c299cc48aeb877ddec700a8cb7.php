<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-semibold">Returns</h1>
        <a href="<?php echo e(route('returns.create')); ?>"
           class="rounded-lg bg-gray-900 px-4 py-2 text-white text-sm">
           New Return
        </a>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
    <a href="<?php echo e(route('print.returns', request()->query())); ?>" target="_blank"
       class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

    <a href="<?php echo e(route('export.returns.csv', request()->query())); ?>"
       class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

    <a href="<?php echo e(route('export.returns.pdf', request()->query())); ?>"
       class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
</div>


    <table class="w-full bg-white border rounded-xl">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">Date</th>
                <th class="p-3 text-left">Type</th>
                <th class="p-3 text-left">Party</th>
                <th class="p-3 text-left">Ref</th>
                <th class="p-3 text-left">User</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="border-t">
                <td class="p-3"><?php echo e($r->return_date->format('Y-m-d')); ?></td>
                <td class="p-3 font-semibold"><?php echo e($r->type === 'IN' ? 'Inward' : 'Outward'); ?></td>
                <td class="p-3"><?php echo e($r->party); ?></td>
                <td class="p-3"><?php echo e($r->reference_no); ?></td>
                <td class="p-3"><?php echo e($r->creator->name); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <?php echo e($returns->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/returns/index.blade.php ENDPATH**/ ?>