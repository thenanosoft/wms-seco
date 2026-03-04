<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Issues (Outward)</h1>
            <p class="mt-1 text-sm text-gray-600">Issue items from store with stock validation.</p>
        </div>

        <a href="<?php echo e(route('issues.create')); ?>"
           class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
            New Issue
        </a>
    </div>

    <form method="GET" action="<?php echo e(route('issues.index')); ?>" class="mb-4 rounded-xl border border-gray-200 bg-white p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-600">From</label>
                <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-600">To</label>
                <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-600">Issued To</label>
                <input type="text" name="issued_to" value="<?php echo e(request('issued_to')); ?>" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Name">
            </div>
            <div>
                <label class="block text-xs text-gray-600">Reference</label>
                <input type="text" name="reference" value="<?php echo e(request('reference')); ?>" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Ref">
            </div>
            <div class="flex gap-2">
                <button class="mt-5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Filter</button>
                <a href="<?php echo e(route('issues.index')); ?>" class="mt-5 rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    <div class="flex flex-wrap gap-2 mb-4">
    <a href="<?php echo e(route('print.issues', request()->query())); ?>" target="_blank"
       class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

    <a href="<?php echo e(route('export.issues.csv', request()->query())); ?>"
       class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

    <a href="<?php echo e(route('export.issues.pdf', request()->query())); ?>"
       class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
</div>


    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Issued To</th>
                        <th class="px-4 py-3 text-left font-semibold">Ref</th>
                        <th class="px-4 py-3 text-left font-semibold">Created By</th>
                        <th class="px-4 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-left"><a href="<?php echo e(route('issues.show', $it)); ?>" class="text-indigo-600 hover:underline">#<?php echo e($it->id); ?></a></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                    <?php echo e($it->issue_date->format('Y-m-d')); ?>

                            </td>
                            <td class="px-4 py-3"><?php echo e($it->issued_to ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($it->reference_no ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($it->creator?->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="<?php echo e(route('issues.edit', $it)); ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                                        </svg>
                                    </a>
                                    <form action="<?php echo e(route('issues.destroy', $it)); ?>" method="POST" onsubmit="return confirm('Delete this issue? If it has returns, related return records will also be deleted.')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-700 hover:bg-red-50" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4h8v2" />
                                                <path d="M6 6l1 16h10l1-16" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-4 py-3"><?php echo e($it->creator?->name ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-600" colspan="6">
                                No issues yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <?php echo e($issues->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/Data/Development/web/laravel/wms/resources/views/issue/index.blade.php ENDPATH**/ ?>