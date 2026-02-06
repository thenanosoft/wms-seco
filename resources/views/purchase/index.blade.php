@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Purchases (Inward)</h1>
            <p class="mt-1 text-sm text-gray-600">Add and review inward entries.</p>
        </div>

        <a href="{{ route('purchases.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
            New Purchase
        </a>
    </div>

    <form method="GET" action="{{ route('purchases.index') }}" class="mb-4 rounded-xl border border-gray-200 bg-white p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-600">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-600">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-600">Supplier contains</label>
                <input type="text" name="supplier" value="{{ request('supplier') }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="e.g. ABC">
            </div>
            <div>
                <label class="block text-xs text-gray-600">Reference contains</label>
                <input type="text" name="ref" value="{{ request('ref') }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="e.g. PO-001">
            </div>
            <div class="flex gap-2">
                <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Filter</button>
                <a href="{{ route('purchases.index') }}" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-center hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('print.purchases', request()->query()) }}" target="_blank"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

        <a href="{{ route('export.purchases.csv', request()->query()) }}"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

        <a href="{{ route('export.purchases.pdf', request()->query()) }}"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Supplier</th>
                        <th class="px-4 py-3 text-left font-semibold">Ref</th>
                        <th class="px-4 py-3 text-left font-semibold">Created By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchases as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-left"><a href="{{ route('purchases.show', $p) }}" class="text-indigo-600 hover:underline">#{{ $p->id }}</a></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $p->purchase_date->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3">{{ $p->supplier_name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $p->reference_no ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $p->creator?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-600" colspan="4">
                                No purchases yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $purchases->links() }}
    </div>
</div>
@endsection
