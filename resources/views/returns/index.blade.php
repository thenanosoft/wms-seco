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
