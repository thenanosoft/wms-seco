<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Item Stock Detail</h1>
            <div class="text-sm text-gray-600">
                <?php echo e($item->group->group_code); ?> | <?php echo e($item->item_code); ?> - <?php echo e($item->name); ?>

            </div>
        </div>

        <a href="<?php echo e(route('stock.index')); ?>"
           class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Back</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Available</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e($summary['available']); ?></div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total In</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e($summary['total_in']); ?></div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total Out</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e($summary['total_out']); ?></div>
        </div>
    </div>

    <div class="rounded-xl border bg-white overflow-x-auto">
        <div class="p-4 border-b">
            <div class="text-sm font-semibold">History (Ledger)</div>
            <div class="text-xs text-gray-600">All purchases, issues, and returns in one audit-safe timeline.</div>
        </div>

        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-right">Qty In</th>
                    <th class="px-4 py-3 text-right">Qty Out</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-left">Spec</th>
                    <th class="px-4 py-3 text-left">Ref</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2"><?php echo e($h->txn_date); ?></td>
                        <td class="px-4 py-2 font-semibold"><?php echo e($h->txn_type); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e($h->qty_in); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e($h->qty_out); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e(number_format($h->unit_price, 2)); ?></td>
                        <td class="px-4 py-2"><?php echo e($h->specification_snapshot); ?></td>
                        <td class="px-4 py-2 text-xs text-gray-600">
                            <?php echo e($h->ref_table); ?> #<?php echo e($h->ref_id); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <?php echo e($history->links()); ?>


</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/items/stock-show.blade.php ENDPATH**/ ?>