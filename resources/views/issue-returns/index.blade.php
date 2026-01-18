@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Issue Returns (Inward)</h1>
            <p class="text-sm text-gray-600">Detailed history of returned items from issues.</p>
        </div>

        <a href="{{ route('issue-returns.create') }}"
           class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
            New Issue Return
        </a>
    </div>

    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Date</th>
                    <th class="px-4 py-3 text-left font-semibold">Issue ID</th>
                    <th class="px-4 py-3 text-left font-semibold">Notes</th>
                    <th class="px-4 py-3 text-left font-semibold">By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($returns as $r)
                    <tr>
                        <td class="px-4 py-2">{{ $r->return_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-2">{{ $r->issue_id }}</td>
                        <td class="px-4 py-2">{{ $r->notes ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $r->creator?->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $returns->links() }}
</div>
@endsection
