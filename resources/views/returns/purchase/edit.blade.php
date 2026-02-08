@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Edit Purchase Return</h1>
            <p class="text-sm text-gray-600">Return #{{ $txn->id }} against Purchase #{{ $purchase->id }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('returns.purchase.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Back</a>
            <form action="{{ route('returns.purchase.destroy', $txn) }}" method="POST"
                  onsubmit="return confirm('Delete this return? Stock will be restored back.')">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-red-200 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete</button>
            </form>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
            <ul class="list-disc pl-5 text-sm">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('returns.purchase.update', $txn) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Return Date</label>
                    <input type="date" name="return_date" value="{{ old('return_date', optional($txn->return_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-gray-200" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Notes</label>
                    <input type="text" name="notes" value="{{ old('notes', $txn->notes) }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Optional">
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Returned Items</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left">Item</th>
                            <th class="px-4 py-2 text-right">Purchase Price</th>
                            <th class="px-4 py-2 text-right">Return Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($txn->lines as $idx => $l)
                            @php $pl = $l->purchaseLine; @endphp
                            <tr>
                                <td class="px-4 py-2">
                                    {{ optional($pl?->item)->item_code }} - {{ optional($pl?->item)->name }}
                                    <div class="text-xs text-gray-500">Purchase Line #{{ $l->purchase_line_id }}</div>
                                    <input type="hidden" name="lines[{{ $idx }}][id]" value="{{ $l->id }}">
                                </td>
                                <td class="px-4 py-2 text-right">{{ number_format((float)($pl?->purchase_price ?? 0), 0) }}</td>
                                <td class="px-4 py-2 text-right">
                                    <input type="number" min="0" step="1" class="w-24 rounded-lg border-gray-200 text-right"
                                           name="lines[{{ $idx }}][quantity]"
                                           value="{{ old('lines.'.$idx.'.quantity', (int)$l->quantity) }}" required>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            <button class="rounded-lg bg-gray-900 px-6 py-2 text-sm font-medium text-white hover:bg-gray-800">Save Changes</button>
        </div>
    </form>
</div>
@endsection
