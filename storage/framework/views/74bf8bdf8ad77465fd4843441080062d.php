<div class="rounded-xl border border-gray-200 bg-white">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold">Date</th>
                    <th class="px-4 py-2 text-left font-semibold">Type</th>
                    <th class="px-4 py-2 text-left font-semibold">Ref</th>
                    <th class="px-4 py-2 text-right font-semibold">Qty In</th>
                    <th class="px-4 py-2 text-right font-semibold">Qty Out</th>
                    <th class="px-4 py-2 text-right font-semibold">Price</th>
                    <th class="px-4 py-2 text-right font-semibold">Balance</th>
                    <th class="px-4 py-2 text-left font-semibold">By</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-4 py-2">
                            <?php echo e(\Carbon\Carbon::parse($r->txn_date)->format('d-m-Y')); ?>

                        </td>

                        <td class="px-4 py-2">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                <?php if($r->txn_type === 'PURCHASE'): ?> bg-green-100 text-green-800
                                <?php elseif($r->txn_type === 'ISSUE'): ?> bg-red-100 text-red-800
                                <?php elseif(str_contains($r->txn_type,'RETURN')): ?> bg-blue-100 text-blue-800
                                <?php else: ?> bg-gray-100 text-gray-800
                                <?php endif; ?>">
                                <?php echo e(str_replace('_',' ', $r->txn_type)); ?>

                            </span>
                        </td>

                        <td class="px-4 py-2">
                            <?php echo e($r->ref_no ?? '-'); ?>

                        </td>

                        <td class="px-4 py-2 text-right">
                            <?php echo e(number_format($r->qty_in, 3)); ?>

                        </td>

                        <td class="px-4 py-2 text-right">
                            <?php echo e(number_format($r->qty_out, 3)); ?>

                        </td>

                        <td class="px-4 py-2 text-right">
                            <?php echo e(number_format($r->unit_price, 2)); ?>

                        </td>

                        <td class="px-4 py-2 text-right font-semibold">
                            <?php echo e(number_format($r->running_balance, 3)); ?>

                        </td>

                        <td class="px-4 py-2 text-xs text-gray-600">
                            <?php echo e($r->user?->name ?? '-'); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                            No history found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/items/partials/_history_table.blade.php ENDPATH**/ ?>