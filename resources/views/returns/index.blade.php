@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-semibold">Returns</h1>
        <a href="{{ route('returns.create') }}"
           class="rounded-lg bg-gray-900 px-4 py-2 text-white text-sm">
           New Return
        </a>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
    <form method="GET" action="{{ route('returns.index') }}" class="flex flex-wrap items-end gap-2 w-full">
        <div>
            <label class="block text-xs text-gray-600">Return Type</label>
            <select name="type" class="rounded-lg border-gray-200 text-sm">
                <option value="" {{ ($type ?? '') === '' ? 'selected' : '' }}>All</option>
                <option value="IN" {{ ($type ?? '') === 'IN' ? 'selected' : '' }}>Inward (Return In)</option>
                <option value="OUT" {{ ($type ?? '') === 'OUT' ? 'selected' : '' }}>Outward (Return Out)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-600">From</label>
            <input type="date" name="from" value="{{ $from ?? '' }}" class="rounded-lg border-gray-200 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-600">To</label>
            <input type="date" name="to" value="{{ $to ?? '' }}" class="rounded-lg border-gray-200 text-sm">
        </div>
        <div class="pt-4">
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-white text-sm">Filter</button>
            <a href="{{ route('returns.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <a href="{{ route('print.returns', request()->query()) }}" target="_blank"
       class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

    <a href="{{ route('export.returns.csv', request()->query()) }}"
       class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

    <a href="{{ route('export.returns.pdf', request()->query()) }}"
       class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
</div>


    <table class="w-full bg-white border rounded-xl">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">Date</th>
                <th class="p-3 text-left">Type</th>
                <th class="p-3 text-left">Party</th>
                <th class="p-3 text-left">Ref</th>
                <th class="p-3 text-left">User</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returns as $r)
            <tr class="border-t">
                <td class="p-3">{{ $r->return_date->format('Y-m-d') }}</td>
                <td class="p-3 font-semibold">{{ $r->type === 'IN' ? 'Inward' : 'Outward' }}</td>
                <td class="p-3">{{ $r->party }}</td>
                <td class="p-3">{{ $r->reference_no }}</td>
                <td class="p-3">{{ $r->creator->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $returns->links() }}
</div>
@endsection
