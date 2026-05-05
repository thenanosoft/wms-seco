@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Dashboard</h1>
            <p class="text-sm text-gray-600">Simple and effective stock overview.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('purchases.create') }}"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                New Purchase
            </a>

            <a href="{{ route('issues.create') }}"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                New Issue
            </a>

            <a href="{{ route('returns.index') }}"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                Returns
            </a>

            @if(auth()->user()?->role === 'admin')
                <a href="{{ route('stock.index') }}"
                   class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Stock
                </a>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dashboard', ['period' => 'today']) }}"
               class="rounded-lg px-4 py-2 text-sm font-medium {{ $period === 'today' ? 'bg-gray-900 text-white' : 'border border-gray-200 hover:bg-gray-50' }}">
                Today
            </a>
            <a href="{{ route('dashboard', ['period' => 'monthly']) }}"
               class="rounded-lg px-4 py-2 text-sm font-medium {{ $period === 'monthly' ? 'bg-gray-900 text-white' : 'border border-gray-200 hover:bg-gray-50' }}">
                Monthly (Last 30 Days)
            </a>
            <a href="{{ route('dashboard', ['period' => 'all']) }}"
               class="rounded-lg px-4 py-2 text-sm font-medium {{ $period === 'all' ? 'bg-gray-900 text-white' : 'border border-gray-200 hover:bg-gray-50' }}">
                All Time
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Purchases (Value)</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format((float)$currentStats->purchase_total, 4) }}</div>
            <div class="text-xs text-gray-600 mt-1">Qty: {{ rtrim(rtrim(number_format((float)$currentStats->purchase_qty, 8, '.', ''), '0'), '.') }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Issues (Value)</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format((float)$currentStats->issue_total, 4) }}</div>
            <div class="text-xs text-gray-600 mt-1">Qty: {{ rtrim(rtrim(number_format((float)$currentStats->issue_qty, 8, '.', ''), '0'), '.') }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Returns Inward (Value)</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format((float)$currentStats->return_in_total, 4) }}</div>
            <div class="text-xs text-gray-600 mt-1">Qty: {{ rtrim(rtrim(number_format((float)$currentStats->return_in_qty, 8, '.', ''), '0'), '.') }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Net Movement (Value)</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format((float)$currentStats->net_total, 4) }}</div>
            <div class="text-xs text-gray-600 mt-1">Purchase + Inward Return - Issue - Outward Return</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Available Stock Qty (Collective)</div>
            <div class="mt-2 text-2xl font-semibold">{{ rtrim(rtrim(number_format((float)$totalAvailableQty, 8, '.', ''), '0'), '.') }}</div>
            <div class="text-xs text-gray-600 mt-1">All items combined</div>
        </div>
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Current Stock Value</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format((float)$balanceValue, 4) }}</div>
            <div class="text-xs text-gray-600 mt-1">Based on ledger prices</div>
        </div>
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Items In Stock</div>
            <div class="mt-2 text-2xl font-semibold">{{ $itemsInStock }}</div>
            <div class="text-xs text-gray-600 mt-1">Out of {{ $itemsCount }} total items</div>
        </div>
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Pending Price Batches</div>
            <div class="mt-2 text-2xl font-semibold">{{ $pendingPriceBatches }}</div>
            <div class="text-xs text-gray-600 mt-1">Need price confirmation</div>
        </div>
    </div>

    <div class="rounded-xl border bg-white overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-sm font-semibold">Today vs Monthly vs All (Quick Compare)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Period</th>
                        <th class="px-4 py-3 text-right font-semibold">Purchase Value</th>
                        <th class="px-4 py-3 text-right font-semibold">Issue Value</th>
                        <th class="px-4 py-3 text-right font-semibold">Inward Return</th>
                        <th class="px-4 py-3 text-right font-semibold">Outward Return</th>
                        <th class="px-4 py-3 text-right font-semibold">Net Movement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php($labels = ['today' => 'Today', 'monthly' => 'Monthly (30 Days)', 'all' => 'All Time'])
                    @foreach(['today','monthly','all'] as $key)
                        @php($s = $periodStats[$key])
                        <tr class="{{ $period === $key ? 'bg-indigo-50/50' : '' }}">
                            <td class="px-4 py-3 font-medium">{{ $labels[$key] }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float)$s->purchase_total, 4) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float)$s->issue_total, 4) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float)$s->return_in_total, 4) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float)$s->return_out_total, 4) }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format((float)$s->net_total, 4) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
