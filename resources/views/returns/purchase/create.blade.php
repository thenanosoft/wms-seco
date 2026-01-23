@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">New Purchase Return (Outward)</h1>
        <p class="mt-1 text-sm text-gray-600">Select a Purchase, then return only allowed quantities. Quantity is limited by remaining from that purchase and current available stock.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold">Please fix the errors:</div>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('returns.purchase.create') }}" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
            <div>
                <label class="text-sm font-medium">Select Purchase</label>
                <select name="purchase_id" class="mt-1 w-full rounded-lg border-gray-200" required>
                    <option value="">Select</option>
                    @foreach($purchases as $p)
                        <option value="{{ $p->id }}" @selected(request('purchase_id') == $p->id)>
                            {{ $p->purchase_date }} | {{ $p->group_code ?? 'Group' }} | {{ $p->reference_no ?? 'No Ref' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Load Items</button>
            </div>
        </div>
    </form>

    @if($selectedPurchase)
        <form method="POST" action="{{ route('returns.purchase.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="purchase_id" value="{{ $selectedPurchase->id }}">

            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="text-sm font-medium">Return Date</label>
                        <input type="date" name="return_date" class="mt-1 w-full rounded-lg border-gray-200" value="{{ old('return_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="text-sm font-medium">Notes</label>
                        <input type="text" name="notes" class="mt-1 w-full rounded-lg border-gray-200" value="{{ old('notes') }}" placeholder="Optional">
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
                <div class="text-sm font-semibold">Return Lines</div>
                <div class="text-xs text-gray-600">Max Return = min(Remaining from this Purchase, Available in Stock).</div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Group</th>
                                <th class="px-3 py-2 text-left font-semibold">Item</th>
                                <th class="px-3 py-2 text-left font-semibold">Spec</th>
                                <th class="px-3 py-2 text-right font-semibold">Purchased</th>
                                <th class="px-3 py-2 text-right font-semibold">Returned</th>
                                <th class="px-3 py-2 text-right font-semibold">Remaining</th>
                                <th class="px-3 py-2 text-right font-semibold">Available</th>
                                <th class="px-3 py-2 text-right font-semibold">Max Return</th>
                                <th class="px-3 py-2 text-right font-semibold">Return Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($lines as $idx => $l)
                                <tr>
                                    <td class="px-3 py-2">{{ $l['group_code'] }}</td>
                                    <td class="px-3 py-2">{{ $l['item_code'] }} - {{ $l['item_name'] }}</td>
                                    <td class="px-3 py-2">{{ $l['specification'] }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($l['purchased_qty'], 0) }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($l['returned_qty'], 0) }}</td>
                                    <td class="px-3 py-2 text-right font-semibold">{{ number_format($l['remaining_from_purchase'], 0) }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($l['available_now'], 0) }}</td>
                                    <td class="px-3 py-2 text-right font-semibold">{{ number_format($l['max_return_qty'], 0) }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <input type="hidden" name="lines[{{ $idx }}][purchase_line_id]" value="{{ $l['line_id'] }}">
                                        <input type="number" step="1" min="0" max="{{ $l['max_return_qty'] }}"
                                               name="lines[{{ $idx }}][quantity]"
                                               value="{{ old("lines.$idx.quantity", 0) }}"
                                               class="w-28 rounded-lg border-gray-200 text-right">
                                        <div class="mt-1 text-xs text-gray-500">Max {{ number_format($l['max_return_qty'], 0) }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('returns.purchase.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancel</a>
                <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">Save Return</button>
            </div>
        </form>
    @endif
</div>
@endsection
