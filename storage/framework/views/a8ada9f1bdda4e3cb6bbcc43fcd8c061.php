<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">New Issue Return (Inward)</h1>
        <p class="mt-1 text-sm text-gray-600">Select an Issue, then return only remaining quantities. Prices are auto from the original Issue.</p>
    </div>

    <?php if($errors->any()): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold">Please fix the errors:</div>
            <ul class="mt-2 list-disc pl-5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="GET" action="<?php echo e(route('returns.issue.create')); ?>" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
            <div>
                <label class="text-sm font-medium">Select Issue</label>
                <input type="text" id="issueSearch" placeholder="Search by Issue ID, user, reference..." class="mt-1 w-full rounded-lg border-gray-200" autocomplete="off">
                <select name="issue_id" class="mt-1 w-full rounded-lg border-gray-200" required>
                    <option value="">Select</option>
                    <?php $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $iss): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($iss->id); ?>" <?php if(request('issue_id') == $iss->id): echo 'selected'; endif; ?>
                            data-search="<?php echo e(strtolower(''.$iss->id.' '.$iss->issue_date.' '.($iss->reference_no ?? '').' '.($iss->issued_to ?? ''))); ?>">
                            #<?php echo e($iss->id); ?> | <?php echo e($iss->issue_date); ?> | <?php echo e($iss->reference_no ?? 'No Ref'); ?> | <?php echo e($iss->issued_to ?? 'N/A'); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Load Items</button>
            </div>
        </div>
    </form>

    <?php if($selectedIssue): ?>
        <form method="POST" action="<?php echo e(route('returns.issue.store')); ?>" class="mt-4 space-y-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="issue_id" value="<?php echo e($selectedIssue->id); ?>">

            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="text-sm font-medium">Return Date</label>
                        <input type="date" name="return_date" class="mt-1 w-full rounded-lg border-gray-200" value="<?php echo e(old('return_date', now()->format('Y-m-d'))); ?>" required>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="text-sm font-medium">Notes</label>
                        <input type="text" name="notes" class="mt-1 w-full rounded-lg border-gray-200" value="<?php echo e(old('notes')); ?>" placeholder="Optional">
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold">Return Lines</div>
                        <div class="text-xs text-gray-600">Only remaining qty is allowed.</div>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Group</th>
                                <th class="px-3 py-2 text-left font-semibold">Item</th>
                                <th class="px-3 py-2 text-left font-semibold">Spec</th>
                                <th class="px-3 py-2 text-right font-semibold">Issued</th>
                                <th class="px-3 py-2 text-right font-semibold">Returned</th>
                                <th class="px-3 py-2 text-right font-semibold">Remaining</th>
                                <th class="px-3 py-2 text-right font-semibold">Return Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-3 py-2"><?php echo e($l['group_code']); ?></td>
                                    <td class="px-3 py-2"><?php echo e($l['item_code']); ?> - <?php echo e($l['item_name']); ?></td>
                                    <td class="px-3 py-2"><?php echo e($l['specification']); ?></td>
                                    <td class="px-3 py-2 text-right"><?php echo e(number_format($l['issued_qty'], 0)); ?></td>
                                    <td class="px-3 py-2 text-right"><?php echo e(number_format($l['returned_qty'], 0)); ?></td>
                                    <td class="px-3 py-2 text-right font-semibold"><?php echo e(number_format($l['remaining_qty'], 0)); ?></td>
                                    <td class="px-3 py-2 text-right">
                                        <input type="hidden" name="lines[<?php echo e($idx); ?>][issue_line_id]" value="<?php echo e($l['line_id']); ?>">
                                        <input type="number" step="1" min="0" max="<?php echo e($l['remaining_qty']); ?>"
                                               name="lines[<?php echo e($idx); ?>][quantity]"
                                               value="<?php echo e(old("lines.$idx.quantity", 0)); ?>"
                                               class="w-28 rounded-lg border-gray-200 text-right">
                                        <div class="mt-1 text-xs text-gray-500">Max <?php echo e(number_format($l['remaining_qty'], 0)); ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="<?php echo e(route('returns.issue.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancel</a>
                <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">Save Return</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    (function () {
        const input = document.getElementById('issueSearch');
        const select = document.querySelector('select[name="issue_id"]');
        if (!input || !select) return;

        const options = Array.from(select.options);
        input.addEventListener('input', function () {
            const q = (this.value || '').trim().toLowerCase();
            options.forEach(opt => {
                if (opt.value === '') return;
                const hay = (opt.getAttribute('data-search') || opt.textContent || '').toLowerCase();
                opt.hidden = q && !hay.includes(q);
            });
        });
    })();
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(() => {
    const input = document.getElementById('issueSearch');
    const select = document.querySelector('select[name="issue_id"]');
    if (!input || !select) return;

    const options = Array.from(select.options);
    input.addEventListener('input', () => {
        const q = (input.value || '').trim().toLowerCase();
        options.forEach(opt => {
            if (!opt.value) return;
            const hay = opt.getAttribute('data-search') || opt.textContent.toLowerCase();
            opt.hidden = q && !hay.includes(q);
        });
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/returns/issue/create.blade.php ENDPATH**/ ?>