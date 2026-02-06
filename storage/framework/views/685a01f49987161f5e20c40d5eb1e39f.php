<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Issue Details</h1>
            <p class="text-sm text-gray-600">Issue #<?php echo e($issue->id); ?> · <?php echo e(optional($issue->issue_date)->format('Y-m-d')); ?></p>
        </div>
        <a href="<?php echo e(route('issues.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Back</a>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        <a href="<?php echo e(route('print.issues', request()->query())); ?>" target="_blank"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

        <a href="<?php echo e(route('export.issues.csv', request()->query())); ?>"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

        <a href="<?php echo e(route('export.issues.pdf', request()->query())); ?>"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500">Issued To</span><div class="font-medium"><?php echo e($issue->issued_to ?: '-'); ?></div></div>
            <div><span class="text-gray-500">Reference</span><div class="font-medium"><?php echo e($issue->reference_no ?: '-'); ?></div></div>
            <div><span class="text-gray-500">Created By</span><div class="font-medium"><?php echo e(optional($issue->creator)->name ?: '-'); ?></div></div>
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
                    <th class="px-4 py-2 text-left">Specification</th>
                    <th class="px-4 py-2 text-right">Line Total</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php $__currentLoopData = ($lines ?? $issue->lines ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                    <tr>
                        <td class="px-4 py-2">
                            <a href="<?php echo e(route('items.stock.show', $line->item_id)); ?>" class="text-indigo-600 hover:underline">
                                <?php echo e(optional($line->item)->item_code); ?> - <?php echo e(optional($line->item)->name); ?>

                            </a>
                        </td>
                        <td class="px-4 py-2 text-right"><?php echo e((int) $line->quantity); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e(number_format((float) $line->issue_price, 0)); ?></td>
                        <td class="px-4 py-2"><?php echo e($line->specification ?: ''); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e(number_format((float) $line->line_total, 0)); ?></td>

                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if(($issue->lines ?? collect())->isEmpty()): ?>
    <tr>
        <td colspan="4" class="px-4 py-6 text-center text-gray-500">No items found for this issue.</td>
    </tr>
<?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\wms\resources\views/issue/show.blade.php ENDPATH**/ ?>