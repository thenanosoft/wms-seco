<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">New Item</h1>

    <?php if($errors->any()): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('items.store')); ?>" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6 space-y-4">
        <?php echo csrf_field(); ?>

        <div>
            <label class="text-sm font-medium">Group</label>
            <select name="group_id" class="mt-1 w-full rounded-lg border-gray-200" required>
                <option value="">Select group</option>
                <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($g->id); ?>" <?php if(old('group_id') == $g->id): echo 'selected'; endif; ?>>
                        <?php echo e($g->group_code); ?><?php echo e($g->group_name ? ' - '.$g->group_name : ''); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Item Code</label>
                <input name="item_code" value="<?php echo e(old('item_code')); ?>" class="mt-1 w-full rounded-lg border-gray-200" required>
            </div>

            <div>
                <label class="text-sm font-medium">Item Name</label>
                <input name="name" value="<?php echo e(old('name')); ?>" class="mt-1 w-full rounded-lg border-gray-200" required>
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Default Specification</label>
            <textarea name="default_spec" class="mt-1 w-full rounded-lg border-gray-200" rows="3" placeholder="Optional"><?php echo e(old('default_spec')); ?></textarea>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="<?php echo e(route('items.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancel</a>
            <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">Save</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/items/create.blade.php ENDPATH**/ ?>