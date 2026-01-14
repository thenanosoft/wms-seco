@extends('layouts.app')

@section('content')
@php
    $rowsCollection = collect($rows);

    $totalItems = $rowsCollection->count();
    $totalBalance = $rowsCollection->sum(fn($r) => (float)($r->balance ?? 0));

    $totalValueLast = $rowsCollection->sum(fn($r) => (float)($r->stock_value_last ?? 0));
    $totalValueAvg  = $rowsCollection->sum(fn($r) => (float)($r->stock_value_avg ?? 0));

    $lowCount = $rowsCollection->filter(fn($r) => (bool)($r->is_low ?? false))->count();
@endphp

<div class="max-w-7xl mx-auto space-y-4">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Stock Summary</h1>
            <p class="text-sm text-gray-600">
                Total Items: <span class="font-semibold">{{ $totalItems }}</span>
                <span class="mx-2">|</span>
                Total Balance Qty: <span class="font-semibold">{{ number_format($totalBalance, 0) }}</span>
                <span class="mx-2">|</span>
                Low Stock: <span class="font-semibold">{{ $lowCount }}</span>
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('print.stock') }}" target="_blank"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">
                Print
            </a>

            <a href="{{ route('export.stock.csv') }}"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">
                Export CSV
            </a>

            <a href="{{ route('export.stock.pdf') }}"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">
                Export PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total Value (Last Price)</div>
            <div class="mt-1 text-xl font-semibold">{{ number_format($totalValueLast, 0) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total Value (Avg Price)</div>
            <div class="mt-1 text-xl font-semibold">{{ number_format($totalValueAvg, 0) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Low Stock Items</div>
            <div class="mt-1 text-xl font-semibold">{{ $lowCount }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total Balance Qty</div>
            <div class="mt-1 text-xl font-semibold">{{ number_format($totalBalance, 0) }}</div>
        </div>
    </div>

    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Group</th>
                    <th class="px-4 py-3 text-left font-semibold">Item</th>

                    <th class="px-4 py-3 text-right font-semibold">Total In</th>
                    <th class="px-4 py-3 text-right font-semibold">Total Out</th>
                    <th class="px-4 py-3 text-right font-semibold">Balance</th>

                    <th class="px-4 py-3 text-right font-semibold">Last Purchase</th>
                    <th class="px-4 py-3 text-right font-semibold">Avg Purchase</th>

                    <th class="px-4 py-3 text-right font-semibold">Value (Last)</th>
                    <th class="px-4 py-3 text-right font-semibold">Value (Avg)</th>

                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @foreach($rows as $r)
                    <tr class="{{ ($r->is_low ?? false) ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ $r->group_code }}
                        </td>

                        <td class="px-4 py-2 min-w-[280px]">
                            <a class="text-gray-900 font-medium hover:underline"
                               href="{{ route('items.stock.show', $r->item_id) }}">
                                {{ $r->item_code }} - {{ $r->item_name }}
                            </a>
                        </td>

                        <td class="px-4 py-2 text-right">{{ number_format((float)($r->total_in ?? 0), 0) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)($r->total_out ?? 0), 0) }}</td>

                        <td class="px-4 py-2 text-right font-semibold">
                            {{ number_format((float)($r->balance ?? 0), 0) }}
                        </td>

                        <td class="px-4 py-2 text-right">
                            {{ number_format((float)($r->last_purchase_price ?? 0), 2) }}
                        </td>

                        <td class="px-4 py-2 text-right">
                            {{ number_format((float)($r->avg_purchase_price ?? 0), 2) }}
                        </td>

                        <td class="px-4 py-2 text-right font-semibold">
                            {{ number_format((float)($r->stock_value_last ?? 0), 0) }}
                        </td>

                        <td class="px-4 py-2 text-right">
                            {{ number_format((float)($r->stock_value_avg ?? 0), 0) }}
                        </td>

                        <td class="px-4 py-2 whitespace-nowrap">
                            @if($r->is_low ?? false)
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">
                                    Low
                                </span>
                                <span class="text-xs text-gray-600 ml-1">
                                    (Min: {{ number_format((float)($r->threshold_used ?? 0), 0) }})
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                    OK
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot class="bg-gray-50 text-gray-700">
                <tr>
                    <td class="px-4 py-3 font-semibold" colspan="5">Totals</td>
                    <td class="px-4 py-3"></td>
                    <td class="px-4 py-3"></td>
                    <td class="px-4 py-3 text-right font-semibold">{{ number_format($totalValueLast, 0) }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ number_format($totalValueAvg, 0) }}</td>
                    <td class="px-4 py-3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
