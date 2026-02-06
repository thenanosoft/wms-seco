<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Backup and Restore</h1>
        <p class="text-sm text-gray-600">Admin only. Manual backup download and restore from SQL file.</p>
    </div>

    <?php if(session('success')): ?>
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <?php echo e(session('success')); ?>

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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-xl border bg-white p-4 sm:p-6 space-y-4">
            <h2 class="text-lg font-semibold">Auto Backup Settings</h2>
            <form method="POST" action="<?php echo e(route('backup.settings.update')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="enabled" value="1" <?php echo e(($settings?->enabled ?? false) ? 'checked' : ''); ?>>
                    Enable auto backup (runs automatically when admin uses the system)
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Frequency</label>
                        <select id="backup_frequency" name="frequency" class="w-full rounded-lg border-gray-200">
                            <?php $__currentLoopData = ['daily'=>'Daily','weekly'=>'Weekly']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($k); ?>" <?php echo e(($settings?->frequency ?? 'daily') === $k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div id="weekly_day_wrap" style="display:none;">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Weekly Day</label>
                        <select name="weekly_day" class="w-full rounded-lg border-gray-200">
                            <?php
                                $days = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'];
                                $sel = (int)($settings?->weekly_day ?? 1);
                            ?>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($k); ?>" <?php echo e($sel === $k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Run Time</label>
                        <input type="time" name="time_hm" value="<?php echo e($settings?->time_hm ?? '18:00'); ?>" class="w-full rounded-lg border-gray-200">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Backup Folder (inside storage/app)</label>
                    <input type="text" name="backup_path" value="<?php echo e($settings?->backup_path ?? 'wms_backups'); ?>" class="w-full rounded-lg border-gray-200" placeholder="wms_backups">
                    <p class="mt-1 text-xs text-gray-600">Example: <span class="font-mono">wms_backups</span> or <span class="font-mono">wms_backups/branch_A</span></p>
                </div>

                <div class="flex items-center gap-3">
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Save Settings</button>
                    <span class="text-xs text-gray-600">Last run: <?php echo e($settings?->last_run_at ? $settings->last_run_at->format('Y-m-d H:i') : 'Never'); ?></span>
                </div>
            </form>
        </div>

        <div class="rounded-xl border bg-white p-4 sm:p-6 space-y-4">
            <h2 class="text-lg font-semibold">Backups</h2>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="<?php echo e(route('backup.manual')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Create Backup Now</button>
                </form>
                <?php if(!empty($autoBackups)): ?>
                    <a href="<?php echo e(route('backup.download.latest')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Download Latest</a>
                <?php endif; ?>
            </div>

            <div class="text-xs text-gray-600">Only the last <?php echo e(\App\Services\BackupService::RETENTION_COUNT); ?> auto backups are kept (older files are deleted automatically).</div>

            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left">File</th>
                            <th class="px-3 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php $__empty_1 = true; $__currentLoopData = $autoBackups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs">
    <?php echo e($b['name'] ?? ''); ?>

    <div class="text-gray-500"><?php echo e($b['display'] ?? (($b['date'] ?? '') . ' ' . ($b['time'] ?? ''))); ?></div>
</td>

                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="<?php echo e(route('backup.download', ['filename' => $b['name']])); ?>" class="rounded border border-gray-200 px-3 py-1.5 text-xs hover:bg-gray-50">Download</a>
                                        <form method="POST" action="<?php echo e(route('backup.restore')); ?>" onsubmit="return confirm('This will overwrite the current database. Continue?')">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="selected_backup" value="<?php echo e($b['name'] ?? ''); ?>">
                                            <button class="rounded border border-red-200 px-3 py-1.5 text-xs text-red-700 hover:bg-red-50">Restore</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td class="px-3 py-3 text-gray-600" colspan="2">No backup files yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="rounded-lg border bg-gray-50 p-3">
                <div class="text-sm font-semibold">Restore from uploaded SQL</div>
                <p class="text-xs text-gray-600 mb-2">Use this if a backup file is on your computer. Uploaded SQL is not kept on the server after restore.</p>
                <form method="POST" action="<?php echo e(route('backup.restore')); ?>" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="sql_file" required class="block text-sm">
                    <button class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50" onclick="return confirm('This will overwrite the current database. Continue?')">Restore Upload</button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
(function(){
  const freq = document.getElementById('backup_frequency');
  const wrap = document.getElementById('weekly_day_wrap');
  function sync(){
    if(!freq || !wrap) return;
    wrap.style.display = (freq.value === 'weekly') ? '' : 'none';
  }
  if(freq){
    freq.addEventListener('change', sync);
    sync();
  }
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\wms\resources\views/backup/index.blade.php ENDPATH**/ ?>