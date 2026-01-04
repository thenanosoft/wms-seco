<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $groups = Group::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('group_code', 'like', "%{$q}%")
                      ->orWhere('group_name', 'like', "%{$q}%");
            })
            ->orderBy('group_code')
            ->paginate(15)
            ->withQueryString();

        return view('groups.index', compact('groups', 'q'));
    }

    public function create()
    {
        return view('groups.create');
    }

    public function store(StoreGroupRequest $request)
    {
        Group::create($request->validated());

        return redirect()->route('groups.index')->with('status', 'Group created successfully.');
    }

    public function edit(Group $group)
    {
        return view('groups.edit', compact('group'));
    }

    public function update(UpdateGroupRequest $request, Group $group)
    {
        $group->update($request->validated());

        return redirect()->route('groups.index')->with('status', 'Group updated successfully.');
    }

    public function destroy(Group $group)
    {
        // Will fail if items exist due to FK cascadeOnDelete in items migration.
        // We used cascadeOnDelete on group -> items, so deleting group will delete items too.
        // If you prefer to block delete, tell me and I will adjust logic.

        $group->delete();

        return redirect()->route('groups.index')->with('status', 'Group deleted successfully.');
    }
}
