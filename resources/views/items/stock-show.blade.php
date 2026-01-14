@extends('layouts.app')

@section('content')
@php
    $balance = (float)($summary['balance'] ?? 0);
    $totalIn = (float)($summary['total_in'] ?? 0);
    $totalOut = (float)($summary['total_out'] ?? 0);

    $lastPurchase = (float)($summary['last_purchase_price'] ?? 0);
    $avgPurchase  = (float)($summary['avg_purchase_price'] ?? 0);

    $valueLast = round($balance * $lastPurchase, 2);
    $valueAvg  = round($balance * $avgPurchase, 2);
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="text-sm text-gray-600">
                <a href="{{ route('items.index') }}" class="hover:underline">Items</a>
                <span class="mx-2">/</span>
                <span>Stock</span>
            </div>

            <h1 class="text-2xl font-semibold mt-1">
                {{ $item->item_code }} - {{ $item->name }}
            </h1>

            <div class="text-sm text-gray-600 mt-1">
                Group:
                <span class="font-medium text-gray-900">
                    {{ $item->group->group_code }}{{ $item->group->group_name ? ' - '.$item->group->group_name : '' }}
                </span>
            </div>
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

            <a href="{{ route('returns.create') }}"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                New Return
            </a>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Balance Stock</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($balance, 0) }}</div>
            <div class="text-xs text-gray-600 mt-1">Total In: {{ number_format($totalIn, 0) }} | Total Out: {{ number_format($totalOut, 0) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Last Purchase Price</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($lastPurchase, 0) }}</div>
            <div class="text-xs text-gray-600 mt-1">Value (Last): {{ number_format($valueLast, 0) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Avg Purchase Price</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($avgPurchase, 0) }}</div>
            <div class="text-xs text-gray-600 mt-1">Value (Avg): {{ number_format($valueAvg, 0) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Low Stock Threshold</div>
            <div class="mt-2 text-2xl font-semibold">
                {{ $item->low_stock_threshold !== null ? number_format((float)$item->low_stock_threshold, 3) : 'Default' }}
            </div>
            <div class="text-xs text-gray-600 mt-1">
                Status:
                @if(!empty($summary['is_low']))
                    <span class="font-semibold text-red-700">Low</span>
                @else
                    <span class="font-semibold text-green-700">OK</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-xl border bg-white p-4">
        <form method="GET" action="{{ route('items.stock.show', $item->id) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-4">
            <div>
                <label class="text-sm font-medium">From</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="mt-1 w-full rounded-lg border-gray-200">
            </div>

            <div>
                <label class="text-sm font-medium">To</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="mt-1 w-full rounded-lg border-gray-200">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Apply Filter
                </button>

                <a href="{{ route('items.stock.show', $item->id) }}"
                   class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Purchase History --}}
    <div class="rounded-xl border bg-white overflow-x-auto">
        <div class="px-4 py-3 border-b">
            <div class="text-sm font-semibold">Purchase Price History</div>
            <div class="text-xs text-gray-600">Date-wise purchase prices for this item.</div>
        </div>

        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Date</th>
                    <th class="px-4 py-3 text-left font-semibold">Specification</th>
                    <th class="px-4 py-3 text-right font-semibold">Qty In</th>
                    <th class="px-4 py-3 text-right font-semibold">Price</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($purchaseHistory as $row)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $row->txn_date }}</td>
                        <td class="px-4 py-2">{{ $row->specification_snapshot ?? '-' }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->qty_in, 0) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->unit_price, 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-3 text-gray-600" colspan="4">No purchase history found for selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4">
            {{ $purchaseHistory->appends(['from' => $from, 'to' => $to])->links() }}
        </div>
    </div>

    {{-- Sale/Issue History --}}
    <div class="rounded-xl border bg-white overflow-x-auto">
        <div class="px-4 py-3 border-b">
            <div class="text-sm font-semibold">Sale / Issue Price History</div>
            <div class="text-xs text-gray-600">Date-wise sale/issue prices for this item.</div>
        </div>

        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Date</th>
                    <th class="px-4 py-3 text-left font-semibold">Specification</th>
                    <th class="px-4 py-3 text-right font-semibold">Qty Out</th>
                    <th class="px-4 py-3 text-right font-semibold">Price</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($saleHistory as $row)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $row->txn_date }}</td>
                        <td class="px-4 py-2">{{ $row->specification_snapshot ?? '-' }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->qty_out, 0) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$row->unit_price, 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-3 text-gray-600" colspan="4">No sale/issue history found for selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4">
            {{ $saleHistory->appends(['from' => $from, 'to' => $to])->links() }}
        </div>
    </div>

</div>
@endsection
