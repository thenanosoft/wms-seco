<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto"
     x-data="returnForm(<?php echo e($groups->toJson()); ?>, <?php echo e($items->toJson()); ?>)"
     x-init="init()">

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Return (Inward / Outward)</h1>
        <p class="mt-1 text-sm text-gray-600">
            Use this screen to return items inward or outward. Stock will update automatically.
        </p>
    </div>

    <?php if($errors->any()): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold mb-1">Please fix the errors:</div>
            <ul class="list-disc pl-5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('returns.store')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>

        <!-- Header info -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div>
                    <label class="text-sm font-medium">Return Date</label>
                    <input type="date"
                           name="return_date"
                           class="mt-1 w-full rounded-lg border-gray-200"
                           value="<?php echo e(old('return_date', now()->format('Y-m-d'))); ?>"
                           required>
                </div>

                <div>
                    <label class="text-sm font-medium">Return Type</label>
                    <select name="type"
                            class="mt-1 w-full rounded-lg border-gray-200"
                            required>
                        <option value="IN">Return Inward (Stock Increase)</option>
                        <option value="OUT">Return Outward (Stock Decrease)</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Party / Supplier / Dept</label>
                    <input type="text"
                           name="party"
                           class="mt-1 w-full rounded-lg border-gray-200"
                           placeholder="Optional">
                </div>

                <div>
                    <label class="text-sm font-medium">Reference No</label>
                    <input type="text"
                           name="reference_no"
                           class="mt-1 w-full rounded-lg border-gray-200"
                           placeholder="Optional">
                </div>

            </div>

            <div class="mt-4">
                <label class="text-sm font-medium">Notes</label>
                <textarea name="notes"
                          class="mt-1 w-full rounded-lg border-gray-200"
                          rows="2"
                          placeholder="Optional"></textarea>
            </div>
        </div>

        <!-- Lines -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-sm font-semibold">Items</div>
                    <div class="text-xs text-gray-600">
                        Select group → item → enter quantity
                    </div>
                </div>

                <button type="button"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50"
                        @click="addNewLine()">
                    Add Line
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Group</th>
                            <th class="px-3 py-2 text-left">Item</th>
                            <th class="px-3 py-2 text-left">Specification</th>
                            <th class="px-3 py-2 text-left">Price</th>
                            <th class="px-3 py-2 text-left">Qty</th>
                            <th class="px-3 py-2 text-left">Total</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(line, idx) in lines" :key="line.key">
                            <tr>
                                <!-- Group -->
                                <td class="px-3 py-2">
                                    <select class="w-44 rounded-lg border-gray-200"
                                            :name="`lines[${idx}][group_id]`"
                                            x-model="line.group_id"
                                            @change="onGroupChange(idx)"
                                            required>
                                        <option value="">Select</option>
                                        <template x-for="g in groups" :key="g.id">
                                            <option :value="g.id"
                                                    x-text="`${g.group_code}${g.group_name ? ' - ' + g.group_name : ''}`">
                                            </option>
                                        </template>
                                    </select>
                                </td>

                                <!-- Item -->
                                <td class="px-3 py-2">
                                    <select class="w-64 rounded-lg border-gray-200"
                                            :name="`lines[${idx}][item_id]`"
                                            x-model="line.item_id"
                                            required>
                                        <option value="">Select item</option>
                                        <template x-for="it in itemsForGroup(line.group_id)" :key="it.id">
                                            <option :value="it.id"
                                                    x-text="`${it.item_code} - ${it.name}`">
                                            </option>
                                        </template>
                                    </select>
                                </td>

                                <!-- Spec -->
                                <td class="px-3 py-2">
                                    <input type="text"
                                           class="w-64 rounded-lg border-gray-200"
                                           :name="`lines[${idx}][specification]`"
                                           x-model="line.specification"
                                           placeholder="Optional">
                                </td>

                                <!-- Price -->
                                <td class="px-3 py-2">
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           class="w-28 rounded-lg border-gray-200"
                                           :name="`lines[${idx}][unit_price]`"
                                           x-model.number="line.unit_price"
                                           @input="recalc(idx)">
                                </td>

                                <!-- Qty -->
                                <td class="px-3 py-2">
                                    <input type="number"
                                           step="0.001"
                                           min="0.001"
                                           class="w-24 rounded-lg border-gray-200"
                                           :name="`lines[${idx}][quantity]`"
                                           x-model.number="line.quantity"
                                           @input="recalc(idx)"
                                           required>
                                </td>

                                <!-- Total -->
                                <td class="px-3 py-2 font-semibold">
                                    <span x-text="formatMoney(line.line_total)"></span>
                                </td>

                                <!-- Remove -->
                                <td class="px-3 py-2 text-right">
                                    <button type="button"
                                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50"
                                            @click="removeLine(idx)">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end text-base font-semibold">
                Grand Total:
                <span class="ml-2" x-text="formatMoney(grandTotal)"></span>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <a href="<?php echo e(route('returns.index')); ?>"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                Save Return
            </button>
        </div>
    </form>
</div>

<script>
function returnForm(groups, items) {
    return {
        groups,
        items,
        lines: [],
        grandTotal: 0,

        init() {
            this.addNewLine();
        },

        addNewLine() {
            this.lines.push({
                key: Date.now() + Math.random(),
                group_id: '',
                item_id: '',
                specification: '',
                unit_price: 0,
                quantity: 1,
                line_total: 0,
            });
            this.recalcAll();
        },

        removeLine(idx) {
            this.lines.splice(idx, 1);
            if (this.lines.length === 0) this.addNewLine();
            this.recalcAll();
        },

        itemsForGroup(groupId) {
            if (!groupId) return [];
            return this.items.filter(i => String(i.group_id) === String(groupId));
        },

        recalc(idx) {
            const line = this.lines[idx];
            const price = Number(line.unit_price || 0);
            const qty = Number(line.quantity || 0);
            line.line_total = Math.round(price * qty * 100) / 100;
            this.recalcAll();
        },

        recalcAll() {
            this.grandTotal = this.lines.reduce((s, l) => s + Number(l.line_total || 0), 0);
            this.grandTotal = Math.round(this.grandTotal * 100) / 100;
        },

        formatMoney(v) {
            return Number(v || 0).toFixed(2);
        }
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/returns/create.blade.php ENDPATH**/ ?>