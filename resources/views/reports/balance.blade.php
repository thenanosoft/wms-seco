@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="balanceFilters({
    allItems: @js($allItems),
    selectedGroupId: @js(request('group_id')),
    selectedItemId: @js(request('item_id')),
    selectedRange: @js($range ?? 'all'),
    dateTouched: @js(request()->boolean('date_touched')),
})">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Balance Report</h1>
        <p class="mt-1 text-sm text-gray-600">Item-wise balance report with smart filters and exports.</p>
    </div>

    <form method="GET" action="{{ route('reports.balance.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
        <input type="hidden" name="date_touched" :value="dateTouched ? 1 : 0">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label class="text-sm font-medium">Range</label>
                <select name="range" class="mt-1 w-full rounded-lg border-gray-200" x-model="selectedRange" @change="onRangeChange()">
                    <option value="all" @selected(($range ?? 'all')==='all')>All</option>
                    <option value="today" @selected(($range ?? 'all')==='today')>Today</option>
                    <option value="weekly" @selected(($range ?? 'all')==='weekly')>Weekly</option>
                    <option value="monthly" @selected(($range ?? 'all')==='monthly')>Monthly</option>
                    <option value="yearly" @selected(($range ?? 'all')==='yearly')>Yearly</option>
                    <option value="custom" @selected(($range ?? 'all')==='custom')>Custom</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">From</label>
                <input type="date" name="from" value="{{ request('from', $from) }}" class="mt-1 w-full rounded-lg border-gray-200" @change="markCustom()">
            </div>

            <div>
                <label class="text-sm font-medium">To</label>
                <input type="date" name="to" value="{{ request('to', $to) }}" class="mt-1 w-full rounded-lg border-gray-200" @change="markCustom()">
            </div>

            <div>
                <label class="text-sm font-medium">Group</label>
                <select name="group_id" class="mt-1 w-full rounded-lg border-gray-200" x-model="selectedGroupId" @change="onGroupChange()">
                    <option value="">All</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected(request('group_id')==$g->id)>{{ $g->group_code }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Item</label>
                <select name="item_id" class="mt-1 w-full rounded-lg border-gray-200" x-model="selectedItemId">
                    <option value="">All</option>
                    <template x-for="it in filteredItems" :key="it.id">
                        <option :value="String(it.id)" x-text="`${it.item_code} - ${it.name}`"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="item code / name / group">
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('reports.balance.csv', request()->query()) }}">Export CSV</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('reports.balance.xls', request()->query()) }}">Export Excel</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('reports.balance.pdf', request()->query()) }}">Export PDF</a>
                <button name="apply" value="1" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Apply</button>
            </div>
        </div>
    </form>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">Group</th>
                        <th class="px-3 py-2 text-left font-semibold">Item</th>
                        <th class="px-3 py-2 text-right font-semibold">Purchased Qty</th>
                        <th class="px-3 py-2 text-right font-semibold">Purchased Amount</th>
                        <th class="px-3 py-2 text-right font-semibold">Issued Qty</th>
                        <th class="px-3 py-2 text-right font-semibold">Issued Amount</th>
                        <th class="px-3 py-2 text-right font-semibold">Available Qty</th>
                        <th class="px-3 py-2 text-right font-semibold">Per Item Price</th>
                        <th class="px-3 py-2 text-right font-semibold">Net Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php($tP=0)
                    @php($tPA=0)
                    @php($tI=0)
                    @php($tIA=0)
                    @php($tN=0)
                    @php($tUnit=0)
                    @php($tNA=0)
                    @forelse($rows as $r)
                        @php($tP += (float)$r->purchased_qty)
                        @php($tPA += (float)($r->purchased_amount ?? 0))
                        @php($tI += (float)$r->issued_qty)
                        @php($tIA += (float)($r->issued_amount ?? 0))
                        @php($tN += (float)$r->net_balance)
                        @php($tUnit += (float)($r->per_item_price ?? 0))
                        @php($tNA += (float)($r->net_amount ?? 0))
                        <tr>
                            <td class="px-3 py-2">{{ $r->group_code ?? '' }}</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('items.stock.show', $r->item_id) }}" class="text-indigo-600 hover:underline">
                                    {{ $r->item_code }} - {{ $r->item_name }}
                                </a>
                            </td>
                            <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format((float)$r->purchased_qty, 8, '.', ''), '0'), '.') }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)($r->purchased_amount ?? 0),4) }}</td>
                            <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format((float)$r->issued_qty, 8, '.', ''), '0'), '.') }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)($r->issued_amount ?? 0),4) }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ rtrim(rtrim(number_format((float)$r->net_balance, 8, '.', ''), '0'), '.') }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)($r->per_item_price ?? 0), 4) }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ number_format((float)($r->net_amount ?? 0), 4) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">No records found for this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-semibold">
                        <td class="px-3 py-2 text-right" colspan="2">Totals</td>
                        <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format((float)$tP, 8, '.', ''), '0'), '.') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tPA,4) }}</td>
                        <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format((float)$tI, 8, '.', ''), '0'), '.') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tIA,4) }}</td>
                        <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format((float)$tN, 8, '.', ''), '0'), '.') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format(count($rows) > 0 ? ($tUnit / count($rows)) : 0, 4) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tNA,4) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
function balanceFilters({ allItems, selectedGroupId, selectedItemId, selectedRange, dateTouched }) {
    return {
        allItems: Array.isArray(allItems) ? allItems : [],
        filteredItems: [],
        selectedGroupId: selectedGroupId ? String(selectedGroupId) : '',
        selectedItemId: selectedItemId ? String(selectedItemId) : '',
        selectedRange: selectedRange || 'all',
        dateTouched: Boolean(dateTouched),
        init() {
            this.rebuildFilteredItems();
        },
        markCustom() {
            this.selectedRange = 'custom';
            this.dateTouched = true;
        },
        onRangeChange() {
            if (this.selectedRange !== 'custom') {
                this.dateTouched = false;
            }
        },
        onGroupChange() {
            this.rebuildFilteredItems();
            const exists = this.filteredItems.some(it => String(it.id) === String(this.selectedItemId));
            if (!exists) this.selectedItemId = '';
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
