@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Issue Returns</h1>
            <p class="text-sm text-gray-600">Return inward strictly against issued lines (no stock tampering).</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('issue-returns.create') }}"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                New Issue Return
            </a>
        </div>
    </div>

    <form method="GET" class="rounded-xl border bg-white p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="text-xs font-semibold text-gray-600">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div class="lg:col-span-2">
                <label class="text-xs font-semibold text-gray-600">Issue</label>
                <select name="issue_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    @foreach($issues as $i)
                        <option value="{{ $i->id }}" @selected(request('issue_id') == $i->id)>
                            #{{ $i->id }} | {{ $i->issue_date->format('Y-m-d') }} | {{ $i->issued_to ?? 'N/A' }} {{ $i->reference_no ? ' | Ref: '.$i->reference_no : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-end gap-2">
            <a href="{{ route('issue-returns.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Reset</a>
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Apply</button>
        </div>
    </form>

    <div class="rounded-xl border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Issue</th>
                    <th class="px-4 py-3 text-left">Received From</th>
                    <th class="px-4 py-3 text-left">Ref</th>
                    <th class="px-4 py-3 text-left">Created By</th>
                    <th class="px-4 py-3 text-right">Lines</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rows as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $r->return_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            #{{ $r->issue_id }}
                        </td>
                        <td class="px-4 py-2">{{ $r->received_from ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $r->reference_no ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $r->creator?->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-right">
                            {{ $r->lines()->count() }}
                        </td>
                    </tr>
                @endforeach

                @if($rows->count() === 0)
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-600">No records found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div>
        {{ $rows->links() }}
    </div>
</div>
@endsection
