@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Groups</h1>
            <p class="mt-1 text-sm text-gray-600">Manage group codes (example: 51 Steel).</p>
        </div>
        <a href="{{ route('groups.create') }}"
           class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
            New Group
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input name="q" value="{{ $q }}" class="w-full rounded-lg border-gray-200"
                   placeholder="Search by group code or name">
            <button class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                Search
            </button>
            <a href="{{ route('groups.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                Reset
            </a>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Group Code</th>
                        <th class="px-4 py-3 text-left font-semibold">Name</th>
                        <th class="px-4 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($groups as $g)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold">{{ $g->group_code }}</td>
                            <td class="px-4 py-3">{{ $g->group_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('groups.edit', $g) }}"
                                   class="rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                                    Edit
                                </a>

                                <form class="inline" method="POST" action="{{ route('groups.destroy', $g) }}"
                                      onsubmit="return confirm('Delete this group? This will also delete its items.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-600">No groups found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $groups->links() }}
    </div>
</div>
@endsection
