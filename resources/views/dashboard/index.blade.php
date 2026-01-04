@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">
                Welcome, {{ $user->name }}. Use the sidebar to start Purchase, Issue, or Returns entries.
            </p>
        </div>

        <!-- Quick actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="text-sm font-semibold">Purchase (Inward)</div>
                <div class="mt-2 text-sm text-gray-600">Add new inward entry quickly.</div>
                <button class="mt-4 w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    New Purchase
                </button>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="text-sm font-semibold">Issue (Outward)</div>
                <div class="mt-2 text-sm text-gray-600">Issue items and reduce stock safely.</div>
                <button class="mt-4 w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    New Issue
                </button>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="text-sm font-semibold">Returns</div>
                <div class="mt-2 text-sm text-gray-600">Record returned material in/out.</div>
                <button class="mt-4 w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    New Return
                </button>
            </div>
        </div>

        @if($user->role === 'admin')
            <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
                <div class="text-sm font-semibold">Admin Panel</div>
                <div class="mt-2 text-sm text-gray-600">
                    You can manage items, reports, users, and backups.
                </div>
            </div>
        @else
            <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
                <div class="text-sm font-semibold">Helper Access</div>
                <div class="mt-2 text-sm text-gray-600">
                    You can add inward/outward/returns entries. Reports and master listings are restricted.
                </div>
            </div>
        @endif
    </div>
@endsection
