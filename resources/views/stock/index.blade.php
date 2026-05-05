@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="stockFilters({
    allItems: @js($allItems),
    selectedGroupId: @js(request('group_id')),
    selectedItemId: @js(request('item_id')),
})">
    @php
        $currentSort = request('sort', 'group_code');
        $currentDir = strtolower((string)request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortUrl = function (string $key) use ($currentSort, $currentDir) {
            $nextDir = ($currentSort === $key && $currentDir === 'asc') ? 'desc' : 'asc';
            return route('stock.index', array_merge(request()->query(), ['sort' => $key, 'dir' => $nextDir]));
        };
        $sortIndicator = function (string $key) use ($currentSort, $currentDir) {
            if ($currentSort !== $key) return ' ';
            return $currentDir === 'asc' ? '↑' : '↓';
        };
    @endphp
    <h1 class="text-2xl font-semibold mb-4">Stock Summary</h1>

    <form method="GET" action="{{ route('stock.index') }}" class="mb-4 rounded-xl border border-gray-200 bg-white p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-600">Group</label>
                <select name="group_id" x-model="selectedGroupId" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All Groups</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected(request('group_id') == $g->id)>
                            {{ $g->group_code }}{{ $g->group_name ? ' - ' . $g->group_name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-600">Item</label>
                <select name="item_id" x-model="selectedItemId" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All Items</option>
                    <template x-for="it in filteredItems" :key="it.id">
                        <option :value="String(it.id)" x-text="`${it.item_code} - ${it.name}`"></option>
                    </template>
                </select>
            </div>
            <div class="flex gap-2">
                <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Apply</button>
                <a href="{{ route('stock.index') }}" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-center hover:bg-gray-50">Reset</a>
            </div>
            <div class="text-xs text-gray-500 lg:text-right">
                Tip: Header par click karke sort karein
            </div>
        </div>
    </form>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('print.stock', request()->query()) }}" target="_blank"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

        <a href="{{ route('export.stock.csv', request()->query()) }}"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

        <a href="{{ route('export.stock.pdf', request()->query()) }}"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
    </div>


    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <a href="{{ $sortUrl('group_code') }}" class="inline-flex items-center gap-1 hover:underline">
                            Group <span class="text-xs">{{ $sortIndicator('group_code') }}</span>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <a href="{{ $sortUrl('item_code') }}" class="inline-flex items-center gap-1 hover:underline">
                            Item <span class="text-xs">{{ $sortIndicator('item_code') }}</span>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-right">
                        <a href="{{ $sortUrl('total_in') }}" class="inline-flex items-center gap-1 hover:underline">
                            Total In <span class="text-xs">{{ $sortIndicator('total_in') }}</span>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-right">
                        <a href="{{ $sortUrl('total_out') }}" class="inline-flex items-center gap-1 hover:underline">
                            Total Out <span class="text-xs">{{ $sortIndicator('total_out') }}</span>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-right">
                        <a href="{{ $sortUrl('balance') }}" class="inline-flex items-center gap-1 hover:underline">
                            Balance <span class="text-xs">{{ $sortIndicator('balance') }}</span>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">Low Stock</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @php
                    $fmtQty = function ($v) {
                        $n = (float) $v;
                        if (abs($n - round($n)) < 0.00000001) {
                            return (string) ((int) round($n));
                        }
                        return rtrim(rtrim(number_format($n, 4, '.', ''), '0'), '.');
                    };
                @endphp
                @foreach($rows as $r)
                    <tr class="{{ $r->is_low ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-2">{{ $r->group_code }}</td>
                        <td class="px-4 py-2"><a class="text-blue-700 hover:underline"
   href="{{ route('items.stock.show', $r->item_id) }}">
   {{ $r->item_code }} – {{ $r->item_name }}
</a></td>
                        <td class="px-4 py-2 text-right">{{ $fmtQty($r->total_in) }}</td>
                        <td class="px-4 py-2 text-right">{{ $fmtQty($r->total_out) }}</td>
                        <td class="px-4 py-2 text-right font-semibold">
                            {{ $fmtQty($r->balance) }}
                        </td>
                        <td class="px-4 py-2">
                            @if($r->is_low)
                                <span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
                                    Low
                                </span>
                                <span> (Min: {{ $fmtQty($r->threshold_used) }})</span>
                            @else
                                <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                    OK
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function stockFilters({ allItems, selectedGroupId, selectedItemId }) {
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
