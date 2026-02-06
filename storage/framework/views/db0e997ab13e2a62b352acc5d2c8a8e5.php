<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Purchase Returns (Outward)</h1>
            <p class="mt-1 text-sm text-gray-600">History of items returned to supplier based on original purchases.</p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('returns.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Returns Home</a>
            <a href="<?php echo e(route('returns.purchase.create')); ?>" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">New Return</a>
        </div>
    </div>

    <?php if(session('status')): ?>
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <div class="rounded-xl border border-gray-200 bg-white p-4 mb-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">From</label>
                <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">To</label>
                <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Purchase</label>
                <select name="purchase_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    <?php $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" <?php if(request('purchase_id')==$p->id): echo 'selected'; endif; ?>><?php echo e($p->purchase_date); ?> | <?php echo e($p->reference_no ?? 'No Ref'); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Group</label>
                <select name="group_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($g->id); ?>" <?php if(request('group_id')==$g->id): echo 'selected'; endif; ?>><?php echo e($g->group_code); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Item</label>
                <select name="item_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($it->id); ?>" <?php if(request('item_id')==$it->id): echo 'selected'; endif; ?>><?php echo e($it->item_code); ?> - <?php echo e($it->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Filter</button>
                <a href="<?php echo e(route('returns.purchase.index')); ?>" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold">Return Date</th>
                    <th class="px-3 py-2 text-left font-semibold">Purchase</th>
                    <th class="px-3 py-2 text-left font-semibold">Group</th>
                    <th class="px-3 py-2 text-left font-semibold">Item</th>
                    <th class="px-3 py-2 text-right font-semibold">Qty</th>
                    <th class="px-3 py-2 text-right font-semibold">Price</th>
                    <th class="px-3 py-2 text-right font-semibold">Total</th>
                    <th class="px-3 py-2 text-left font-semibold">By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="px-3 py-2"><?php echo e($r->return_date); ?></td>
                        <td class="px-3 py-2"><?php echo e($r->purchase_date); ?> <div class="text-xs text-gray-500"><?php echo e($r->reference_no); ?></div></td>
                        <td class="px-3 py-2"><?php echo e($r->group_code); ?></td>
                        <td class="px-3 py-2">
                            <a class="font-semibold hover:underline" href="<?php echo e(route('items.stock.show', $r->item_id)); ?>"><?php echo e($r->item_code); ?></a>
                            <div class="text-xs text-gray-600"><?php echo e($r->item_name); ?></div>
                        </td>
                        <td class="px-3 py-2 text-right"><?php echo e(number_format((float)$r->quantity, 3)); ?></td>
                        <td class="px-3 py-2 text-right"><?php echo e(number_format((float)$r->purchase_price, 2)); ?></td>
                        <td class="px-3 py-2 text-right font-semibold"><?php echo e(number_format((float)$r->line_total, 2)); ?></td>
                        <td class="px-3 py-2"><?php echo e($r->created_by_name ?? '-'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4"><?php echo e($rows->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\wms\resources\views/returns/purchase/index.blade.php ENDPATH**/ ?>