<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">FIFO Valuation Report</h1>
            <p class="mt-1 text-sm text-gray-600">Exact inventory value as of any date based on FIFO batches. Pending-price batches are valued as 0 until price is entered.</p>
        </div>
    </div>

    <form method="GET" class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-600">As of date</label>
                <input type="date" name="date" value="<?php echo e($asOf); ?>" class="mt-1 rounded-lg border-gray-200">
            </div>
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Run Report</button>
        </div>
    </form>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Group</th>
                        <th class="px-4 py-3 text-left font-semibold">Item</th>
                        <th class="px-4 py-3 text-right font-semibold">Qty</th>
                        <th class="px-4 py-3 text-right font-semibold">Value</th>
                        <th class="px-4 py-3 text-left font-semibold">Pending</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $summary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?php echo e($r->group_code); ?></td>
                            <td class="px-4 py-3">
                                <div class="font-medium"><?php echo e($r->item_code); ?> - <?php echo e($r->item_name); ?></div>
                            </td>
                            <td class="px-4 py-3 text-right"><?php echo e($r->qty); ?></td>
                            <td class="px-4 py-3 text-right"><?php echo e(number_format($r->value)); ?></td>
                            <td class="px-4 py-3">
                                <?php if($r->pending_batches > 0): ?>
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800"><?php echo e($r->pending_batches); ?> batch(es)</span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500">OK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-600" colspan="5">No stock found for this date.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold" colspan="3">Total</th>
                        <th class="px-4 py-3 text-right font-semibold"><?php echo e(number_format($grandTotal)); ?></th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <details class="rounded-xl border border-gray-200 bg-white p-4">
        <summary class="cursor-pointer text-sm font-semibold">Show batch-level breakdown</summary>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">Batch</th>
                        <th class="px-3 py-2 text-left font-semibold">Purchase Date</th>
                        <th class="px-3 py-2 text-left font-semibold">Item</th>
                        <th class="px-3 py-2 text-right font-semibold">Remaining</th>
                        <th class="px-3 py-2 text-right font-semibold">Unit Price</th>
                        <th class="px-3 py-2 text-right font-semibold">Value</th>
                        <th class="px-3 py-2 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-3 py-2">#<?php echo e($b->batch_id); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap"><?php echo e($b->purchase_date); ?></td>
                            <td class="px-3 py-2"><?php echo e($b->item_code); ?> - <?php echo e($b->item_name); ?></td>
                            <td class="px-3 py-2 text-right"><?php echo e($b->remaining_qty); ?></td>
                            <td class="px-3 py-2 text-right"><?php echo e($b->unit_price_display); ?></td>
                            <td class="px-3 py-2 text-right"><?php echo e(number_format($b->value)); ?></td>
                            <td class="px-3 py-2">
                                <?php if($b->price_pending): ?>
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Pending</span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500">OK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </details>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/reports/valuation.blade.php ENDPATH**/ ?>