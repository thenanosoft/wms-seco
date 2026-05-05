@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="valuationFilters({
    allItems: @js($allItems),
    selectedGroupId: @js(request('group_id')),
    selectedItemId: @js(request('item_id')),
})">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">FIFO Valuation Report</h1>
            <p class="mt-1 text-sm text-gray-600">Simple view: har item ki remaining qty, average FIFO rate, aur total stock value.</p>
        </div>
    </div>

    <form method="GET" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-600">As of date</label>
                <input type="date" name="date" value="{{ $asOf }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-600">Group</label>
                <select name="group_id" x-model="selectedGroupId" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All groups</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected(request('group_id') == $g->id)>{{ $g->group_code }}{{ $g->group_name ? ' - ' . $g->group_name : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-600">Item</label>
                <select name="item_id" x-model="selectedItemId" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All items</option>
                    <template x-for="it in filteredItems" :key="it.id">
                        <option :value="String(it.id)" x-text="`${it.item_code} - ${it.name}`"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-600">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="item code / name / group">
            </div>
            <div class="flex items-center gap-2 pb-2">
                <input id="pending_only" type="checkbox" name="pending_only" value="1" @checked(request()->boolean('pending_only')) class="rounded border-gray-300">
                <label for="pending_only" class="text-sm text-gray-700">Pending prices only</label>
            </div>
            <div class="flex justify-end">
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Run Report</button>
            </div>
        </div>
    </form>

    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 sm:p-5">
        <div class="text-sm font-semibold text-indigo-900">Ye report kaise samjhein (simple)</div>
        <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-indigo-900">
            <div class="rounded-lg bg-white/70 p-3">
                <div class="font-medium">1) Remaining Qty</div>
                <div class="text-xs mt-1">Iss date tak item ka bacha hua stock.</div>
            </div>
            <div class="rounded-lg bg-white/70 p-3">
                <div class="font-medium">2) Avg FIFO Rate</div>
                <div class="text-xs mt-1">Value / Remaining Qty. FIFO batches ka effective rate.</div>
            </div>
            <div class="rounded-lg bg-white/70 p-3">
                <div class="font-medium">3) Stock Value</div>
                <div class="text-xs mt-1">Remaining Qty x FIFO rate (pending-price batch ka value 0 hota hai).</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-xs text-gray-500">Total Stock Value</div>
            <div class="mt-1 text-2xl font-semibold">{{ number_format((float)$grandTotal, 4) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-xs text-gray-500">Total Remaining Qty</div>
            <div class="mt-1 text-2xl font-semibold">{{ rtrim(rtrim(number_format((float)$totalQty, 8, '.', ''), '0'), '.') }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-xs text-gray-500">Items In Stock</div>
            <div class="mt-1 text-2xl font-semibold">{{ count($summary) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="text-xs text-gray-500">Pending Price Batches</div>
            <div class="mt-1 text-2xl font-semibold">{{ $pendingBatches }}</div>
            <div class="text-xs text-gray-500">Items affected: {{ $pendingItems }}</div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-semibold">Item Summary</h2>
            <div class="text-xs text-gray-500">Rows: {{ count($summary) }}</div>
        </div>
        <div class="overflow-x-auto max-h-[540px]">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Group</th>
                        <th class="px-4 py-3 text-left font-semibold">Item</th>
                        <th class="px-4 py-3 text-right font-semibold">Remaining Qty</th>
                        <th class="px-4 py-3 text-right font-semibold">Avg FIFO Rate</th>
                        <th class="px-4 py-3 text-right font-semibold">Stock Value</th>
                        <th class="px-4 py-3 text-left font-semibold">Pending</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($summary as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $r->group_code }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('items.stock.show', $r->item_id) }}" class="font-medium text-indigo-600 hover:underline">
                                    {{ $r->item_code }} - {{ $r->item_name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-right">{{ rtrim(rtrim(number_format((float)$r->qty, 8, '.', ''), '0'), '.') }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float)$r->avg_rate, 4) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float)$r->value, 4) }}</td>
                            <td class="px-4 py-3">
                                @if($r->pending_batches > 0)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">{{ $r->pending_batches }} batch(es)</span>
                                @else
                                    <span class="text-xs text-gray-500">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-600" colspan="6">No stock found for this filter/date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <details class="rounded-xl border border-gray-200 bg-white p-4">
        <summary class="cursor-pointer text-sm font-semibold">Batch-level breakdown (advanced)</summary>
        <div class="mt-4 overflow-x-auto max-h-[560px]">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">Batch</th>
                        <th class="px-3 py-2 text-left font-semibold">Purchase Date</th>
                        <th class="px-3 py-2 text-left font-semibold">Item</th>
                        <th class="px-3 py-2 text-right font-semibold">Remaining</th>
                        <th class="px-3 py-2 text-right font-semibold">Unit Price</th>
                        <th class="px-3 py-2 text-right font-semibold">Value</th>
                        <th class="px-3 py-2 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($batches as $b)
                        <tr>
                            <td class="px-3 py-2">#{{ $b->batch_id }}</td>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $b->purchase_date }}</td>
                            <td class="px-3 py-2">{{ $b->item_code }} - {{ $b->item_name }}</td>
                            <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format((float)$b->remaining_qty, 8, '.', ''), '0'), '.') }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)$b->unit_price_display, 4) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)$b->value, 4) }}</td>
                            <td class="px-3 py-2">
                                @if($b->price_pending)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Pending</span>
                                @else
                                    <span class="text-xs text-gray-500">OK</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</div>

<script>
function valuationFilters({ allItems, selectedGroupId, selectedItemId }) {
    return {
        allItems: Array.isArray(allItems) ? allItems : [],
        filteredItems: [],
        selectedGroupId: selectedGroupId ? String(selectedGroupId) : '',
        selectedItemId: selectedItemId ? String(selectedItemId) : '',
        init() {
            this.rebuildFilteredItems();
            this.$watch('selectedGroupId', () => {
                this.rebuildFilteredItems();
                const exists = this.filteredItems.some(it => String(it.id) === String(this.selectedItemId));
                if (!exists) this.selectedItemId = '';
            });
        },
        rebuildFilteredItems() {
            if (!this.selectedGroupId) {
                this.filteredItems = this.allItems;
                return;
            }
            this.filteredItems = this.allItems.filter(it => String(it.group_id) === String(this.selectedGroupId));
        },
    };
}
</script>
@endsection
