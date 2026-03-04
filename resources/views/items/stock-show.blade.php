@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Item Stock Detail</h1>
            <div class="text-sm text-gray-600">
                {{ $item->group->group_code }} | {{ $item->item_code }} - {{ $item->name }}
            </div>
        </div>

        <a href="{{ route('stock.index') }}"
           class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Back</a>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('print.item.ledger', $item->id) }}" target="_blank"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

        <a href="{{ route('export.ledger.csv', ['item_id' => $item->id]) }}"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

        <a href="{{ route('export.item.ledger.pdf', $item->id) }}"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Available</div>
            <div class="mt-2 text-2xl font-semibold">{{ $summary['available'] }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total In</div>
            <div class="mt-2 text-2xl font-semibold">{{ $summary['total_in'] }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total Out</div>
            <div class="mt-2 text-2xl font-semibold">{{ $summary['total_out'] }}</div>
        </div>
    </div>

    <div class="rounded-xl border bg-white overflow-x-auto">
        <div class="p-4 border-b">
            <div class="text-sm font-semibold">History (Ledger)</div>
            <div class="text-xs text-gray-600">All purchases, issues, and returns in one audit-safe timeline.</div>
        </div>

        <div class="p-4 border-b bg-gray-50">
            <form method="GET" action="{{ route('items.stock.show', $item) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-600">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-lg border-gray-200">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="mt-1 w-full rounded-lg border-gray-200">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Type</label>
                    <select name="type" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        <option value="purchase" @selected(request('type')==='purchase')>Purchase</option>
                        <option value="issue" @selected(request('type')==='issue')>Issue</option>
                        <option value="ISSUE_RETURN_IN" @selected(request('type')==='ISSUE_RETURN_IN')>Issue Return</option>
                        <option value="PURCHASE_RETURN_OUT" @selected(request('type')==='PURCHASE_RETURN_OUT')>Purchase Return</option>
                    </select>
                </div>
                <div class="flex gap-2 lg:col-span-2">
                    <a href="{{ route('items.stock.show', $item) }}" class="mt-5 rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-white">Reset</a>
                    <button class="mt-5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Apply</button>
                </div>
            </form>
        </div>

        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-right">Qty In</th>
                    <th class="px-4 py-3 text-right">Qty Out</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-left">Spec</th>
                    <th class="px-4 py-3 text-left">Ref</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($history as $h)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $h->txn_date }}</td>
                        <td class="px-4 py-2">
                            @php
                                $t = (string)$h->txn_type;
                                $label = match($t) {
                                    'purchase' => 'Purchase',
                                    'issue' => 'Issue',
                                    'issue_return' => 'Issue Return',
                                    'purchase_return' => 'Purchase Return',
                                    default => $t,
                                };
                                $cls = match($t) {
                                    'purchase' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'issue' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'issue_return' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'purchase_return' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-gray-50 text-gray-700 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium {{ $cls }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">{{ $h->qty_in }}</td>
                        <td class="px-4 py-2 text-right">{{ $h->qty_out }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$h->unit_price, 4) }}</td>
                        <td class="px-4 py-2">{{ $h->specification_snapshot }}</td>
                        <td class="px-4 py-2 text-xs text-gray-600">
                            {{ $h->ref_table }} #{{ $h->ref_id }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $history->links() }}

</div>
@endsection
