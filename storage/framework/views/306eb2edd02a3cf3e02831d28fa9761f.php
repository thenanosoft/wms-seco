<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Settings</h1>
        <p class="text-sm text-gray-600">Low stock alerts and backup schedule.</p>
    </div>

    <?php if(session('status')): ?>
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('settings.update')); ?>" class="space-y-6">
        <?php echo csrf_field(); ?>

        <div class="rounded-xl border bg-white p-4 sm:p-6">
            <h2 class="text-lg font-semibold mb-4">Low Stock</h2>

            <label class="text-sm font-medium">Default Low Stock Threshold</label>
            <input type="number" step="0.001" min="0"
                   name="default_low_stock_threshold"
                   value="<?php echo e(old('default_low_stock_threshold', $defaultLow)); ?>"
                   class="mt-1 w-64 rounded-lg border-gray-200">
            <div class="text-xs text-gray-600 mt-1">Used when item specific threshold is empty.</div>
        </div>

        <div class="rounded-xl border bg-white p-4 sm:p-6">
            <h2 class="text-lg font-semibold mb-4">Backup Schedule</h2>

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="backup_enabled" value="1"
                       <?php echo e(old('backup_enabled', optional($backup)->enabled) ? 'checked' : ''); ?>>
                <span class="text-sm font-medium">Enable Auto Backup</span>
            </label>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div>
                    <label class="text-sm font-medium">Frequency</label>
                    <select name="backup_frequency" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="daily" <?php echo e(old('backup_frequency', optional($backup)->frequency) === 'daily' ? 'selected' : ''); ?>>Daily</option>
                        <option value="weekly" <?php echo e(old('backup_frequency', optional($backup)->frequency) === 'weekly' ? 'selected' : ''); ?>>Weekly</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Weekly Day (1=Mon, 7=Sun)</label>
                    <input type="number" min="1" max="7"
                           name="backup_weekly_day"
                           value="<?php echo e(old('backup_weekly_day', optional($backup)->weekly_day ?? 1)); ?>"
                           class="mt-1 w-full rounded-lg border-gray-200">
                </div>

                <div>
                    <label class="text-sm font-medium">Time (HH:MM)</label>
                    <input type="text"
                           name="backup_time_hm"
                           value="<?php echo e(old('backup_time_hm', optional($backup)->time_hm ?? '02:00')); ?>"
                           class="mt-1 w-full rounded-lg border-gray-200"
                           placeholder="02:00">
                </div>

                <div>
                    <label class="text-sm font-medium">Backup Folder Path</label>
                    <input type="text"
                           name="backup_path"
                           value="<?php echo e(old('backup_path', optional($backup)->backup_path)); ?>"
                           class="mt-1 w-full rounded-lg border-gray-200"
                           placeholder="/path/to/backups">
                </div>

            </div>

            <div class="text-xs text-gray-600 mt-3">
                Tip: On Mac you can use: <?php echo e(storage_path('app/backups')); ?>

            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                Save Settings
            </button>
        </div>
    </form>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/settings/index.blade.php ENDPATH**/ ?>