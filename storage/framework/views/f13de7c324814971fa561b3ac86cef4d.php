<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Purchase Details</h1>
            <p class="text-sm text-gray-600">Purchase #<?php echo e($purchase->id); ?> · <?php echo e(optional($purchase->purchase_date)->format('Y-m-d')); ?></p>
        </div>
        <a href="<?php echo e(route('purchases.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Back</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500">Supplier</span><div class="font-medium"><?php echo e($purchase->supplier_name ?: '-'); ?></div></div>
            <div><span class="text-gray-500">Reference</span><div class="font-medium"><?php echo e($purchase->reference ?: '-'); ?></div></div>
            <div><span class="text-gray-500">Created By</span><div class="font-medium"><?php echo e(optional($purchase->creator)->name ?: '-'); ?></div></div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Items</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-2 text-left">Item</th>
                    <th class="px-4 py-2 text-right">Qty</th>
                    <th class="px-4 py-2 text-right">Unit Price</th>
                    <th class="px-4 py-2 text-right">Specification</th>
                    <th class="px-4 py-2 text-right">Line Total</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php $__currentLoopData = $purchase->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="px-4 py-2">
                            <a href="<?php echo e(route('items.stock.show', $line->item_id)); ?>" class="text-indigo-600 hover:underline">
                                <?php echo e(optional($line->item)->item_code); ?> - <?php echo e(optional($line->item)->name); ?>

                            </a>
                        </td>
                        <td class="px-4 py-2 text-right"><?php echo e((int) $line->quantity); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e(number_format((float) $line->purchase_price, 0)); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e($line->specification ?: ''); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e(number_format((float) $line->line_total, 0)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/purchase/show.blade.php ENDPATH**/ ?>