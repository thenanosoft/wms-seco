@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Dashboard</h1>
            <p class="text-sm text-gray-600">Daily overview for store operations.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('purchases.create') }}"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                New Purchase
            </a>

            <a href="{{ route('issues.create') }}"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                New Issue
            </a>

            <a href="{{ route('returns.index') }}"
               class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                Returns
            </a>

            @if(auth()->user()?->role === 'admin')
                <a href="{{ route('stock.index') }}"
                   class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Stock
                </a>
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Purchases</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($purchase->total, 0) }}</div>
            <div class="text-xs text-gray-600 mt-1">Qty: {{ $purchase->qty }}</div>
            <div class="mt-3">
                <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-gray-900 underline">
                    View Purchases
                </a>
            </div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Issues</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($issue->total, 0) }}</div>
            <div class="text-xs text-gray-600 mt-1">Qty: {{ $issue->qty }}</div>
            <div class="mt-3">
                <a href="{{ route('issues.index') }}" class="text-sm font-medium text-gray-900 underline">
                    View Issues
                </a>
            </div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Return Inward</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($returnIn->qty, 0) }}</div>
            <div class="text-xs text-gray-600 mt-1">Value: {{ number_format($returnIn->total, 0) }}</div>
            <div class="mt-3">
                <a href="{{ route('returns.issue.index') }}" class="text-sm font-medium text-gray-900 underline">View Issue Returns</a>
            </div>
        </div>

        <div class="rounded-xl border bg-white p-4">
            <div class="text-sm text-gray-600">Today Return Outward</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($returnOut->qty, 0) }}</div>
            <div class="text-xs text-gray-600 mt-1">Value: {{ number_format($returnOut->total, 0) }}</div>
            <div class="mt-3">
                <a href="{{ route('returns.purchase.index') }}" class="text-sm font-medium text-gray-900 underline">View Purchase Returns</a>
            </div>
        </div>

    </div>

    {{-- Admin Quick Actions --}}
    @if(auth()->user()?->role === 'admin')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="rounded-xl border bg-white p-5">
                <div class="text-sm font-semibold">Admin Quick Actions</div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('groups.index') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Groups</a>
                    <a href="{{ route('items.index') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Items</a>
                    <a href="{{ route('purchases.items.index') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Purchase Items List</a>
                    <a href="{{ route('backup.index') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Backup</a>
                    <a href="{{ route('settings.index') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Settings</a>
                </div>
            </div>

            <div class="rounded-xl border bg-white p-5">
                <div class="text-sm font-semibold">Exports</div>
                <div class="mt-1 text-xs text-gray-600">Download CSV/PDF quickly.</div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="block text-xs text-gray-600">Data</label>
                        <select id="exportData" class="mt-1 w-full rounded-lg border-gray-200">
                            <option value="stock">Stock</option>
                            <option value="purchases">Purchases</option>
                            <option value="issues">Issues</option>
                            <option value="issue_returns">Issue Returns</option>
                            <option value="purchase_returns">Purchase Returns</option>
                            <option value="ledger">Full History</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">Format</label>
                        <select id="exportFormat" class="mt-1 w-full rounded-lg border-gray-200">
                            <option value="pdf" selected>PDF</option>
                            <option value="csv">CSV</option>
                            <option value="print">Print</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <a id="exportGo" href="{{ route('export.stock.pdf') }}" class="w-full text-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Export</a>
                    </div>
                </div>

                <script>
                    (function () {
                        const routes = {
                            stock: { csv: "{{ route('export.stock.csv') }}", pdf: "{{ route('export.stock.pdf') }}" },
                            purchases: { csv: "{{ route('export.purchases.csv') }}", pdf: "{{ route('export.purchases.pdf') }}" },
                            issues: { csv: "{{ route('export.issues.csv') }}", pdf: "{{ route('export.issues.pdf') }}" },
                            issue_returns: { csv: "{{ route('export.issue_returns.csv') }}", pdf: "{{ route('export.issue_returns.pdf') }}" },
                            purchase_returns: { csv: "{{ route('export.purchase_returns.csv') }}", pdf: "{{ route('export.purchase_returns.pdf') }}" },
                            ledger: { csv: "{{ route('export.ledger.csv') }}", pdf: null },
                        };

                        const dataSel = document.getElementById('exportData');
                        const fmtSel = document.getElementById('exportFormat');
                        const go = document.getElementById('exportGo');
                        if (!dataSel || !fmtSel || !go) return;

                        function update() {
                            const dataKey = dataSel.value;
                            const fmt = fmtSel.value;
                            const map = routes[dataKey] || {};
                            let href = null;
                            if (fmt === 'csv') href = map.csv || null;
                            if (fmt === 'pdf') href = map.pdf || null;
                            if (fmt === 'print') href = map.pdf || map.csv || null;
                            if (!href) {
                                go.classList.add('opacity-50', 'pointer-events-none');
                                go.textContent = 'Not Available';
                                go.removeAttribute('href');
                                return;
                            }
                            go.classList.remove('opacity-50', 'pointer-events-none');
                            go.textContent = (fmt === 'print') ? 'Open to Print' : 'Export';
                            go.setAttribute('href', href);
                            go.setAttribute('target', (fmt === 'print') ? '_blank' : '_self');
                        }

                        dataSel.addEventListener('change', update);
                        fmtSel.addEventListener('change', update);
                        update();
                    })();
                </script>
            </div>

            <div class="rounded-xl border bg-white p-5">
                <div class="text-sm font-semibold">Totals</div>
                <div class="mt-3">
                    <div class="text-sm text-gray-600">Total Items</div>
                    <div class="mt-1 text-2xl font-semibold">{{ $itemsCount }}</div>
                    <div class="mt-4 grid grid-cols-1 gap-2 text-sm">
                        <div class="flex items-center justify-between"><span class="text-gray-600">Total In Value</span><span class="font-semibold">{{ number_format($inValue, 0) }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-gray-600">Total Out Value</span><span class="font-semibold">{{ number_format($outValue, 0) }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-gray-600">Balance Value</span><span class="font-semibold">{{ number_format($balanceValue, 0) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-xl border bg-white p-5">
            <div class="text-sm font-semibold">Quick Actions</div>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('purchases.create') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">New Purchase</a>
                <a href="{{ route('issues.create') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">New Issue</a>
                <a href="{{ route('returns.index') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">Returns</a>
            </div>
        </div>
    @endif

</div>
@endsection
