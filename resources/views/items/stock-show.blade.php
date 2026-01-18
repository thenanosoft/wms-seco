@extends('layouts.app')

@section('content')
@php
    $balance = (float)($summary['balance'] ?? ($summary['available'] ?? 0));
    $totalIn = (float)($summary['total_in'] ?? 0);
    $totalOut = (float)($summary['total_out'] ?? 0);

    $lastPurchase = (float)($summary['last_purchase_price'] ?? 0);
    $avgPurchase  = (float)($summary['avg_purchase_price'] ?? 0);

    $valueLast = round($balance * $lastPurchase, 2);
    $valueAvg  = round($balance * $avgPurchase, 2);

    $isLow = (bool)($summary['is_low'] ?? false);
    $thresholdUsed = (float)($summary['threshold_used'] ?? 0);

    $commonFilters = array_filter([
        'from' => $from ?? null,
        'to' => $to ?? null,
        'item_id' => $item->id,
    ]);
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="text-sm text-gray-600">
                <a href="{{ route('stock.index') }}" class="hover:underline">Stock</a>
                <span class="mx-2">/</span>
                <span>Item Detail</span>
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

            @if(Route::has('issue-returns.create'))
                <a href="{{ route('issue-returns.create') }}"
                   class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Issue Return
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Balance Stock</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($balance, 3) }}</div>
            <div class="text-xs text-gray-600 mt-1">In: {{ number_format($totalIn, 3) }} | Out: {{ number_format($totalOut, 3) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Last Purchase Price</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($lastPurchase, 2) }}</div>
            <div class="text-xs text-gray-600 mt-1">Value (Last): {{ number_format($valueLast, 2) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Average Purchase Price</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($avgPurchase, 2) }}</div>
            <div class="text-xs text-gray-600 mt-1">Value (Avg): {{ number_format($valueAvg, 2) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Status</div>
            <div class="mt-2">
                @if($isLow)
                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">Low Stock</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">OK</span>
                @endif
            </div>
            <div class="text-xs text-gray-600 mt-2">
                Min Threshold: <span class="font-semibold">{{ number_format($thresholdUsed, 3) }}</span>
            </div>
        </div>
    </div>

    <div class="rounded-xl border bg-white p-4">
        <form method="GET" action="{{ route('items.stock.show', $item->id) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-4">
            <div>
                <label class="text-sm font-medium">From</label>
                <input type="date" name="from" value="{{ $from }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-sm font-medium">To</label>
                <input type="date" name="to" value="{{ $to }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Apply</button>
                <a href="{{ route('items.stock.show', $item->id) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border bg-white p-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-sm font-semibold">Quick Export</div>
                <div class="text-xs text-gray-600">Exports will respect selected date filters (where supported).</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('print.purchases', $commonFilters) }}" target="_blank">Print Purchases</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('export.purchases.csv', $commonFilters) }}">CSV Purchases</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('export.purchases.pdf', $commonFilters) }}">PDF Purchases</a>

                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('print.issues', $commonFilters) }}" target="_blank">Print Issues</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('export.issues.csv', $commonFilters) }}">CSV Issues</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('export.issues.pdf', $commonFilters) }}">PDF Issues</a>

                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('print.returns', $commonFilters) }}" target="_blank">Print Returns</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('export.returns.csv', $commonFilters) }}">CSV Returns</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('export.returns.pdf', $commonFilters) }}">PDF Returns</a>

                @if(Route::has('export.issue-returns.csv'))
                    <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('export.issue-returns.csv', $commonFilters) }}">CSV Issue Returns</a>
                    <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('export.issue-returns.pdf', $commonFilters) }}">PDF Issue Returns</a>
                @endif
            </div>
        </div>
    </div>

    @include('items.partials._history_table', [
        'title' => 'Purchase Price History',
        'subtitle' => 'Date-wise purchases for this item.',
        'rows' => $purchaseHistory,
        'qtyField' => 'qty_in',
    ])

    @include('items.partials._history_table', [
        'title' => 'Issue / Sale History',
        'subtitle' => 'Date-wise issues for this item.',
        'rows' => $issueHistory,
        'qtyField' => 'qty_out',
    ])

    @include('items.partials._history_table', [
        'title' => 'Issue Return History (Inward)',
        'subtitle' => 'Returns against issues (audit-safe).',
        'rows' => $issueReturnHistory,
        'qtyField' => 'qty_in',
    ])

</div>
@endsection
