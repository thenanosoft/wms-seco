@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Settings</h1>
        <p class="text-sm text-gray-600">Low stock alerts and backup schedule.</p>
    </div>

    @if(session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
        @csrf

        <div class="rounded-xl border bg-white p-4 sm:p-6">
            <h2 class="text-lg font-semibold mb-4">Low Stock</h2>

            <label class="text-sm font-medium">Default Low Stock Threshold</label>
            <input type="number" step="0.001" min="0"
                   name="default_low_stock_threshold"
                   value="{{ old('default_low_stock_threshold', $defaultLow) }}"
                   class="mt-1 w-64 rounded-lg border-gray-200">
            <div class="text-xs text-gray-600 mt-1">Used when item specific threshold is empty.</div>
        </div>

        <div class="rounded-xl border bg-white p-4 sm:p-6">
            <h2 class="text-lg font-semibold mb-4">Backup Schedule</h2>

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="backup_enabled" value="1"
                       {{ old('backup_enabled', optional($backup)->enabled) ? 'checked' : '' }}>
                <span class="text-sm font-medium">Enable Auto Backup</span>
            </label>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div>
                    <label class="text-sm font-medium">Frequency</label>
                    <select name="backup_frequency" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="daily" {{ old('backup_frequency', optional($backup)->frequency) === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ old('backup_frequency', optional($backup)->frequency) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Weekly Day (1=Mon, 7=Sun)</label>
                    <input type="number" min="1" max="7"
                           name="backup_weekly_day"
                           value="{{ old('backup_weekly_day', optional($backup)->weekly_day ?? 1) }}"
                           class="mt-1 w-full rounded-lg border-gray-200">
                </div>

                <div>
                    <label class="text-sm font-medium">Time (HH:MM)</label>
                    <input type="text"
                           name="backup_time_hm"
                           value="{{ old('backup_time_hm', optional($backup)->time_hm ?? '02:00') }}"
                           class="mt-1 w-full rounded-lg border-gray-200"
                           placeholder="02:00">
                </div>

                <div>
                    <label class="text-sm font-medium">Backup Folder Path</label>
                    <input type="text"
                           name="backup_path"
                           value="{{ old('backup_path', optional($backup)->backup_path) }}"
                           class="mt-1 w-full rounded-lg border-gray-200"
                           placeholder="/path/to/backups">
                </div>

            </div>

            <div class="text-xs text-gray-600 mt-3">
                Tip: On Mac you can use: {{ storage_path('app/backups') }}
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                Save Settings
            </button>
        </div>
    </form>

    <!-- Full Export (CSV) -->
    <div class="mt-8 rounded-xl border bg-white p-4 sm:p-6">
        <div class="mb-4">
            <h2 class="text-lg font-semibold">Full Export (CSV)</h2>
            <p class="text-sm text-gray-600">Export complete history including supplier and issued-to details. If you do not select any filter, the system will export everything.</p>
        </div>

        <form method="GET" action="{{ route('export.ledger.csv') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm font-medium">Quick Date</label>
                    <select name="date_preset" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">Custom</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">From</label>
                    <input type="date" name="from" class="mt-1 w-full rounded-lg border-gray-200" value="{{ request('from') }}">
                </div>

                <div>
                    <label class="text-sm font-medium">To</label>
                    <input type="date" name="to" class="mt-1 w-full rounded-lg border-gray-200" value="{{ request('to') }}">
                </div>

                <div>
                    <label class="text-sm font-medium">Type</label>
                    <select name="type" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        <option value="purchase">Purchases (In)</option>
                        <option value="issue">Issues (Out)</option>
                        <option value="issue_return">Issue Returns (In)</option>
                        <option value="purchase_return">Purchase Returns (Out)</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Supplier</label>
                    <select name="supplier" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        @foreach(($suppliers ?? []) as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Issued To</label>
                    <select name="issued_to" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        @foreach(($issuedTos ?? []) as $it)
                            <option value="{{ $it }}">{{ $it }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Group</label>
                    <select name="group_id" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        @foreach(($groups ?? []) as $g)
                            <option value="{{ $g->id }}">{{ $g->group_code }}{{ $g->group_name ? ' - '.$g->group_name : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Item</label>
                    <select name="item_id" class="mt-1 w-full rounded-lg border-gray-200">
                        <option value="">All</option>
                        @foreach(($items ?? []) as $i)
                            <option value="{{ $i->id }}">{{ $i->item_code }} - {{ $i->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('settings.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Download CSV</button>
            </div>
        </form>
    </div>

</div>
@endsection
