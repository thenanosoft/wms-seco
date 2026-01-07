@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Issues (Outward)</h1>
            <p class="mt-1 text-sm text-gray-600">Issue items from store with stock validation.</p>
        </div>

        <a href="{{ route('issues.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
            New Issue
        </a>
    </div>

    <div class="flex flex-wrap gap-2">
    <a href="{{ route('print.issues', request()->query()) }}" target="_blank"
       class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

    <a href="{{ route('export.issues.csv', request()->query()) }}"
       class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

    <a href="{{ route('export.issues.pdf', request()->query()) }}"
       class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
</div>


    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Issued To</th>
                        <th class="px-4 py-3 text-left font-semibold">Ref</th>
                        <th class="px-4 py-3 text-left font-semibold">Created By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($issues as $it)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $it->issue_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $it->issued_to ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $it->reference_no ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $it->creator?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-600" colspan="4">
                                No issues yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $issues->links() }}
    </div>
</div>
@endsection
