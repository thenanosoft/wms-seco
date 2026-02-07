<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Settings</h1>
        <p class="text-sm text-gray-600">Store name, timezone, low stock alerts, exports and CSV imports.</p>
    </div>

    <?php if(session('status')): ?>
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('settings.update')); ?>" class="space-y-6">
        <?php echo csrf_field(); ?>

        <div class="rounded-xl border bg-white p-4 sm:p-6">
            <h2 class="text-lg font-semibold mb-4">Store</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Store Name</label>
                    <input type="text"
                           name="store_name"
                           value="<?php echo e(old('store_name', $storeName ?? '')); ?>"
                           placeholder="Warehouse Store Management System"
                           class="mt-1 w-full rounded-lg border-gray-200">
                    <div class="text-xs text-gray-600 mt-1">Shown on header, exports, prints and PDFs.</div>
                </div>

                <div>
                    <label class="text-sm font-medium">Timezone</label>
                    <select name="timezone" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="Asia/Karachi" <?php echo e(old('timezone', $timezone ?? 'Asia/Karachi') === 'Asia/Karachi' ? 'selected' : ''); ?>>Asia/Karachi</option>
                    </select>
                    <div class="text-xs text-gray-600 mt-1">Fixed to Asia/Karachi for consistent dates.</div>
                </div>
            </div>
        </div>

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

    <!-- Pending Prices quick link -->
    <div class="rounded-xl border bg-white p-4 sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Admin Quick Views</h2>
                <p class="text-sm text-gray-600">One-click screens for daily admin work.</p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('pending_prices.index')); ?>" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Pending Prices</a>
                <a href="<?php echo e(route('reports.valuation.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">FIFO Valuation</a>
            </div>
        </div>
    </div>

    <!-- Full Export (CSV) -->
    <div class="rounded-xl border bg-white p-4 sm:p-6">
        <div class="mb-4">
            <h2 class="text-lg font-semibold">Full Export (CSV)</h2>
            <p class="text-sm text-gray-600">Filters support name + reference number. Leave empty to export everything.</p>
        </div>

        <form method="GET" action="<?php echo e(route('export.ledger.csv')); ?>" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                    <label class="text-sm font-medium">Search</label>
                    <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Name or Ref#">
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

    <!-- CSV Import -->
    <div class="rounded-xl border bg-white p-4 sm:p-6">
        <div class="mb-4">
            <h2 class="text-lg font-semibold">CSV Import</h2>
            <p class="text-sm text-gray-600">Import master data and transactions. Download a sample, fill it, then upload.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold">Groups</div>
                        <div class="text-xs text-gray-600">Create/update groups by group_code.</div>
                    </div>
                    <a href="<?php echo e(route('imports.samples', ['type' => 'groups'])); ?>" class="text-sm text-indigo-600 hover:underline">Download sample</a>
                </div>
                <form method="POST" action="<?php echo e(route('imports.run', ['type' => 'groups'])); ?>" enctype="multipart/form-data" class="mt-3 flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="file" accept=".csv" class="w-full rounded-lg border-gray-200">
                    <button class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">Import</button>
                </form>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold">Items</div>
                        <div class="text-xs text-gray-600">Create/update items by item_code.</div>
                    </div>
                    <a href="<?php echo e(route('imports.samples', ['type' => 'items'])); ?>" class="text-sm text-indigo-600 hover:underline">Download sample</a>
                </div>
                <form method="POST" action="<?php echo e(route('imports.run', ['type' => 'items'])); ?>" enctype="multipart/form-data" class="mt-3 flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="file" accept=".csv" class="w-full rounded-lg border-gray-200">
                    <button class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">Import</button>
                </form>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold">Purchases</div>
                        <div class="text-xs text-gray-600">Creates purchases and purchase lines. Price can be blank for pending price.</div>
                    </div>
                    <a href="<?php echo e(route('imports.samples', ['type' => 'purchases'])); ?>" class="text-sm text-indigo-600 hover:underline">Download sample</a>
                </div>
                <form method="POST" action="<?php echo e(route('imports.run', ['type' => 'purchases'])); ?>" enctype="multipart/form-data" class="mt-3 flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="file" accept=".csv" class="w-full rounded-lg border-gray-200">
                    <button class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">Import</button>
                </form>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold">Issues</div>
                        <div class="text-xs text-gray-600">Creates issues and allocates FIFO automatically.</div>
                    </div>
                    <a href="<?php echo e(route('imports.samples', ['type' => 'issues'])); ?>" class="text-sm text-indigo-600 hover:underline">Download sample</a>
                </div>
                <form method="POST" action="<?php echo e(route('imports.run', ['type' => 'issues'])); ?>" enctype="multipart/form-data" class="mt-3 flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="file" accept=".csv" class="w-full rounded-lg border-gray-200">
                    <button class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">Import</button>
                </form>
            </div>
        </div>

        <div class="text-xs text-gray-500 mt-4">Note: CSV import is strict. If an item_code/group_code is unknown, import will stop with an error.</div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/settings/index.blade.php ENDPATH**/ ?>