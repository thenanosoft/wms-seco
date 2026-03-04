@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">FIFO Valuation Report</h1>
            <p class="mt-1 text-sm text-gray-600">Exact inventory value as of any date based on FIFO batches. Pending-price batches are valued as 0 until price is entered.</p>
        </div>
    </div>

    <form method="GET" class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-600">As of date</label>
                <input type="date" name="date" value="{{ $asOf }}" class="mt-1 rounded-lg border-gray-200">
            </div>
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Run Report</button>
        </div>
    </form>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Group</th>
                        <th class="px-4 py-3 text-left font-semibold">Item</th>
                        <th class="px-4 py-3 text-right font-semibold">Qty</th>
                        <th class="px-4 py-3 text-right font-semibold">Value</th>
                        <th class="px-4 py-3 text-left font-semibold">Pending</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($summary as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $r->group_code }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $r->item_code }} - {{ $r->item_name }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">{{ $r->qty }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float)$r->value, 4) }}</td>
                            <td class="px-4 py-3">
                                @if($r->pending_batches > 0)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">{{ $r->pending_batches }} batch(es)</span>
                                @else
                                    <span class="text-xs text-gray-500">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-600" colspan="5">No stock found for this date.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold" colspan="3">Total</th>
                        <th class="px-4 py-3 text-right font-semibold">{{ number_format((float)$grandTotal, 4) }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <details class="rounded-xl border border-gray-200 bg-white p-4">
        <summary class="cursor-pointer text-sm font-semibold">Show batch-level breakdown</summary>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">Batch</th>
                        <th class="px-3 py-2 text-left font-semibold">Purchase Date</th>
                        <th class="px-3 py-2 text-left font-semibold">Item</th>
                        <th class="px-3 py-2 text-right font-semibold">Remaining</th>
                        <th class="px-3 py-2 text-right font-semibold">Unit Price</th>
                        <th class="px-3 py-2 text-right font-semibold">Value</th>
                        <th class="px-3 py-2 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($batches as $b)
                        <tr>
                            <td class="px-3 py-2">#{{ $b->batch_id }}</td>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $b->purchase_date }}</td>
                            <td class="px-3 py-2">{{ $b->item_code }} - {{ $b->item_name }}</td>
                            <td class="px-3 py-2 text-right">{{ $b->remaining_qty }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)$b->unit_price_display, 4) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)$b->value, 4) }}</td>
                            <td class="px-3 py-2">
                                @if($b->price_pending)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Pending</span>
                                @else
                                    <span class="text-xs text-gray-500">OK</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</div>
@endsection
