@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Pending Prices</h1>
            <p class="mt-1 text-sm text-gray-600">Purchase lines where price is missing. Update prices from the Purchase Edit screen.
            </p>
        </div>
        <a href="{{ route('purchases.index', ['pending' => 1]) }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Open Purchases</a>
    </div>

    <form method="GET" class="mb-4 rounded-xl border border-gray-200 bg-white p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-600">Supplier contains</label>
                <input type="text" name="supplier" value="{{ request('supplier') }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="e.g. ABC">
            </div>
            <div class="flex gap-2">
                <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Filter</button>
                <a href="{{ route('pending_prices.index') }}" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-center hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Purchase</th>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Supplier</th>
                        <th class="px-4 py-3 text-left font-semibold">Item</th>
                        <th class="px-4 py-3 text-left font-semibold">Spec</th>
                        <th class="px-4 py-3 text-right font-semibold">Qty</th>
                        <th class="px-4 py-3 text-left font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($lines as $l)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('purchases.show', $l->purchase_id) }}" class="text-indigo-600 hover:underline">#{{ $l->purchase_id }}</a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ optional($l->purchase?->purchase_date)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $l->purchase?->supplier_name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $l->item?->item_code }} - {{ $l->item?->name }}</div>
                                <div class="text-xs text-gray-500">{{ $l->item?->group?->group_code }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $l->specification ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">{{ (int)$l->quantity }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('purchases.edit', $l->purchase_id) }}" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-medium text-white hover:bg-gray-800">Update Price</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-600" colspan="7">No pending prices.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $lines->links() }}</div>
</div>
@endsection
