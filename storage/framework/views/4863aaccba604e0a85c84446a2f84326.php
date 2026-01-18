<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Issue Returns</h1>
            <p class="text-sm text-gray-600">Return inward strictly against issued lines (no stock tampering).</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('issue-returns.create')); ?>"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                New Issue Return
            </a>
        </div>
    </div>

    <form method="GET" class="rounded-xl border bg-white p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="text-xs font-semibold text-gray-600">From</label>
                <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">To</label>
                <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div class="lg:col-span-2">
                <label class="text-xs font-semibold text-gray-600">Issue</label>
                <select name="issue_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    <?php $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($i->id); ?>" <?php if(request('issue_id') == $i->id): echo 'selected'; endif; ?>>
                            #<?php echo e($i->id); ?> | <?php echo e($i->issue_date->format('Y-m-d')); ?> | <?php echo e($i->issued_to ?? 'N/A'); ?> <?php echo e($i->reference_no ? ' | Ref: '.$i->reference_no : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-end gap-2">
            <a href="<?php echo e(route('issue-returns.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Reset</a>
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Apply</button>
        </div>
    </form>

    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Issue</th>
                    <th class="px-4 py-3 text-left">Received From</th>
                    <th class="px-4 py-3 text-left">Ref</th>
                    <th class="px-4 py-3 text-left">Created By</th>
                    <th class="px-4 py-3 text-right">Lines</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 whitespace-nowrap"><?php echo e($r->return_date->format('Y-m-d')); ?></td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            #<?php echo e($r->issue_id); ?>

                        </td>
                        <td class="px-4 py-2"><?php echo e($r->received_from ?? '-'); ?></td>
                        <td class="px-4 py-2"><?php echo e($r->reference_no ?? '-'); ?></td>
                        <td class="px-4 py-2"><?php echo e($r->creator?->name ?? '-'); ?></td>
                        <td class="px-4 py-2 text-right">
                            <?php echo e($r->lines()->count()); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($rows->count() === 0): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-600">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div>
        <?php echo e($rows->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/issue-returns/index.blade.php ENDPATH**/ ?>