@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto" x-data="issueForm({{ $groups->toJson() }}, {{ $items->toJson() }})">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">New Issue (Outward)</h1>
        <p class="mt-1 text-sm text-gray-600">Fast entry screen. System will block issue more than available stock.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold">Please fix the errors:</div>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('issues.store') }}" class="space-y-4">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm font-medium">Date</label>
                    <input type="date" name="issue_date" class="mt-1 w-full rounded-lg border-gray-200"
                           value="{{ old('issue_date', now()->format('Y-m-d')) }}" required>
                </div>

                <div>
                    <label class="text-sm font-medium">Issued To</label>
                    <input type="text" name="issued_to" class="mt-1 w-full rounded-lg border-gray-200"
                           value="{{ old('issued_to') }}" placeholder="Optional">
                </div>

                <div>
                    <label class="text-sm font-medium">Reference No</label>
                    <input type="text" name="reference_no" class="mt-1 w-full rounded-lg border-gray-200"
                           value="{{ old('reference_no') }}" placeholder="Optional">
                </div>

                <div>
                    <label class="text-sm font-medium">Notes</label>
                    <input type="text" name="notes" class="mt-1 w-full rounded-lg border-gray-200"
                           value="{{ old('notes') }}" placeholder="Optional">
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold">Items</div>
                    <div class="text-xs text-gray-600">Tip: Select group then search item.</div>
                </div>

                <button type="button"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50"
                        @click="addLine()">
                    Add Line
                </button>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Group</th>
                            <th class="px-3 py-2 text-left font-semibold">Item</th>
                            <th class="px-3 py-2 text-left font-semibold">Specification</th>
                            <th class="px-3 py-2 text-left font-semibold">FIFO Price</th>
                            <th class="px-3 py-2 text-left font-semibold">Qty</th>
                            <th class="px-3 py-2 text-left font-semibold">Total</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(line, idx) in lines" :key="line.key">
                            <tr>
                                <td class="px-3 py-2">
                                    <select class="w-44 rounded-lg border-gray-200"
                                            :name="`lines[${idx}][group_id]`"
                                            x-model="line.group_id"
                                            @change="onGroupChange(idx)"
                                            required>
                                        <option value="">Select</option>
                                        <template x-for="g in groups" :key="g.id">
                                            <option :value="g.id" x-text="`${g.group_code}${g.group_name ? ' - ' + g.group_name : ''}`"></option>
                                        </template>
                                    </select>
                                </td>

                                <td class="px-3 py-2">
                                    <div class="w-72">
                                        <input
                                            type="text"
                                            class="w-full rounded-lg border-gray-200"
                                            x-model="line.item_search"
                                            @input="filterItemSearch(idx)"
                                            @focus="line.show_items = true"
                                            @click.away="line.show_items = false"
                                            placeholder="Search item code or name"
                                            autocomplete="off"
                                        >

                                        <input type="hidden" :name="`lines[${idx}][item_id]`" :value="line.item_id" required>

                                        <div x-show="line.show_items" x-cloak class="relative">
                                            <div class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-sm max-h-56 overflow-auto">
                                                <template x-for="it in line.filtered_items" :key="it.id">
                                                    <button
                                                        type="button"
                                                        class="w-full text-left px-3 py-2 hover:bg-gray-50"
                                                        @click="pickItem(idx, it)"
                                                    >
                                                        <div class="font-medium" x-text="`${it.item_code} - ${it.name}`"></div>
                                                        <div class="text-xs text-gray-600" x-text="`Group: ${groupCodeById(it.group_id)}`"></div>
                                                    </button>
                                                </template>

                                                <div x-show="line.filtered_items.length === 0" class="px-3 py-3 text-sm text-gray-600">
                                                    No items found.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-1 text-xs text-gray-600" x-show="line.item_id">
                                            Selected: <span class="font-semibold" x-text="line.item_label"></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text" class="w-72 rounded-lg border-gray-200"
                                           :name="`lines[${idx}][specification]`"
                                           x-model="line.specification"
                                           placeholder="Optional">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="1" min="0"
                                           class="w-28 rounded-lg border-gray-200 bg-gray-50"
                                           x-model.number="line.display_price"
                                           disabled>
                                    <div class="mt-1 text-xs text-gray-600">
                                        Estimated (actual uses FIFO batches)
                                    </div>
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number"
       step="1"
       min="1"
       :max="line.available_stock"
       class="w-24 rounded-lg border-gray-200"
       :name="`lines[${idx}][quantity]`"
       x-model.number="line.quantity"
       @input="validateQty(idx)"
       required>
       <div class="mt-1 text-xs text-gray-600">
    Available:
    <span class="font-semibold" x-text="line.available_stock"></span>
</div>


                                </td>

                                <td class="px-3 py-2 font-semibold">
                                    <span x-text="formatMoney(line.line_total)"></span>
                                </td>

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

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Lines: <span class="font-semibold" x-text="lines.length"></span>
                </div>

                <div class="text-base font-semibold">
                    Grand Total: <span x-text="formatMoney(grandTotal)"></span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('issues.index') }}"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                Save Issue
            </button>
        </div>
    </form>
</div>

<script>
function issueForm(groups, items) {
    return {
        groups,
        items,
        lines: [],
        grandTotal: 0,

        init() {
            this.addLine();
        },

        addLine() {
            this.lines.push({
                key: Date.now() + Math.random(),
                group_id: '',
                item_id: '',
                item_label: '',
                item_search: '',
                show_items: false,
                filtered_items: [],
                specification: '',
                display_price: 0,
                quantity: 1,
                line_total: 0,
                available_stock: 0,
            });
            this.recalcAll();
        },

        removeLine(idx) {
            this.lines.splice(idx, 1);
            if (this.lines.length === 0) this.addLine();
            this.recalcAll();
        },

        groupCodeById(groupId) {
            const g = this.groups.find(x => String(x.id) === String(groupId));
            if (!g) return '';
            return g.group_code + (g.group_name ? ` - ${g.group_name}` : '');
        },

        itemsForGroup(groupId) {
            if (!groupId) return [];
            return this.items.filter(i => String(i.group_id) === String(groupId));
        },

        onGroupChange(idx) {
            const line = this.lines[idx];
            line.item_id = '';
            line.item_label = '';
            line.item_search = '';
            line.specification = '';
            line.filtered_items = this.itemsForGroup(line.group_id).slice(0, 30);
            line.show_items = true;
            this.recalc(idx);
        },

        filterItemSearch(idx) {
            const line = this.lines[idx];
            const q = (line.item_search || '').toLowerCase().trim();

            if (!line.group_id) {
                line.filtered_items = [];
                return;
            }

            const pool = this.itemsForGroup(line.group_id);

            if (q.length === 0) {
                line.filtered_items = pool.slice(0, 30);
                return;
            }

            const filtered = pool.filter(it => {
                const code = (it.item_code || '').toLowerCase();
                const name = (it.name || '').toLowerCase();
                return code.includes(q) || name.includes(q);
            });

            line.filtered_items = filtered.slice(0, 30);
        },

        pickItem(idx, it) {
    const duplicate = this.lines.some((l, i) => i !== idx && String(l.item_id) === String(it.id));
    if (duplicate) {
        alert('This item is already added in another line.');
        return;
    }

    const line = this.lines[idx];

    line.item_id = it.id;
    line.item_label = `${it.item_code} - ${it.name}`;
    line.item_search = line.item_label;
    line.show_items = false;

    // Display only: last known price (actual issue price will be taken from FIFO batches)
    line.display_price = Number(it.last_price || 0);

    // Available stock
    line.available_stock = Number(it.available_stock || 0);

    // Reset qty if more than stock
    if (line.quantity > line.available_stock) {
        line.quantity = line.available_stock;
    }

    // Auto-fill spec
    if (!line.specification && it.default_spec) {
        line.specification = it.default_spec;
    }

    this.recalc(idx);
},

validateQty(idx) {
    const line = this.lines[idx];

    if (line.quantity > line.available_stock) {
        alert(`Only ${line.available_stock} in stock.`);
        line.quantity = line.available_stock;
    }

    if (line.quantity < 0) {
        line.quantity = 0;
    }

    this.recalc(idx);
},
        recalc(idx) {
            const line = this.lines[idx];
            const price = Number(line.display_price || 0);
            const qty = Number(line.quantity || 0);
            line.line_total = Math.round(price * qty * 100) / 100;
            this.recalcAll();
        },

        recalcAll() {
            this.grandTotal = this.lines.reduce((sum, l) => sum + Number(l.line_total || 0), 0);
            this.grandTotal = Math.round(this.grandTotal * 100) / 100;
        },

        formatMoney(v) {
            return Number(v || 0).toFixed(2);
        }
    }
}
</script>
@endsection
