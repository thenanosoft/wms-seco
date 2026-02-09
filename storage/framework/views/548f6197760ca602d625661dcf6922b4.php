<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Report</title>
    <?php echo $__env->make('partials.report_theme_pdf', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>
    <?php echo $__env->make('partials.report_header', ['title' => 'Balance Report'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div style="margin-top: -6px; font-size: 11px; text-align:center;">
        From: <?php echo e($from ?: '-'); ?> | To: <?php echo e($to ?: '-'); ?>

    </div>
    <div class="report-divider"></div>

    <table>
        <thead>
            <tr>
                <th class="w-10">Group</th>
                <th class="w-25">Item</th>
                <th class="w-10 right">P Qty</th>
                <th class="w-10 right">P Amt</th>
                <th class="w-10 right">I Qty</th>
                <th class="w-10 right">I Amt</th>
                <th class="w-10 right">IR Qty</th>
                <th class="w-10 right">IR Amt</th>
                <th class="w-10 right">PR Qty</th>
                <th class="w-10 right">PR Amt</th>
                <th class="w-10 right">Net Qty</th>
                <th class="w-10 right">Net Amt</th>
            </tr>
        </thead>
        <tbody>
            <?php ($tP=0); ?>
            <?php ($tPA=0); ?>
            <?php ($tI=0); ?>
            <?php ($tIA=0); ?>
            <?php ($tIR=0); ?>
            <?php ($tIRA=0); ?>
            <?php ($tPR=0); ?>
            <?php ($tPRA=0); ?>
            <?php ($tN=0); ?>
            <?php ($tNA=0); ?>
            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($tP += (int)$r->purchased_qty); ?>
                <?php ($tPA += (float)($r->purchased_amount ?? 0)); ?>
                <?php ($tI += (int)$r->issued_qty); ?>
                <?php ($tIA += (float)($r->issued_amount ?? 0)); ?>
                <?php ($tIR += (int)$r->issue_return_qty); ?>
                <?php ($tIRA += (float)($r->issue_return_amount ?? 0)); ?>
                <?php ($tPR += (int)$r->purchase_return_qty); ?>
                <?php ($tPRA += (float)($r->purchase_return_amount ?? 0)); ?>
                <?php ($tN += (int)$r->net_balance); ?>
                <?php ($tNA += (float)($r->net_amount ?? 0)); ?>
                <tr>
                    <td><?php echo e($r->group_code ?? ''); ?></td>
                    <td><?php echo e($r->item_code); ?> - <?php echo e($r->item_name); ?></td>
                    <td class="right"><?php echo e(number_format((int)$r->purchased_qty,0)); ?></td>
                    <td class="right"><?php echo e(number_format((float)($r->purchased_amount ?? 0),0)); ?></td>
                    <td class="right"><?php echo e(number_format((int)$r->issued_qty,0)); ?></td>
                    <td class="right"><?php echo e(number_format((float)($r->issued_amount ?? 0),0)); ?></td>
                    <td class="right"><?php echo e(number_format((int)$r->issue_return_qty,0)); ?></td>
                    <td class="right"><?php echo e(number_format((float)($r->issue_return_amount ?? 0),0)); ?></td>
                    <td class="right"><?php echo e(number_format((int)$r->purchase_return_qty,0)); ?></td>
                    <td class="right"><?php echo e(number_format((float)($r->purchase_return_amount ?? 0),0)); ?></td>
                    <td class="right"><strong><?php echo e(number_format((int)$r->net_balance,0)); ?></strong></td>
                    <td class="right"><strong><?php echo e(number_format((float)($r->net_amount ?? 0),0)); ?></strong></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="total-row">
                <td colspan="2" class="right">Total</td>
                <td class="right"><?php echo e(number_format($tP,0)); ?></td>
                <td class="right"><?php echo e(number_format($tPA,0)); ?></td>
                <td class="right"><?php echo e(number_format($tI,0)); ?></td>
                <td class="right"><?php echo e(number_format($tIA,0)); ?></td>
                <td class="right"><?php echo e(number_format($tIR,0)); ?></td>
                <td class="right"><?php echo e(number_format($tIRA,0)); ?></td>
                <td class="right"><?php echo e(number_format($tPR,0)); ?></td>
                <td class="right"><?php echo e(number_format($tPRA,0)); ?></td>
                <td class="right"><?php echo e(number_format($tN,0)); ?></td>
                <td class="right"><?php echo e(number_format($tNA,0)); ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/pdf/balance_report.blade.php ENDPATH**/ ?>