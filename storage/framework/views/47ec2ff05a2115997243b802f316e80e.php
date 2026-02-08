<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Edit Issue #<?php echo e($issue->id); ?></h1>
            <p class="text-sm text-gray-600">You can edit header fields, decrease quantities, or remove lines. Prices are locked.</p>
        </div>
        <a href="<?php echo e(route('issues.show', $issue)); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Back</a>
    </div>

    <?php if($errors->any()): ?>
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('issues.update', $issue)); ?>" class="space-y-6">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="rounded-xl border bg-white p-4 sm:p-6">
            <h2 class="text-lg font-semibold mb-4">Issue Info</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Issue Date</label>
                    <input type="date" name="issue_date" value="<?php echo e(old('issue_date', $issue->issue_date->format('Y-m-d'))); ?>" class="mt-1 w-full rounded-lg border-gray-200">
                </div>
                <div>
                    <label class="text-sm font-medium">Issued To</label>
                    <input type="text" name="issued_to" value="<?php echo e(old('issued_to', $issue->issued_to)); ?>" class="mt-1 w-full rounded-lg border-gray-200">
                </div>
                <div>
                    <label class="text-sm font-medium">Reference No</label>
                    <input type="text" name="reference_no" value="<?php echo e(old('reference_no', $issue->reference_no)); ?>" class="mt-1 w-full rounded-lg border-gray-200">
                </div>
                <div>
                    <label class="text-sm font-medium">Notes</label>
                    <input type="text" name="notes" value="<?php echo e(old('notes', $issue->notes)); ?>" class="mt-1 w-full rounded-lg border-gray-200">
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-white overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h2 class="text-lg font-semibold">Issue Lines</h2>
                <p class="text-xs text-gray-600">If a line has returns, you cannot remove it and you cannot set quantity below returned qty.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Item</th>
                            <th class="px-4 py-3 text-right font-semibold">Price</th>
                            <th class="px-4 py-3 text-right font-semibold">Issued Qty</th>
                            <th class="px-4 py-3 text-right font-semibold">Returned Qty</th>
                            <th class="px-4 py-3 text-right font-semibold">New Qty</th>
                            <th class="px-4 py-3 text-center font-semibold">Remove</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $issue->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $retQty = (int)($returned[$l->id] ?? 0);
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium"><?php echo e($l->item->item_code); ?> - <?php echo e($l->item->name); ?></div>
                                    <div class="text-xs text-gray-500">Batch line: <?php echo e($l->purchase_line_id); ?></div>
                                </td>
                                <td class="px-4 py-3 text-right"><?php echo e(number_format($l->issue_price)); ?></td>
                                <td class="px-4 py-3 text-right"><?php echo e($l->quantity); ?></td>
                                <td class="px-4 py-3 text-right"><?php echo e($retQty); ?></td>
                                <td class="px-4 py-3 text-right">
                                    <input type="hidden" name="lines[<?php echo e($idx); ?>][id]" value="<?php echo e($l->id); ?>">
                                    <input type="number"
                                           name="lines[<?php echo e($idx); ?>][new_quantity]"
                                           min="<?php echo e($retQty); ?>"
                                           max="<?php echo e($l->quantity); ?>"
                                           value="<?php echo e(old('lines.'.$idx.'.new_quantity', $l->quantity)); ?>"
                                           class="w-28 rounded-lg border-gray-200 text-right">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox"
                                           name="lines[<?php echo e($idx); ?>][remove]"
                                           value="1"
                                           <?php echo e($retQty > 0 ? 'disabled' : ''); ?>

                                           title="<?php echo e($retQty > 0 ? 'Cannot remove because returns exist' : 'Remove line'); ?>">
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                Save Changes
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/issue/edit.blade.php ENDPATH**/ ?>