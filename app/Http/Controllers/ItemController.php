<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Group;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $groupId = $request->get('group_id');

        $groups = Group::query()->orderBy('group_code')->get(['id','group_code','group_name']);

        $items = Item::query()
            ->with('group:id,group_code,group_name')
            ->when($groupId, fn($query) => $query->where('group_id', $groupId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where('item_code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%");
            })
            ->orderBy('group_id')
            ->orderBy('item_code')
            ->paginate(15)
            ->withQueryString();

        return view('items.index', compact('items', 'groups', 'q', 'groupId'));
    }

    public function create()
    {
        $groups = Group::query()->orderBy('group_code')->get(['id','group_code','group_name']);
        return view('items.create', compact('groups'));
    }

    public function store(StoreItemRequest $request)
    {
        Item::create($request->validated());

        return redirect()->route('items.index')->with('status', 'Item created successfully.');
    }

    public function edit(Item $item)
    {
        $groups = Group::query()->orderBy('group_code')->get(['id','group_code','group_name']);
        return view('items.edit', compact('item', 'groups'));
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        $item->update($request->validated());

        return redirect()->route('items.index')->with('status', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('items.index')->with('status', 'Item deleted successfully.');
    }
}
