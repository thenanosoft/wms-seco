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
                        <td class="px-4 py-2 font-semibold">{{ $h->txn_type }}</td>
                        <td class="px-4 py-2 text-right">{{ $h->qty_in }}</td>
                        <td class="px-4 py-2 text-right">{{ $h->qty_out }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($h->unit_price, 2) }}</td>
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
