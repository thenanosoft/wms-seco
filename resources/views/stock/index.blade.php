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
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($rows as $r)
                    <tr>
                        <td class="px-4 py-2">{{ $r->group_code }}</td>
                        <td class="px-4 py-2">{{ $r->item_code }} – {{ $r->item_name }}</td>
                        <td class="px-4 py-2 text-right">{{ $r->total_in }}</td>
                        <td class="px-4 py-2 text-right">{{ $r->total_out }}</td>
                        <td class="px-4 py-2 text-right font-semibold">
                            {{ $r->balance }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
