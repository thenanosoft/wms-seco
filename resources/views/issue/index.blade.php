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

    <form method="GET" action="{{ route('issues.index') }}" class="mb-4 rounded-xl border border-gray-200 bg-white p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-600">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-600">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-600">Issued To</label>
                <input type="text" name="issued_to" value="{{ request('issued_to') }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Name">
            </div>
            <div>
                <label class="block text-xs text-gray-600">Reference</label>
                <input type="text" name="reference" value="{{ request('reference') }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Ref">
            </div>
            <div class="flex gap-2">
                <button class="mt-5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Filter</button>
                <a href="{{ route('issues.index') }}" class="mt-5 rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    <div class="flex flex-wrap gap-2 mb-4">
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
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Issued To</th>
                        <th class="px-4 py-3 text-left font-semibold">Ref</th>
                        <th class="px-4 py-3 text-left font-semibold">Created By</th>
                        <th class="px-4 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                @if($issues->count())
                    @foreach($issues as $it)
                        <tbody x-data="{ openQuick: false }" class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-left">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('issues.show', $it) }}" class="text-indigo-600 hover:underline">#{{ $it->id }}</a>
                                        <button type="button"
                                                class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2 py-1 text-xs hover:bg-gray-50"
                                                @click="openQuick = !openQuick">
                                            Quick View
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 transition-transform" :class="openQuick ? 'rotate-180' : ''">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                        {{ $it->issue_date->format('Y-m-d') }}
                                </td>
                                <td class="px-4 py-3">{{ $it->issued_to ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $it->reference_no ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $it->creator?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('issues.edit', $it) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('issues.destroy', $it) }}" method="POST" onsubmit="return confirm('Delete this issue? If it has returns, related return records will also be deleted.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-700 hover:bg-red-50" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M6 6l1 16h10l1-16" />
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openQuick" x-cloak>
                                <td colspan="6" class="bg-gray-50 px-4 py-3">
                                    <div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
                                        <div class="px-3 py-2 border-b text-xs font-semibold text-gray-600">
                                            Quick View - Issue #{{ $it->id }} Items
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-xs">
                                                <thead class="bg-gray-50 text-gray-600">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left font-semibold">Item</th>
                                                        <th class="px-3 py-2 text-right font-semibold">Qty</th>
                                                        <th class="px-3 py-2 text-right font-semibold">Unit Price</th>
                                                        <th class="px-3 py-2 text-right font-semibold">Line Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @forelse($it->lines as $line)
                                                        <tr>
                                                            <td class="px-3 py-2">{{ $line->item?->item_code }} - {{ $line->item?->name }}</td>
                                                            <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format((float)$line->quantity, 8, '.', ''), '0'), '.') }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                @if($line->issue_price === null)
                                                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800">PENDING</span>
                                                                @else
                                                                    {{ number_format((float)$line->issue_price, 4) }}
                                                                @endif
                                                            </td>
                                                            <td class="px-3 py-2 text-right font-medium">{{ number_format((float)$line->line_total, 4) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="px-3 py-3 text-center text-gray-500">No items found.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                @else
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-600" colspan="6">
                                No issues yet.
                            </td>
                        </tr>
                    </tbody>
                @endif
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $issues->links() }}
    </div>
</div>
@endsection
