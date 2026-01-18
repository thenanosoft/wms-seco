@extends('layouts.app')

@section('content')
@php
    $totalItems = count($rows);
    $lowCount = collect($rows)->filter(fn($r) => (bool)($r->is_low ?? false))->count();
    $totalBalance = collect($rows)->sum(fn($r) => (float)($r->balance ?? 0));
    $totalValueLast = collect($rows)->sum(fn($r) => (float)($r->value_last ?? 0));
@endphp

<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Stock Summary</h1>
        <p class="text-sm text-gray-600">Audit-safe balance from ledger (Purchase, Issue, Returns). Export anytime.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total Items</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($totalItems) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Low Stock Items</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($lowCount) }}</div>
            <div class="text-xs text-gray-600 mt-1">Based on item threshold or default setting</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total Balance Qty</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($totalBalance, 3) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Total Value (Last Price)</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($totalValueLast, 2) }}</div>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('print.stock') }}" target="_blank"
           class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Print</a>

        <a href="{{ route('export.stock.csv') }}"
           class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Export CSV</a>

        <a href="{{ route('export.stock.pdf') }}"
           class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Export PDF</a>
    </div>

    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Group</th>
                    <th class="px-4 py-3 text-left">Item</th>
                    <th class="px-4 py-3 text-right">In</th>
                    <th class="px-4 py-3 text-right">Out</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3 text-right">Last Price</th>
                    <th class="px-4 py-3 text-right">Avg Price</th>
                    <th class="px-4 py-3 text-right">Value (Last)</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rows as $r)
                    <tr class="hover:bg-gray-50 {{ ($r->is_low ?? false) ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $r->group_code }}</div>
                            @if(!empty($r->group_name))
                                <div class="text-xs text-gray-600">{{ $r->group_name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <a class="text-gray-900 font-medium hover:underline" href="{{ route('items.stock.show', $r->item_id) }}">
                                {{ $r->item_code }} - {{ $r->item_name }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$r->total_in, 3) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$r->total_out, 3) }}</td>
                        <td class="px-4 py-2 text-right font-semibold">{{ number_format((float)$r->balance, 3) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$r->last_purchase_price, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$r->avg_purchase_price, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$r->value_last, 2) }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            @if($r->is_low ?? false)
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">Low</span>
                                <span class="text-xs text-gray-600 ml-1">(Min: {{ number_format((float)($r->threshold_used ?? 0), 0) }})</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">OK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
