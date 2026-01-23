<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Items</h1>
            <p class="mt-1 text-sm text-gray-600">Items are linked to groups. Example: Steel group has many items.</p>
        </div>
        <a href="<?php echo e(route('items.create')); ?>"
           class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
            New Item
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 mb-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-1">
                <select name="group_id" class="w-full rounded-lg border-gray-200">
                    <option value="">All Groups</option>
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($g->id); ?>" <?php if((string)$groupId === (string)$g->id): echo 'selected'; endif; ?>>
                            <?php echo e($g->group_code); ?><?php echo e($g->group_name ? ' - '.$g->group_name : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="sm:col-span-2">
                <input name="q" value="<?php echo e($q); ?>" class="w-full rounded-lg border-gray-200"
                       placeholder="Search by item code or name">
            </div>
            <div class="sm:col-span-1 flex gap-3">
                <button class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Search
                </button>
                <a href="<?php echo e(route('items.index')); ?>" class="w-full text-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Group</th>
                        <th class="px-4 py-3 text-left font-semibold">Item Code</th>
                        <th class="px-4 py-3 text-left font-semibold">Item Name</th>
                        <th class="px-4 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-semibold"><?php echo e($it->group->group_code); ?></div>
                                <div class="text-xs text-gray-600"><?php echo e($it->group->group_name ?? ''); ?></div>
                            </td>
                            <td class="px-4 py-3 font-semibold"><?php echo e($it->item_code); ?></td>
                            <td class="px-4 py-3">
                                <div class="font-medium"><?php echo e($it->name); ?></div>
                                <?php if($it->default_spec): ?>
                                    <div class="text-xs text-gray-600"><?php echo e($it->default_spec); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="<?php echo e(route('items.edit', $it)); ?>"
                                   class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                                    Edit
                                </a>

                                <form class="inline" method="POST" action="<?php echo e(route('items.destroy', $it)); ?>"
                                      onsubmit="return confirm('Delete this item?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-600">No items found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <?php echo e($items->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/items/index.blade.php ENDPATH**/ ?>