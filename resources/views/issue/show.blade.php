@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Issue Details</h1>
            <p class="text-sm text-gray-600">Issue #{{ $issue->id }} · {{ optional($issue->issue_date)->format('Y-m-d') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('issues.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Back</a>

            @if(auth()->user()?->role === 'admin')
                <a href="{{ route('issues.edit', $issue) }}"
                   class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50"
                   title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                    </svg>
                </a>

                <form action="{{ route('issues.destroy', $issue) }}" method="POST"
                      onsubmit="return confirm('Delete this issue? Any returns linked to it will also be deleted.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-red-200 text-red-700 hover:bg-red-50"
                            title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                            <path d="M3 6h18" />
                            <path d="M8 6V4h8v2" />
                            <path d="M6 6l1 16h10l1-16" />
                            <path d="M10 11v6" />
                            <path d="M14 11v6" />
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('print.issues', request()->query()) }}" target="_blank"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Print</a>

        <a href="{{ route('export.issues.csv', request()->query()) }}"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Export CSV</a>

        <a href="{{ route('export.issues.pdf', request()->query()) }}"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">Export PDF</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500">Issued To</span><div class="font-medium">{{ $issue->issued_to ?: '-' }}</div></div>
            <div><span class="text-gray-500">Reference</span><div class="font-medium">{{ $issue->reference_no ?: '-' }}</div></div>
            <div><span class="text-gray-500">Created By</span><div class="font-medium">{{ optional($issue->creator)->name ?: '-' }}</div></div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Items</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-2 text-left">Item</th>
                    <th class="px-4 py-2 text-right">Qty</th>
                    <th class="px-4 py-2 text-right">Returned</th>
                    <th class="px-4 py-2 text-right">Remaining</th>
                    <th class="px-4 py-2 text-right">Unit Price</th>
                    <th class="px-4 py-2 text-left">Specification</th>
                    <th class="px-4 py-2 text-right">Line Total</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse(($lines ?? collect()) as $line)
                    <tr>
                        <td class="px-4 py-2">
                            <a href="{{ route('items.stock.show', $line->item_id) }}" class="text-indigo-600 hover:underline">
                                {{ $line->item_code }} - {{ $line->item_name }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-right">{{ (int)$line->quantity }}</td>
                        <td class="px-4 py-2 text-right">{{ (int)$line->returned_qty }}</td>
                        <td class="px-4 py-2 text-right">{{ (int)$line->remaining_qty }}</td>
                        <td class="px-4 py-2 text-right">
                            @if($line->issue_price === null)
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Pending</span>
                            @else
                                {{ number_format((float)$line->issue_price, 4) }}
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $line->specification ?: '' }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float)$line->net_line_total, 4) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">No items found for this issue.</td>
                    </tr>
                @endforelse

                <tr class="bg-gray-50 font-semibold">
                    <td class="px-4 py-2 text-right">Totals</td>
                    <td class="px-4 py-2 text-right">{{ number_format((float)($totals->total_qty ?? 0), 4) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format((float)($totals->total_returned ?? 0), 4) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format((float)($totals->total_remaining ?? 0), 4) }}</td>
                    <td class="px-4 py-2"></td>
                    <td class="px-4 py-2"></td>
                    <td class="px-4 py-2 text-right">{{ number_format((float)($totals->total_net_amount ?? 0), 4) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
