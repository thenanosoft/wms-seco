@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Items</h1>
            <p class="mt-1 text-sm text-gray-600">Items are linked to groups. Example: Steel group has many items.</p>
        </div>
        <a href="{{ route('items.create') }}"
           class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
            New Item
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 mb-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-1">
                <select name="group_id" class="w-full rounded-lg border-gray-200">
                    <option value="">All Groups</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected((string)$groupId === (string)$g->id)>
                            {{ $g->group_code }}{{ $g->group_name ? ' - '.$g->group_name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <input name="q" value="{{ $q }}" class="w-full rounded-lg border-gray-200"
                       placeholder="Search by item code or name">
            </div>
            <div class="sm:col-span-1 flex gap-3">
                <button class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Search
                </button>
                <a href="{{ route('items.index') }}" class="w-full text-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Group</th>
                        <th class="px-4 py-3 text-left font-semibold">Item Code</th>
                        <th class="px-4 py-3 text-left font-semibold">Item Name</th>
                        <th class="px-4 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $it)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-semibold">{{ $it->group->group_code }}</div>
                                <div class="text-xs text-gray-600">{{ $it->group->group_name ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ $it->item_code }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $it->name }}</div>
                                @if($it->default_spec)
                                    <div class="text-xs text-gray-600">{{ $it->default_spec }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('items.edit', $it) }}"
                                   class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                                    Edit
                                </a>

                                <form class="inline" method="POST" action="{{ route('items.destroy', $it) }}"
                                      onsubmit="return confirm('Delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-600">No items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>
@endsection
