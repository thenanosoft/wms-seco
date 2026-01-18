<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Dashboard</h1>
            <p class="text-sm text-gray-600">Daily overview for store operations.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('purchases.create')); ?>"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                New Purchase
            </a>

            <a href="<?php echo e(route('issues.create')); ?>"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                New Issue
            </a>

            <a href="<?php echo e(route('returns.create')); ?>"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                New Return
            </a>

            <?php if(auth()->user()?->role === 'admin'): ?>
                <a href="<?php echo e(route('stock.index')); ?>"
                   class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Stock
                </a>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Purchases</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e(number_format($purchase->total, 2)); ?></div>
            <div class="text-xs text-gray-600 mt-1">Qty: <?php echo e($purchase->qty); ?></div>
            <div class="mt-3">
                <a href="<?php echo e(route('purchases.index')); ?>" class="text-sm font-medium text-gray-900 underline">
                    View Purchases
                </a>
            </div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Issues</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e(number_format($issue->total, 2)); ?></div>
            <div class="text-xs text-gray-600 mt-1">Qty: <?php echo e($issue->qty); ?></div>
            <div class="mt-3">
                <a href="<?php echo e(route('issues.index')); ?>" class="text-sm font-medium text-gray-900 underline">
                    View Issues
                </a>
            </div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Return Inward</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e(number_format($returnIn->qty, 3)); ?></div>
            <div class="text-xs text-gray-600 mt-1">Value: <?php echo e(number_format($returnIn->total, 2)); ?></div>
            <div class="mt-3">
                <a href="<?php echo e(route('returns.index')); ?>" class="text-sm font-medium text-gray-900 underline">
                    View Returns
                </a>
            </div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Return Outward</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e(number_format($returnOut->qty, 3)); ?></div>
            <div class="text-xs text-gray-600 mt-1">Value: <?php echo e(number_format($returnOut->total, 2)); ?></div>
            <div class="mt-3">
                <a href="<?php echo e(route('returns.index')); ?>" class="text-sm font-medium text-gray-900 underline">
                    View Returns
                </a>
            </div>
        </div>

    </div>

    
    <?php if(auth()->user()?->role === 'admin'): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="rounded-xl border bg-white p-5">
                <div class="text-sm font-semibold">Admin Quick Actions</div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="<?php echo e(route('groups.index')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Groups</a>
                    <a href="<?php echo e(route('items.index')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Items</a>
                    <a href="<?php echo e(route('purchases.items.index')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Purchase Items List</a>
                    <a href="<?php echo e(route('backup.index')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Backup</a>
                    <a href="<?php echo e(route('settings.index')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Settings</a>
                </div>
            </div>

            <div class="rounded-xl border bg-white p-5">
                <div class="text-sm font-semibold">Exports</div>
                <div class="mt-1 text-xs text-gray-600">Download CSV/PDF quickly.</div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="<?php echo e(route('export.stock.csv')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Stock CSV</a>
                    <a href="<?php echo e(route('export.stock.pdf')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Stock PDF</a>
                    <a href="<?php echo e(route('export.purchases.csv')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Purchases CSV</a>
                    <a href="<?php echo e(route('export.purchases.pdf')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Purchases PDF</a>
                    <a href="<?php echo e(route('export.issues.csv')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Issues CSV</a>
                    <a href="<?php echo e(route('export.issues.pdf')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Issues PDF</a>
                    <a href="<?php echo e(route('export.returns.csv')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Returns CSV</a>
                    <a href="<?php echo e(route('export.returns.pdf')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Returns PDF</a>
                </div>
            </div>

            <div class="rounded-xl border bg-white p-5">
                <div class="text-sm font-semibold">Totals</div>
                <div class="mt-3">
                    <div class="text-sm text-gray-600">Total Items</div>
                    <div class="mt-1 text-2xl font-semibold"><?php echo e($itemsCount); ?></div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="rounded-xl border bg-white p-5">
            <div class="text-sm font-semibold">Quick Actions</div>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="<?php echo e(route('purchases.create')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">New Purchase</a>
                <a href="<?php echo e(route('issues.create')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">New Issue</a>
                <a href="<?php echo e(route('returns.create')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">New Return</a>
            </div>
        </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/dashboard/index.blade.php ENDPATH**/ ?>