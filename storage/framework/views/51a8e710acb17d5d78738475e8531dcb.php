<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto" x-data="issueReturnForm()">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Return Against Issue (Inward)</h1>
        <p class="mt-1 text-sm text-gray-600">Helper can return only from issued items. System blocks returning more than issued quantity, and price is auto-fixed from that issue.</p>
    </div>

    <?php if($errors->any()): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold">Please fix the errors:</div>
            <ul class="mt-2 list-disc pl-5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('issue-returns.store')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm font-medium">Return Date</label>
                    <input type="date" name="return_date" class="mt-1 w-full rounded-lg border-gray-200"
                           value="<?php echo e(old('return_date', now()->format('Y-m-d'))); ?>" required>
                </div>

                <div>
                    <label class="text-sm font-medium">Select Issue</label>
                    <select name="issue_id" class="mt-1 w-full rounded-lg border-gray-200" x-model="issue_id" @change="loadLines" required>
                        <option value="">Select</option>
                        <?php $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($i->id); ?>">
                                #<?php echo e($i->id); ?> | <?php echo e($i->issue_date->format('Y-m-d')); ?> | <?php echo e($i->issued_to ?? 'N/A'); ?> <?php echo e($i->reference_no ? ' | Ref: '.$i->reference_no : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="mt-1 text-xs text-gray-600">Only latest 200 issues shown. You can search by typing after click.</div>
                </div>

                <div>
                    <label class="text-sm font-medium">Received From</label>
                    <input type="text" name="received_from" class="mt-1 w-full rounded-lg border-gray-200"
                           value="<?php echo e(old('received_from')); ?>" placeholder="Optional">
                </div>

                <div>
                    <label class="text-sm font-medium">Reference No</label>
                    <input type="text" name="reference_no" class="mt-1 w-full rounded-lg border-gray-200"
                           value="<?php echo e(old('reference_no')); ?>" placeholder="Optional">
                </div>

                <div class="lg:col-span-4">
                    <label class="text-sm font-medium">Notes</label>
                    <input type="text" name="notes" class="mt-1 w-full rounded-lg border-gray-200"
                           value="<?php echo e(old('notes')); ?>" placeholder="Optional">
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold">Issue Items</div>
                    <div class="text-xs text-gray-600">Select lines to return. Quantity cannot exceed remaining.</div>
                </div>

                <div class="text-sm text-gray-600" x-show="loading">Loading...</div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Return</th>
                            <th class="px-3 py-2 text-left font-semibold">Item</th>
                            <th class="px-3 py-2 text-left font-semibold">Spec</th>
                            <th class="px-3 py-2 text-right font-semibold">Issued</th>
                            <th class="px-3 py-2 text-right font-semibold">Returned</th>
                            <th class="px-3 py-2 text-right font-semibold">Remaining</th>
                            <th class="px-3 py-2 text-right font-semibold">Price</th>
                            <th class="px-3 py-2 text-right font-semibold">Return Qty</th>
                            <th class="px-3 py-2 text-right font-semibold">Line Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(l, idx) in lines" :key="l.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <input type="checkbox" class="rounded border-gray-300" x-model="l.selected" @change="onToggle(idx)">
                                </td>
                                <td class="px-3 py-2">
                                    <div class="font-medium" x-text="`${l.group_code} | ${l.item_code} - ${l.item_name}`"></div>
                                    <div class="text-xs text-gray-600">Issue Line #<span x-text="l.id"></span></div>
                                </td>
                                <td class="px-3 py-2" x-text="l.specification || '-'" ></td>
                                <td class="px-3 py-2 text-right" x-text="formatQty(l.issued_qty)"></td>
                                <td class="px-3 py-2 text-right" x-text="formatQty(l.returned_qty)"></td>
                                <td class="px-3 py-2 text-right font-semibold" x-text="formatQty(l.remaining_qty)"></td>
                                <td class="px-3 py-2 text-right" x-text="formatMoney(l.issue_price)"></td>
                                <td class="px-3 py-2 text-right">
                                    <input type="number" step="0.001" min="0" :max="l.remaining_qty" class="w-28 rounded-lg border-gray-200 text-right"
                                           x-model.number="l.return_qty" @input="validate(idx)" :disabled="!l.selected">
                                </td>
                                <td class="px-3 py-2 text-right font-semibold" x-text="formatMoney(l.line_total)"></td>
                            </tr>
                        </template>

                        <tr x-show="lines.length === 0">
                            <td colspan="9" class="px-3 py-6 text-center text-sm text-gray-600">
                                Select an issue to load items.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Hidden inputs for selected lines -->
            <template x-for="(l, idx) in selectedLines" :key="l.id">
                <div>
                    <input type="hidden" :name="`lines[${idx}][issue_line_id]`" :value="l.id">
                    <input type="hidden" :name="`lines[${idx}][quantity]`" :value="l.return_qty">
                </div>
            </template>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Selected Lines: <span class="font-semibold" x-text="selectedLines.length"></span>
                </div>

                <div class="text-base font-semibold">
                    Grand Total: <span x-text="formatMoney(grandTotal)"></span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="<?php echo e(route('issue-returns.index')); ?>"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800"
                    :disabled="selectedLines.length === 0">
                Save Return
            </button>
        </div>
    </form>
</div>

<script>
function issueReturnForm() {
    return {
        issue_id: '',
        loading: false,
        lines: [],

        get selectedLines() {
            return this.lines.filter(l => l.selected && Number(l.return_qty || 0) > 0);
        },

        get grandTotal() {
            return this.selectedLines.reduce((sum, l) => sum + Number(l.line_total || 0), 0);
        },

        async loadLines() {
            this.lines = [];
            if (!this.issue_id) return;

            this.loading = true;
            try {
                const res = await fetch(`<?php echo e(url('/issue-returns/issue')); ?>/${this.issue_id}/lines`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.lines = (data.lines || []).map(l => ({
                    ...l,
                    selected: false,
                    return_qty: 0,
                    line_total: 0,
                }));
            } catch (e) {
                alert('Failed to load issue lines.');
            } finally {
                this.loading = false;
            }
        },

        onToggle(idx) {
            const l = this.lines[idx];
            if (!l.selected) {
                l.return_qty = 0;
                l.line_total = 0;
                return;
            }
            // default: remaining qty
            l.return_qty = Number(l.remaining_qty || 0);
            this.validate(idx);
        },

        validate(idx) {
            const l = this.lines[idx];
            const max = Number(l.remaining_qty || 0);
            let qty = Number(l.return_qty || 0);

            if (qty < 0) qty = 0;
            if (qty > max) {
                qty = max;
                alert(`Only ${this.formatQty(max)} remaining for this line.`);
            }

            l.return_qty = qty;
            l.line_total = Math.round((qty * Number(l.issue_price || 0)) * 100) / 100;
        },

        formatMoney(v) {
            return Number(v || 0).toFixed(2);
        },

        formatQty(v) {
            return Number(v || 0).toFixed(3);
        },
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/farhanellahi/Development/web/laravel/wms/resources/views/issue-returns/create.blade.php ENDPATH**/ ?>