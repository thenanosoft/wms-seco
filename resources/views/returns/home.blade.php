@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Returns</h1>
        <p class="mt-1 text-sm text-gray-600">No manual returns. Use Issue Return (items coming back from issued) or Purchase Return (items going back to supplier).</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="text-sm font-semibold">Issue Return (Inward)</div>
            <div class="mt-1 text-sm text-gray-600">Return only from existing issues. Price auto from issue.</div>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('returns.issue.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">View History</a>
                <a href="{{ route('returns.issue.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">New Return</a>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="text-sm font-semibold">Purchase Return (Outward)</div>
            <div class="mt-1 text-sm text-gray-600">Return only from existing purchases. Quantity capped by stock.</div>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('returns.purchase.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">View History</a>
                <a href="{{ route('returns.purchase.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">New Return</a>
            </div>
        </div>
    </div>
</div>
@endsection
