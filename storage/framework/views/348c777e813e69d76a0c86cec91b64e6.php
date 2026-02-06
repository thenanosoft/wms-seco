<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Purchase Items</h1>
        <p class="text-sm text-gray-600">Line-wise purchase history and stock base.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="<?php echo e(route('print.purchases', request()->query())); ?>" target="_blank"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

        <a href="<?php echo e(route('export.purchases.csv', request()->query())); ?>"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

        <a href="<?php echo e(route('export.purchases.pdf', request()->query())); ?>"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
    </div>


    <!-- Filters -->
    <form method="GET" class="rounded-xl border bg-white p-4 grid grid-cols-1 md:grid-cols-5 gap-4">
        <select name="group_id" class="rounded-lg border-gray-200">
            <option value="">All Groups</option>
            <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($g->id); ?>" <?php if(request('group_id') == $g->id): echo 'selected'; endif; ?>>
                    <?php echo e($g->group_code); ?> - <?php echo e($g->group_name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="item_id" class="rounded-lg border-gray-200">
            <option value="">All Items</option>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($i->id); ?>" <?php if(request('item_id') == $i->id): echo 'selected'; endif; ?>>
                    <?php echo e($i->item_code); ?> - <?php echo e($i->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="rounded-lg border-gray-200">
        <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="rounded-lg border-gray-200">

        <button class="rounded-lg bg-gray-900 text-white px-4 py-2 text-sm">
            Filter
        </button>
    </form>

    <!-- Table -->
    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Group</th>
                    <th class="px-4 py-3 text-left">Item</th>
                    <th class="px-4 py-3 text-right">Qty In</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2"><?php echo e($r->purchase_date); ?></td>
                        <td class="px-4 py-2"><?php echo e($r->group_code); ?></td>
                        <td class="px-4 py-2">
                            <a href="<?php echo e(route('items.stock.show', $r->item_id)); ?>" class="text-indigo-600 hover:underline">
                                <?php echo e($r->item_code); ?> - <?php echo e($r->item_name); ?>

                            </a>
                        </td>
                        <td class="px-4 py-2 text-right"><?php echo e((int)$r->quantity); ?></td>
                        <td class="px-4 py-2 text-right"><?php echo e(number_format((float)$r->purchase_price, 0)); ?></td>
                        <td class="px-4 py-2 text-right font-semibold">
                            <?php echo e(number_format((float)$r->line_total, 0)); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <?php echo e($rows->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\wms\resources\views/purchase/items-index.blade.php ENDPATH**/ ?>