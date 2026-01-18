@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto"
     x-data="issueReturnForm()"
     x-init="init()">

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Issue Return (Inward)</h1>
        <p class="mt-1 text-sm text-gray-600">
            Only issued items can be returned. Price is locked from the issue record.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold mb-1">Please fix the errors:</div>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('issue-returns.store') }}" class="space-y-4">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium">Return Date</label>
                    <input type="date" name="return_date"
                           class="mt-1 w-full rounded-lg border-gray-200"
                           value="{{ old('return_date', now()->format('Y-m-d')) }}" required>
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm font-medium">Select Issue</label>
                    <select name="issue_id"
                            class="mt-1 w-full rounded-lg border-gray-200"
                            x-model="issueId"
                            @change="loadIssueLines()"
                            required>
                        <option value="">Select issue</option>
                        @foreach($issues as $iss)
                            <option value="{{ $iss->id }}">
                                {{ $iss->issue_date }} | {{ $iss->id }} | {{ $iss->issued_to ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="text-xs text-gray-600 mt-1">
                        Tip: Choose the same issue from which item was given.
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <label class="text-sm font-medium">Notes</label>
                <input type="text" name="notes"
                       class="mt-1 w-full rounded-lg border-gray-200"
                       placeholder="Optional">
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold">Issued Items (Returnable)</div>
                    <div class="text-xs text-gray-600">Only remaining qty can be returned.</div>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Group</th>
                            <th class="px-3 py-2 text-left font-semibold">Item</th>
                            <th class="px-3 py-2 text-left font-semibold">Spec</th>
                            <th class="px-3 py-2 text-right font-semibold">Issued</th>
                            <th class="px-3 py-2 text-right font-semibold">Returned</th>
                            <th class="px-3 py-2 text-right font-semibold">Remaining</th>
                            <th class="px-3 py-2 text-right font-semibold">Price</th>
                            <th class="px-3 py-2 text-right font-semibold">Return Qty</th>
                            <th class="px-3 py-2 text-right font-semibold">Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(row, idx) in rows" :key="row.issue_line_id">
                            <tr x-show="row.remaining_qty > 0">
                                <td class="px-3 py-2" x-text="row.group_code"></td>
                                <td class="px-3 py-2">
                                    <div class="font-medium" x-text="row.item_code"></div>
                                    <div class="text-xs text-gray-600" x-text="row.item_name"></div>
                                </td>
                                <td class="px-3 py-2" x-text="row.specification ?? '-'"></td>
                                <td class="px-3 py-2 text-right" x-text="fmt3(row.issued_qty)"></td>
                                <td class="px-3 py-2 text-right" x-text="fmt3(row.returned_qty)"></td>
                                <td class="px-3 py-2 text-right font-semibold" x-text="fmt3(row.remaining_qty)"></td>
                                <td class="px-3 py-2 text-right" x-text="fmt2(row.issue_price)"></td>

                                <td class="px-3 py-2 text-right">
                                    <input type="hidden" :name="`lines[${idx}][issue_line_id]`" :value="row.issue_line_id">
                                    <input type="number"
                                           step="0.001"
                                           min="0"
                                           :max="row.remaining_qty"
                                           class="w-28 rounded-lg border-gray-200 text-right"
                                           :name="`lines[${idx}][quantity]`"
                                           x-model.number="row.return_qty"
                                           @input="clampQty(row); recalc(row)">
                                </td>

                                <td class="px-3 py-2 text-right font-semibold" x-text="fmt2(row.line_total)"></td>
                            </tr>
                        </template>

                        <tr x-show="rows.length === 0">
                            <td colspan="9" class="px-3 py-4 text-sm text-gray-600">
                                Select an issue to load items.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end text-base font-semibold">
                Grand Total: <span class="ml-2" x-text="fmt2(grandTotal)"></span>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('issues.index') }}"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800"
                    :disabled="!canSubmit">
                Save Return
            </button>
        </div>
    </form>
</div>

<script>
function issueReturnForm() {
    return {
        issueId: '',
        rows: [],
        grandTotal: 0,
        canSubmit: false,

        init() {
            this.recalcTotals();
        },

        async loadIssueLines() {
            this.rows = [];
            this.grandTotal = 0;
            this.canSubmit = false;

            if (!this.issueId) return;

            const res = await fetch("{{ url('/issue-returns/issue') }}/" + this.issueId);
            const data = await res.json();

            this.rows = (data.lines || []).map(l => ({
                ...l,
                return_qty: 0,
                line_total: 0,
            }));

            this.recalcTotals();
        },

        clampQty(row) {
            if (row.return_qty > row.remaining_qty) row.return_qty = row.remaining_qty;
            if (row.return_qty < 0) row.return_qty = 0;
        },

        recalc(row) {
            row.line_total = Math.round((Number(row.return_qty || 0) * Number(row.issue_price || 0)) * 100) / 100;
            this.recalcTotals();
        },

        recalcTotals() {
            this.grandTotal = this.rows.reduce((s, r) => s + Number(r.line_total || 0), 0);
            this.grandTotal = Math.round(this.grandTotal * 100) / 100;

            // submit only if at least one return qty > 0
            this.canSubmit = this.rows.some(r => Number(r.return_qty || 0) > 0);
        },

        fmt2(v) { return Number(v || 0).toFixed(2); },
        fmt3(v) { return Number(v || 0).toFixed(3); },
    }
}
</script>
@endsection
