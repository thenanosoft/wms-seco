<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <h1 class="text-2xl font-semibold">Profile & Users</h1>

    <?php if(session('success')): ?>
        <div class="rounded border bg-green-50 px-4 py-2 text-sm text-green-800">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    
    <div class="rounded-xl border bg-white p-5">
        <h2 class="font-semibold mb-3">My Profile</h2>

        <form method="POST" action="<?php echo e(route('profile.update')); ?>" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php echo csrf_field(); ?>

            <div>
                <label class="text-xs text-gray-600">Name</label>
                <input name="name" value="<?php echo e(auth()->user()->name); ?>" class="w-full rounded border-gray-200">
            </div>

            <div>
                <label class="text-xs text-gray-600">Email</label>
                <input name="email" value="<?php echo e(auth()->user()->email); ?>" class="w-full rounded border-gray-200">
            </div>

            <div>
                <label class="text-xs text-gray-600">New Password</label>
                <input type="password" name="password" class="w-full rounded border-gray-200">
            </div>

            <div class="flex items-end">
                <button class="rounded bg-gray-900 px-4 py-2 text-white text-sm">Update</button>
            </div>
        </form>
    </div>

    
    <div class="rounded-xl border bg-white p-5">
        <h2 class="font-semibold mb-3">
            Users (<?php echo e(count($users)); ?>/<?php echo e($maxUsers); ?>)
        </h2>

        <table class="w-full text-sm mb-4">
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr class="border-t">
    <td class="py-2"><?php echo e($u->name); ?></td>
    <td class="py-2"><?php echo e($u->username); ?></td>
    <td class="py-2"><?php echo e($u->email); ?></td>

    <td class="py-2">
        <div class="flex flex-wrap gap-2">

            
            <button type="button"
                class="rounded border border-gray-200 px-3 py-1.5 text-xs hover:bg-gray-50"
                onclick="document.getElementById('edit-user-<?php echo e($u->id); ?>').classList.toggle('hidden')">
                Edit
            </button>

            
            <?php if($u->id !== auth()->id()): ?>
            <form method="POST" action="<?php echo e(route('profile.users.delete', $u)); ?>"
                onsubmit="return confirm('Delete this user?')">
                <?php echo csrf_field(); ?>
                <button class="rounded border border-red-200 px-3 py-1.5 text-xs text-red-700 hover:bg-red-50">
                    Delete
                </button>
            </form>
            <?php endif; ?>
        </div>

        
        <div id="edit-user-<?php echo e($u->id); ?>" class="hidden mt-3 rounded-lg border bg-gray-50 p-3">
            <form method="POST" action="<?php echo e(route('profile.users.update', $u)); ?>" class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                <?php echo csrf_field(); ?>

                <input name="name" value="<?php echo e($u->name); ?>" class="rounded border-gray-200 text-sm" placeholder="Name" required>

                <input name="username" value="<?php echo e($u->username); ?>" class="rounded border-gray-200 text-sm" placeholder="Username" required>

                <input name="email" value="<?php echo e($u->email); ?>" class="rounded border-gray-200 text-sm" placeholder="Email" required>

                <input name="password" class="rounded border-gray-200 text-sm" placeholder="New Password (optional)">

                <div class="sm:col-span-4 flex gap-2">
                    <button class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">
                        Save
                    </button>
                    <button type="button"
                        class="rounded border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50"
                        onclick="document.getElementById('edit-user-<?php echo e($u->id); ?>').classList.add('hidden')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

    </td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


        </table>

        <?php if(count($users) < $maxUsers): ?>
            <form method="POST" action="<?php echo e(route('profile.users.store')); ?>"
      class="grid grid-cols-1 sm:grid-cols-4 gap-2">
    <?php echo csrf_field(); ?>
    <input name="name" placeholder="Name" class="rounded border-gray-200">
    <input name="username" placeholder="Username (optional)" class="rounded border-gray-200">
    <input name="email" placeholder="Email" class="rounded border-gray-200">
    <input name="password" placeholder="Password" class="rounded border-gray-200">
    <button class="rounded bg-gray-900 px-3 py-2 text-white text-sm">
        Add User
    </button>
</form>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\wms\resources\views/profile/index.blade.php ENDPATH**/ ?>