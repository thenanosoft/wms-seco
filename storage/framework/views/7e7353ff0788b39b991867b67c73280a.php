<?php
    $u = auth()->user();
    $username = $u?->username ?: ($u?->name ?: '');
    $role = (string)($u?->role ?? '');
    $roleLabel = $role === 'admin' ? 'Admin' : ($role === 'store_helper' ? 'Store Helper' : ucfirst(str_replace('_',' ', $role)));
    $printedAt = now()->format('d M Y, h:i A');
?>

<div class="report-header">
    <div class="company"><?php echo e($storeName ?? config('app.name')); ?></div>
    <div class="meta"><?php echo e($printedAt); ?></div>
    <div class="meta">Printed By: <?php echo e($roleLabel); ?><?php echo e($username ? ' - ' . $username : ''); ?></div>
    <div class="title"><?php echo e($title ?? ''); ?></div>
</div>
<?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/partials/report_header.blade.php ENDPATH**/ ?>