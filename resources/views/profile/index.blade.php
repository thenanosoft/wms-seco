@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <h1 class="text-2xl font-semibold">Profile & Users</h1>

    @if(session('success'))
        <div class="rounded border bg-green-50 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- My Profile --}}
    <div class="rounded-xl border bg-white p-5">
        <h2 class="font-semibold mb-3">My Profile</h2>

        <form method="POST" action="{{ route('profile.update') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf

            <div>
                <label class="text-xs text-gray-600">Name</label>
                <input name="name" value="{{ auth()->user()->name }}" class="w-full rounded border-gray-200">
            </div>

            <div>
                <label class="text-xs text-gray-600">Email</label>
                <input name="email" value="{{ auth()->user()->email }}" class="w-full rounded border-gray-200">
            </div>

            <div>
                <label class="text-xs text-gray-600">New Password</label>
                <input type="password" name="password" class="w-full rounded border-gray-200">
            </div>

            <div class="flex items-end">
                <button class="rounded bg-gray-900 px-4 py-2 text-white text-sm">Update</button>
            </div>
        </form>
    </div>

    {{-- Users --}}
    <div class="rounded-xl border bg-white p-5">
        <h2 class="font-semibold mb-3">
            Users ({{ count($users) }}/{{ $maxUsers }})
        </h2>

        <table class="w-full text-sm mb-4">
            @foreach($users as $u)
<tr class="border-t">
    <td class="py-2">{{ $u->name }}</td>
    <td class="py-2">{{ $u->username }}</td>
    <td class="py-2">{{ $u->email }}</td>

    <td class="py-2">
        <div class="flex flex-wrap gap-2">

            {{-- EDIT BUTTON (toggles form) --}}
            <button type="button"
                class="rounded border border-gray-200 px-3 py-1.5 text-xs hover:bg-gray-50"
                onclick="document.getElementById('edit-user-{{ $u->id }}').classList.toggle('hidden')">
                Edit
            </button>

            {{-- DELETE --}}
            @if($u->id !== auth()->id())
            <form method="POST" action="{{ route('profile.users.delete', $u) }}"
                onsubmit="return confirm('Delete this user?')">
                @csrf
                <button class="rounded border border-red-200 px-3 py-1.5 text-xs text-red-700 hover:bg-red-50">
                    Delete
                </button>
            </form>
            @endif
        </div>

        {{-- EDIT FORM (hidden by default) --}}
        <div id="edit-user-{{ $u->id }}" class="hidden mt-3 rounded-lg border bg-gray-50 p-3">
            <form method="POST" action="{{ route('profile.users.update', $u) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                @csrf

                <input name="name" value="{{ $u->name }}" class="rounded border-gray-200 text-sm" placeholder="Name" required>

                <input name="username" value="{{ $u->username }}" class="rounded border-gray-200 text-sm" placeholder="Username" required>

                <input name="email" value="{{ $u->email }}" class="rounded border-gray-200 text-sm" placeholder="Email" required>

                <input name="password" class="rounded border-gray-200 text-sm" placeholder="New Password (optional)">

                <div class="sm:col-span-4 flex gap-2">
                    <button class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">
                        Save
                    </button>
                    <button type="button"
                        class="rounded border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50"
                        onclick="document.getElementById('edit-user-{{ $u->id }}').classList.add('hidden')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

    </td>
</tr>
@endforeach


        </table>

        @if(count($users) < $maxUsers)
            <form method="POST" action="{{ route('profile.users.store') }}"
      class="grid grid-cols-1 sm:grid-cols-4 gap-2">
    @csrf
    <input name="name" placeholder="Name" class="rounded border-gray-200">
    <input name="username" placeholder="Username (optional)" class="rounded border-gray-200">
    <input name="email" placeholder="Email" class="rounded border-gray-200">
    <input name="password" placeholder="Password" class="rounded border-gray-200">
    <button class="rounded bg-gray-900 px-3 py-2 text-white text-sm">
        Add User
    </button>
</form>
        @endif
    </div>

</div>
@endsection
