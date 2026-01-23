<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Groups</h1>
            <p class="mt-1 text-sm text-gray-600">Manage group codes (example: 51 Steel).</p>
        </div>
        <a href="<?php echo e(route('groups.create')); ?>"
           class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
            New Group
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input name="q" value="<?php echo e($q); ?>" class="w-full rounded-lg border-gray-200"
                   placeholder="Search by group code or name">
            <button class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                Search
            </button>
            <a href="<?php echo e(route('groups.index')); ?>" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                Reset
            </a>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Group Code</th>
                        <th class="px-4 py-3 text-left font-semibold">Name</th>
                        <th class="px-4 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold"><?php echo e($g->group_code); ?></td>
                            <td class="px-4 py-3"><?php echo e($g->group_name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="<?php echo e(route('groups.edit', $g)); ?>"
                                   class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                                    Edit
                                </a>

                                <form class="inline" method="POST" action="<?php echo e(route('groups.destroy', $g)); ?>"
                                      onsubmit="return confirm('Delete this group? This will also delete its items.');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-600">No groups found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <?php echo e($groups->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/groups/index.blade.php ENDPATH**/ ?>