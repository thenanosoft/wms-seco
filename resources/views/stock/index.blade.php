@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Stock Summary</h1>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('print.stock') }}" target="_blank"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

        <a href="{{ route('export.stock.csv') }}"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

        <a href="{{ route('export.stock.pdf') }}"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
    </div>


    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Group</th>
                    <th class="px-4 py-3 text-left">Item</th>
                    <th class="px-4 py-3 text-right">Total In</th>
                    <th class="px-4 py-3 text-right">Total Out</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3 text-left">Low Stock</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($rows as $r)
                    <tr class="{{ $r->is_low ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-2">{{ $r->group_code }}</td>
                        <td class="px-4 py-2"><a class="text-blue-700 hover:underline"
   href="{{ route('items.stock.show', $r->item_id) }}">
   {{ $r->item_code }} – {{ $r->item_name }}
</a></td>
                        <td class="px-4 py-2 text-right">{{ $r->total_in }}</td>
                        <td class="px-4 py-2 text-right">{{ $r->total_out }}</td>
                        <td class="px-4 py-2 text-right font-semibold">
                            {{ $r->balance }}
                        </td>
                        <td class="px-4 py-2">
                            @if($r->is_low)
                                <span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
                                    Low
                                </span>
                                <span> (Min: {{ $r->threshold_used }})</span>
                            @else
                                <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                    OK
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
