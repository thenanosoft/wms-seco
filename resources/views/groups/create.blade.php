@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">New Group</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('groups.store') }}" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6 space-y-4">
        @csrf

        <div>
            <label class="text-sm font-medium">Group Code</label>
            <input name="group_code" value="{{ old('group_code') }}" class="mt-1 w-full rounded-lg border-gray-200" required>
        </div>

        <div>
            <label class="text-sm font-medium">Group Name</label>
            <input name="group_name" value="{{ old('group_name') }}" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Optional">
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('groups.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancel</a>
            <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">Save</button>
        </div>
    </form>
</div>
@endsection
