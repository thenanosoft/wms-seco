<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Backup and Restore</h1>
        <p class="text-sm text-gray-600">Admin only. Manual backup download and restore from SQL file.</p>
    </div>

    <?php if(session('status')): ?>
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold">Errors:</div>
            <ul class="list-disc pl-5 mt-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="rounded-xl border bg-white p-4 sm:p-6 space-y-4">
        <h2 class="text-lg font-semibold">Manual Backup</h2>
        <form method="POST" action="<?php echo e(route('backup.manual')); ?>">
            <?php echo csrf_field(); ?>
            <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                Create and Download SQL Backup
            </button>
        </form>
        <div class="text-xs text-gray-600">
            Backup will be created in storage/app/backups and downloaded.
        </div>
    </div>

    <div class="rounded-xl border bg-white p-4 sm:p-6 space-y-4">
        <h2 class="text-lg font-semibold">Restore</h2>
        <div class="text-sm text-red-700 font-semibold">
            Warning: Restore will overwrite current database.
        </div>
        <form method="POST" action="<?php echo e(route('backup.restore')); ?>" enctype="multipart/form-data" class="space-y-3">
            <?php echo csrf_field(); ?>
            <input type="file" name="sql_file" required class="block">
            <button class="rounded-lg border border-gray-200 px-5 py-2.5 text-sm hover:bg-gray-50">
                Restore from SQL File
            </button>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/backup/index.blade.php ENDPATH**/ ?>