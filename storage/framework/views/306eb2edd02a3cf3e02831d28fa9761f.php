<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Settings</h1>
        <p class="text-sm text-gray-600">Low stock alerts and backup schedule.</p>
    </div>

    <?php if(session('status')): ?>
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('settings.update')); ?>" class="space-y-6">
        <?php echo csrf_field(); ?>

        <div class="rounded-xl border bg-white p-4 sm:p-6">
            <h2 class="text-lg font-semibold mb-4">Low Stock</h2>

            <label class="text-sm font-medium">Default Low Stock Threshold</label>
            <input type="number" step="1" min="0"
                   name="default_low_stock_threshold"
                   value="<?php echo e(old('default_low_stock_threshold', $defaultLow)); ?>"
                   class="mt-1 w-64 rounded-lg border-gray-200">
            <div class="text-xs text-gray-600 mt-1">Used when item specific threshold is empty.</div>
        </div>

        <div class="flex justify-end gap-3">
            <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                Save Settings
            </button>
        </div>
    </form>

    <!-- Full Export (CSV) -->
    <div class="mt-8 rounded-xl border bg-white p-4 sm:p-6">
        <div class="mb-4">
            <h2 class="text-lg font-semibold">Full Export (CSV)</h2>
            <p class="text-sm text-gray-600">Export complete history including supplier and issued-to details. If you do not select any filter, the system will export everything.</p>
        </div>

        <form method="GET" action="<?php echo e(route('export.ledger.csv')); ?>" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm font-medium">Quick Date</label>
                    <select name="date_preset" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">Custom</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">From</label>
                    <input type="date" name="from" class="mt-1 w-full rounded-lg border-gray-200" value="<?php echo e(request('from')); ?>">
                </div>

                <div>
                    <label class="text-sm font-medium">To</label>
                    <input type="date" name="to" class="mt-1 w-full rounded-lg border-gray-200" value="<?php echo e(request('to')); ?>">
                </div>

                <div>
                    <label class="text-sm font-medium">Type</label>
                    <select name="type" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        <option value="purchase">Purchases (In)</option>
                        <option value="issue">Issues (Out)</option>
                        <option value="issue_return">Issue Returns (In)</option>
                        <option value="purchase_return">Purchase Returns (Out)</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Supplier</label>
                    <select name="supplier" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        <?php $__currentLoopData = ($suppliers ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s); ?>"><?php echo e($s); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Issued To</label>
                    <select name="issued_to" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        <?php $__currentLoopData = ($issuedTos ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($it); ?>"><?php echo e($it); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Group</label>
                    <select name="group_id" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        <?php $__currentLoopData = ($groups ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($g->id); ?>"><?php echo e($g->group_code); ?><?php echo e($g->group_name ? ' - '.$g->group_name : ''); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Item</label>
                    <select name="item_id" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        <?php $__currentLoopData = ($items ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($i->id); ?>"><?php echo e($i->item_code); ?> - <?php echo e($i->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="<?php echo e(route('settings.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Download CSV</button>
            </div>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/settings/index.blade.php ENDPATH**/ ?>