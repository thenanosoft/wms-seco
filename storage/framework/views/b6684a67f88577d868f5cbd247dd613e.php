<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Returns</h1>
        <p class="mt-1 text-sm text-gray-600">No manual returns. Use Issue Return (items coming back from issued) or Purchase Return (items going back to supplier).</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="text-sm font-semibold">Issue Return (Inward)</div>
            <div class="mt-1 text-sm text-gray-600">Return only from existing issues. Price auto from issue.</div>
            <div class="mt-4 flex gap-2">
                <a href="<?php echo e(route('returns.issue.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">View History</a>
                <a href="<?php echo e(route('returns.issue.create')); ?>" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">New Return</a>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="text-sm font-semibold">Purchase Return (Outward)</div>
            <div class="mt-1 text-sm text-gray-600">Return only from existing purchases. Quantity capped by stock.</div>
            <div class="mt-4 flex gap-2">
                <a href="<?php echo e(route('returns.purchase.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">View History</a>
                <a href="<?php echo e(route('returns.purchase.create')); ?>" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">New Return</a>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold">Return History</h2>
                <p class="text-sm text-gray-600">Filter inward (Issue Return) or outward (Purchase Return).</p>
            </div>

            <form method="GET" action="<?php echo e(route('returns.index')); ?>" class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="block text-xs text-gray-600">Type</label>
                    <select name="type" class="rounded-lg border-gray-200">
                        <option value="" <?php echo e(($type ?? '') === '' ? 'selected' : ''); ?>>All</option>
                        <option value="ISSUE_RETURN_IN" <?php echo e(($type ?? '') === 'ISSUE_RETURN_IN' ? 'selected' : ''); ?>>Inward (Issue Return)</option>
                        <option value="PURCHASE_RETURN_OUT" <?php echo e(($type ?? '') === 'PURCHASE_RETURN_OUT' ? 'selected' : ''); ?>>Outward (Purchase Return)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600">From</label>
                    <input type="date" name="from" value="<?php echo e($from ?? ''); ?>" class="rounded-lg border-gray-200">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">To</label>
                    <input type="date" name="to" value="<?php echo e($to ?? ''); ?>" class="rounded-lg border-gray-200">
                </div>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Apply</button>
                <a href="<?php echo e(route('returns.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
            </form>
        </div>

        <div class="mt-4 border rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Item</th>
                        <th class="px-3 py-2 text-right">Qty</th>
                        <th class="px-3 py-2 text-left">By</th>
                        <th class="px-3 py-2 text-left">Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php $__empty_1 = true; $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr>
        <td class="px-3 py-2"><?php echo e(optional($r->txn_date)->format('Y-m-d')); ?></td>

        <td class="px-3 py-2">
            <?php if($r->txn_type === 'ISSUE_RETURN_IN'): ?>
                <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">IN</span>
            <?php else: ?>
                <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700">OUT</span>
            <?php endif; ?>
        </td>

        <td class="px-3 py-2">
            <?php echo e($r->item?->item_code); ?> - <?php echo e($r->item?->name); ?>

        </td>

        <td class="px-3 py-2 text-right">
            <?php if($r->txn_type === 'ISSUE_RETURN_IN'): ?>
                <?php echo e((int)($r->qty_in ?? 0)); ?>

            <?php else: ?>
                <?php echo e((int)($r->qty_out ?? 0)); ?>

            <?php endif; ?>
        </td>

        <td class="px-3 py-2"><?php echo e($r->creator?->name); ?></td>

        <td class="px-3 py-2 text-gray-600">
            Ref #<?php echo e($r->ref_id); ?>

        </td>
    </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <tr><td class="px-3 py-3 text-gray-600" colspan="6">No return records.</td></tr>
<?php endif; ?>

                </tbody>
            </table>
        </div>

        <div class="mt-4"><?php echo e($returns->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/Data/Development/web/laravel/wms/resources/views/returns/home.blade.php ENDPATH**/ ?>