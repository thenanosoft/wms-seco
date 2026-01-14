@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Edit Item</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('items.update', $item) }}" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="text-sm font-medium">Group</label>
            <select name="group_id" class="mt-1 w-full rounded-lg border-gray-200" required>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" @selected(old('group_id', $item->group_id) == $g->id)>
                        {{ $g->group_code }}{{ $g->group_name ? ' - '.$g->group_name : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Item Code</label>
                <input name="item_code" value="{{ old('item_code', $item->item_code) }}" class="mt-1 w-full rounded-lg border-gray-200" required>
            </div>

            <div>
                <label class="text-sm font-medium">Item Name</label>
                <input name="name" value="{{ old('name', $item->name) }}" class="mt-1 w-full rounded-lg border-gray-200" required>
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Default Specification</label>
            <textarea name="default_spec" class="mt-1 w-full rounded-lg border-gray-200" rows="3" placeholder="Optional">{{ old('default_spec', $item->default_spec) }}</textarea>
        </div>

        <div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Low Stock Threshold</label>
    <input
        type="number"
        step="0.001"
        name="low_stock_threshold"
        value="{{ old('low_stock_threshold', $item->low_stock_threshold ?? '') }}"
        class="mt-1 block w-full border rounded p-2"
        placeholder="Example: 5.000"
    />
    @error('low_stock_threshold')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-gray-500 text-xs mt-1">
        Leave empty to use default threshold from settings.
    </p>
</div>


        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('items.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancel</a>
            <button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800">Update</button>
        </div>
    </form>
</div>
@endsection
