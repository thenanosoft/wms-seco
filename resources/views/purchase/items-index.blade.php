@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Purchase Items</h1>
        <p class="text-sm text-gray-600">Line-wise purchase history and stock base.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('print.purchases', request()->query()) }}" target="_blank"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

        <a href="{{ route('export.purchases.csv', request()->query()) }}"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

        <a href="{{ route('export.purchases.pdf', request()->query()) }}"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
    </div>


    <!-- Filters -->
    <form method="GET" class="rounded-xl border bg-white p-4 grid grid-cols-1 md:grid-cols-5 gap-4">
        <select name="group_id" class="rounded-lg border-gray-200">
            <option value="">All Groups</option>
            @foreach($groups as $g)
                <option value="{{ $g->id }}" @selected(request('group_id') == $g->id)>
                    {{ $g->group_code }} - {{ $g->group_name }}
                </option>
            @endforeach
        </select>

        <select name="item_id" class="rounded-lg border-gray-200">
            <option value="">All Items</option>
            @foreach($items as $i)
                <option value="{{ $i->id }}" @selected(request('item_id') == $i->id)>
                    {{ $i->item_code }} - {{ $i->name }}
                </option>
            @endforeach
        </select>

        <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-200">
        <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-200">

        <button class="rounded-lg bg-gray-900 text-white px-4 py-2 text-sm">
            Filter
        </button>
    </form>

    <!-- Table -->
    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Group</th>
                    <th class="px-4 py-3 text-left">Item</th>
                    <th class="px-4 py-3 text-right">Qty In</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($rows as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $r->purchase_date }}</td>
                        <td class="px-4 py-2">{{ $r->group_code }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('items.stock.show', $r->item_id) }}" class="text-indigo-600 hover:underline">
                                {{ $r->item_code }} - {{ $r->item_name }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$r->quantity, 4) }}</td>
                        <td class="px-4 py-2 text-right">
                            @if($r->purchase_price === null)
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">PENDING</span>
                            @else
                                {{ number_format((float)$r->purchase_price, 4) }}
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right font-semibold">
                            {{ number_format((float)$r->line_total, 4) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $rows->links() }}
</div>
@endsection
