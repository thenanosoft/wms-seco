@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Issue Returns (Inward)</h1>
            <p class="mt-1 text-sm text-gray-600">History of items returned from issues.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('returns.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Returns Home</a>
            <a href="{{ route('returns.issue.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">New Return</a>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-4 mb-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Issue</label>
                <select name="issue_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    @foreach($issues as $iss)
                        <option value="{{ $iss->id }}" @selected(request('issue_id')==$iss->id)>{{ $iss->issue_date }} | {{ $iss->reference_no ?? 'No Ref' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Group</label>
                <select name="group_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected(request('group_id')==$g->id)>{{ $g->group_code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Item</label>
                <select name="item_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    @foreach($items as $it)
                        <option value="{{ $it->id }}" @selected(request('item_id')==$it->id)>{{ $it->item_code }} - {{ $it->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Filter</button>
                <a href="{{ route('returns.issue.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold">Return Date</th>
                    <th class="px-3 py-2 text-left font-semibold">Issue</th>
                    <th class="px-3 py-2 text-left font-semibold">Group</th>
                    <th class="px-3 py-2 text-left font-semibold">Item</th>
                    <th class="px-3 py-2 text-right font-semibold">Qty</th>
                    <th class="px-3 py-2 text-right font-semibold">Price</th>
                    <th class="px-3 py-2 text-right font-semibold">Total</th>
                    <th class="px-3 py-2 text-left font-semibold">By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rows as $r)
                    <tr>
                        <td class="px-3 py-2">{{ $r->return_date }}</td>
                        <td class="px-3 py-2">{{ $r->issue_date }} <div class="text-xs text-gray-500">{{ $r->reference_no }}</div></td>
                        <td class="px-3 py-2">{{ $r->group_code }}</td>
                        <td class="px-3 py-2">
                            <a class="font-semibold hover:underline" href="{{ route('items.stock.show', $r->item_id) }}">{{ $r->item_code }}</a>
                            <div class="text-xs text-gray-600">{{ $r->item_name }}</div>
                        </td>
                        <td class="px-3 py-2 text-right">{{ number_format((float)$r->quantity, 3) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format((float)$r->issue_price, 2) }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ number_format((float)$r->line_total, 2) }}</td>
                        <td class="px-3 py-2">{{ $r->created_by_name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
