<?php $__env->startSection('content'); ?>
<?php
    $balance = (float)($summary['balance'] ?? ($summary['available'] ?? 0));
    $totalIn = (float)($summary['total_in'] ?? 0);
    $totalOut = (float)($summary['total_out'] ?? 0);

    $lastPurchase = (float)($summary['last_purchase_price'] ?? 0);
    $avgPurchase  = (float)($summary['avg_purchase_price'] ?? 0);

    $valueLast = round($balance * $lastPurchase, 2);
    $valueAvg  = round($balance * $avgPurchase, 2);

    $isLow = (bool)($summary['is_low'] ?? false);
    $thresholdUsed = (float)($summary['threshold_used'] ?? 0);

    $commonFilters = array_filter([
        'from' => $from ?? null,
        'to' => $to ?? null,
        'item_id' => $item->id,
    ]);
?>

<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="text-sm text-gray-600">
                <a href="<?php echo e(route('stock.index')); ?>" class="hover:underline">Stock</a>
                <span class="mx-2">/</span>
                <span>Item Detail</span>
            </div>

            <h1 class="text-2xl font-semibold mt-1">
                <?php echo e($item->item_code); ?> - <?php echo e($item->name); ?>

            </h1>

            <div class="text-sm text-gray-600 mt-1">
                Group:
                <span class="font-medium text-gray-900">
                    <?php echo e($item->group->group_code); ?><?php echo e($item->group->group_name ? ' - '.$item->group->group_name : ''); ?>

                </span>
            </div>
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

            <?php if(Route::has('issue-returns.create')): ?>
                <a href="<?php echo e(route('issue-returns.create')); ?>"
                   class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Issue Return
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Balance Stock</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e(number_format($balance, 3)); ?></div>
            <div class="text-xs text-gray-600 mt-1">In: <?php echo e(number_format($totalIn, 3)); ?> | Out: <?php echo e(number_format($totalOut, 3)); ?></div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Last Purchase Price</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e(number_format($lastPurchase, 2)); ?></div>
            <div class="text-xs text-gray-600 mt-1">Value (Last): <?php echo e(number_format($valueLast, 2)); ?></div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Average Purchase Price</div>
            <div class="mt-2 text-2xl font-semibold"><?php echo e(number_format($avgPurchase, 2)); ?></div>
            <div class="text-xs text-gray-600 mt-1">Value (Avg): <?php echo e(number_format($valueAvg, 2)); ?></div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Status</div>
            <div class="mt-2">
                <?php if($isLow): ?>
                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">Low Stock</span>
                <?php else: ?>
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">OK</span>
                <?php endif; ?>
            </div>
            <div class="text-xs text-gray-600 mt-2">
                Min Threshold: <span class="font-semibold"><?php echo e(number_format($thresholdUsed, 3)); ?></span>
            </div>
        </div>
    </div>

    <div class="rounded-xl border bg-white p-4">
        <form method="GET" action="<?php echo e(route('items.stock.show', $item->id)); ?>" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-4">
            <div>
                <label class="text-sm font-medium">From</label>
                <input type="date" name="from" value="<?php echo e($from); ?>" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-sm font-medium">To</label>
                <input type="date" name="to" value="<?php echo e($to); ?>" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Apply</button>
                <a href="<?php echo e(route('items.stock.show', $item->id)); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border bg-white p-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-sm font-semibold">Quick Export</div>
                <div class="text-xs text-gray-600">Exports will respect selected date filters (where supported).</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('print.purchases', $commonFilters)); ?>" target="_blank">Print Purchases</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('export.purchases.csv', $commonFilters)); ?>">CSV Purchases</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('export.purchases.pdf', $commonFilters)); ?>">PDF Purchases</a>

                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('print.issues', $commonFilters)); ?>" target="_blank">Print Issues</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('export.issues.csv', $commonFilters)); ?>">CSV Issues</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('export.issues.pdf', $commonFilters)); ?>">PDF Issues</a>

                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('print.returns', $commonFilters)); ?>" target="_blank">Print Returns</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('export.returns.csv', $commonFilters)); ?>">CSV Returns</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('export.returns.pdf', $commonFilters)); ?>">PDF Returns</a>

                <?php if(Route::has('export.issue-returns.csv')): ?>
                    <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('export.issue-returns.csv', $commonFilters)); ?>">CSV Issue Returns</a>
                    <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="<?php echo e(route('export.issue-returns.pdf', $commonFilters)); ?>">PDF Issue Returns</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php echo $__env->make('items.partials._history_table', [
        'title' => 'Purchase Price History',
        'subtitle' => 'Date-wise purchases for this item.',
        'rows' => $purchaseHistory,
        'qtyField' => 'qty_in',
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('items.partials._history_table', [
        'title' => 'Issue / Sale History',
        'subtitle' => 'Date-wise issues for this item.',
        'rows' => $issueHistory,
        'qtyField' => 'qty_out',
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('items.partials._history_table', [
        'title' => 'Issue Return History (Inward)',
        'subtitle' => 'Returns against issues (audit-safe).',
        'rows' => $issueReturnHistory,
        'qtyField' => 'qty_in',
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/items/stock-show.blade.php ENDPATH**/ ?>