@extends('layouts.app')

@section('content')

    <div>
        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <p class="text-sm text-gray-600">Daily overview for store operations.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Purchases</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($purchase->total, 2) }}</div>
            <div class="text-xs text-gray-600 mt-1">Qty: {{ $purchase->qty }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Issues</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($issue->total, 2) }}</div>
            <div class="text-xs text-gray-600 mt-1">Qty: {{ $issue->qty }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Return Inward</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($returnIn->qty, 3) }}</div>
            <div class="text-xs text-gray-600 mt-1">Value: {{ number_format($returnIn->total, 2) }}</div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Return Outward</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($returnOut->qty, 3) }}</div>
            <div class="text-xs text-gray-600 mt-1">Value: {{ number_format($returnOut->total, 2) }}</div>
        </div>

    </div>

    <div class="rounded-xl border bg-white p-4">
        <div class="text-sm text-gray-600">Total Items</div>
        <div class="mt-2 text-2xl font-semibold">{{ $itemsCount }}</div>
    </div>

</div>
@endsection
