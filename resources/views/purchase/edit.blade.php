@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto" x-data="purchaseEditForm({
    existingLines: @js($existingLines),
    groups: @js($groups),
    items: @js($items),
})">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Edit Purchase</h1>
            <p class="text-sm text-gray-600">Purchase #{{ $purchase->id }}</p>
        </div>
        <a href="{{ route('purchases.show', $purchase) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Back</a>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            <div class="font-semibold mb-2">Please fix the following:</div>
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('purchases.update', $purchase) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-600">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($purchase->purchase_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-gray-200" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Supplier Name</label>
                    <input type="text" name="supplier_name" value="{{ old('supplier_name', $purchase->supplier_name) }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Reference No</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no', $purchase->reference_no) }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Optional">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-xs text-gray-600">Notes</label>
                <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Optional">{{ old('notes', $purchase->notes) }}</textarea>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Item</th>
                            <th class="px-3 py-2 text-left font-semibold">Specification</th>
                            <th class="px-3 py-2 text-left font-semibold">Purchase Price</th>
                            <th class="px-3 py-2 text-left font-semibold">Qty</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" x-ref="tbody">
                        <template x-for="(line, idx) in lines" :key="line.key">
                            <tr>
                                <td class="px-3 py-2">
                                    <input type="hidden" :name="`lines[${idx}][id]`" :value="line.id">

                                    <template x-if="line.id">
                                        <div>
                                            <div class="text-sm font-semibold" x-text="line.item_label"></div>
                                            <input type="hidden" :name="`lines[${idx}][group_id]`" :value="line.group_id">
                                            <input type="hidden" :name="`lines[${idx}][item_id]`" :value="line.item_id">
                                        </div>
                                    </template>

                                    <template x-if="!line.id">
                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <select class="w-44 rounded-lg border-gray-200"
                                                    :name="`lines[${idx}][group_id]`"
                                                    x-model="line.group_id"
                                                    @change="line.item_id=''; line.item_label=''">
                                                <option value="">Select group</option>
                                                <template x-for="g in groups" :key="g.id">
                                                    <option :value="g.id" x-text="g.group_code"></option>
                                                </template>
                                            </select>
                                            <select class="w-72 rounded-lg border-gray-200"
                                                    :name="`lines[${idx}][item_id]`"
                                                    x-model="line.item_id"
                                                    @change="setLabel(idx)">
                                                <option value="">Select item</option>
                                                <template x-for="it in filteredItems(line.group_id)" :key="it.id">
                                                    <option :value="it.id" x-text="`${it.item_code} - ${it.name}`"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </template>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" class="w-72 rounded-lg border-gray-200" :name="`lines[${idx}][specification]`" x-model="line.specification" placeholder="Optional">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.0001" min="0" class="w-28 rounded-lg border-gray-200" :name="`lines[${idx}][purchase_price]`" x-model="line.purchase_price" placeholder="Pending">
                                    <div class="mt-1 text-xs text-gray-600" x-show="line.purchase_price === ''">Price pending</div>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.0001" min="0.0001" class="w-24 rounded-lg border-gray-200" :name="`lines[${idx}][quantity]`" x-model.number="line.quantity" required>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" @click="removeLine(idx)">Remove</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t flex items-center justify-between">
                <button type="button" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50" @click="addNewLine()">Add Item</button>
                <div class="text-xs text-gray-600">You can add missed items in the same purchase.</div>
            </div>
        </div>

        <div class="flex justify-end">
            <button class="rounded-lg bg-gray-900 px-6 py-2 text-sm font-medium text-white hover:bg-gray-800">Save Changes</button>
        </div>
    </form>
</div>

<script>
function purchaseEditForm({ existingLines, groups, items }) {
    return {
        groups,
        items,
        lines: [],

        init() {
            this.lines = (existingLines || []).map(l => ({
                key: Date.now() + Math.random(),
                id: l.id,
                group_id: l.group_id,
                item_id: l.item_id,
                item_label: l.item_label,
                specification: l.specification || '',
                purchase_price: (l.purchase_price === null || typeof l.purchase_price === 'undefined') ? '' : String(l.purchase_price),
                quantity: Number(l.quantity || 1),
            }));
            if (this.lines.length === 0) this.addNewLine();
        },

        addNewLine() {
            this.lines.push({
                key: Date.now() + Math.random(),
                id: null,
                group_id: '',
                item_id: '',
                item_label: '',
                specification: '',
                purchase_price: '',
                quantity: 1,
            });
        },

        filteredItems(groupId) {
            if (!groupId) return [];
            return this.items.filter(it => String(it.group_id) === String(groupId));
        },

        setLabel(idx) {
            const line = this.lines[idx];
            const it = this.items.find(x => String(x.id) === String(line.item_id));
            if (it) {
                line.item_label = `${it.item_code} - ${it.name}`;
                if (!line.specification && it.default_spec) line.specification = it.default_spec;
            }
        },

        removeLine(idx) {
            this.lines.splice(idx, 1);
            if (this.lines.length === 0) this.addNewLine();
        },
    }
}
</script>
@endsection
