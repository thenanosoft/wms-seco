@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Balance Report</h1>
        <p class="mt-1 text-sm text-gray-600">Item-wise balance report (purchased / issued / returns) with date filters and exports.</p>
    </div>

    <form method="GET" action="{{ route('reports.balance.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label class="text-sm font-medium">Range</label>
                <select name="range" class="mt-1 w-full rounded-lg border-gray-200">
                    @php($range = request('range','today'))
                    <option value="today" @selected($range==='today')>Today</option>
                    <option value="weekly" @selected($range==='weekly')>Weekly</option>
                    <option value="monthly" @selected($range==='monthly')>Monthly</option>
                    <option value="yearly" @selected($range==='yearly')>Yearly</option>
                    <option value="custom" @selected($range==='custom')>Custom</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">From</label>
                <input type="date" name="from" value="{{ request('from', $from) }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>

            <div>
                <label class="text-sm font-medium">To</label>
                <input type="date" name="to" value="{{ request('to', $to) }}" class="mt-1 w-full rounded-lg border-gray-200">
            </div>

            <div>
                <label class="text-sm font-medium">Group</label>
                <select name="group_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected(request('group_id')==$g->id)>{{ $g->group_code }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Item</label>
                <select name="item_id" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">All</option>
                    @foreach($items as $it)
                        <option value="{{ $it->id }}" @selected(request('item_id')==$it->id)>{{ $it->item_code }} - {{ $it->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="item code / name / group">
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs text-gray-500">
                Tip: Use Range = Custom with From/To for exact date range.
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('reports.balance.csv', request()->query()) }}">Export CSV</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('reports.balance.xls', request()->query()) }}">Export Excel</a>
                <a class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('reports.balance.pdf', request()->query()) }}">Export PDF</a>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Apply</button>
            </div>
        </div>
    </form>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold">Group</th>
                        <th class="px-3 py-2 text-left font-semibold">Item</th>
                        <th class="px-3 py-2 text-right font-semibold">Purchased Qty</th>
                        <th class="px-3 py-2 text-right font-semibold">Purchased Amount</th>
                        <th class="px-3 py-2 text-right font-semibold">Issued Qty</th>
                        <th class="px-3 py-2 text-right font-semibold">Issued Amount</th>
                        <th class="px-3 py-2 text-right font-semibold">Issue Return Qty</th>
                        <th class="px-3 py-2 text-right font-semibold">Issue Return Amount</th>
                        <th class="px-3 py-2 text-right font-semibold">Purchase Return Qty</th>
                        <th class="px-3 py-2 text-right font-semibold">Purchase Return Amount</th>
                        <th class="px-3 py-2 text-right font-semibold">Net Balance</th>
                        <th class="px-3 py-2 text-right font-semibold">Net Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php($tP=0)
                    @php($tPA=0)
                    @php($tI=0)
                    @php($tIA=0)
                    @php($tIR=0)
                    @php($tIRA=0)
                    @php($tPR=0)
                    @php($tPRA=0)
                    @php($tN=0)
                    @php($tNA=0)
                    @forelse($rows as $r)
                        @php($tP += (int)$r->purchased_qty)
                        @php($tPA += (float)($r->purchased_amount ?? 0))
                        @php($tI += (int)$r->issued_qty)
                        @php($tIA += (float)($r->issued_amount ?? 0))
                        @php($tIR += (int)$r->issue_return_qty)
                        @php($tIRA += (float)($r->issue_return_amount ?? 0))
                        @php($tPR += (int)$r->purchase_return_qty)
                        @php($tPRA += (float)($r->purchase_return_amount ?? 0))
                        @php($tN += (int)$r->net_balance)
                        @php($tNA += (float)($r->net_amount ?? 0))
                        <tr>
                            <td class="px-3 py-2">{{ $r->group_code ?? '' }}</td>
                            <td class="px-3 py-2">{{ $r->item_code }} - {{ $r->item_name }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((int)$r->purchased_qty,0) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)($r->purchased_amount ?? 0),4) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((int)$r->issued_qty,0) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)($r->issued_amount ?? 0),4) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((int)$r->issue_return_qty,0) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)($r->issue_return_amount ?? 0),4) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((int)$r->purchase_return_qty,0) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)($r->purchase_return_amount ?? 0),4) }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ number_format((int)$r->net_balance,0) }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ number_format((float)($r->net_amount ?? 0), 4) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-8 text-center text-gray-500">No records found for this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-semibold">
                        <td class="px-3 py-2 text-right" colspan="2">Totals</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tP,0) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tPA,4) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tI,0) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tIA,4) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tIR,0) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tIRA,4) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tPR,0) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tPRA,4) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tN,0) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($tNA,4) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
