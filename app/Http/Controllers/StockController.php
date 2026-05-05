<?php

namespace App\Http\Controllers;

use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request, StockService $stock)
    {
        $groupId = $request->filled('group_id') ? (int)$request->input('group_id') : null;
        $itemId = $request->filled('item_id') ? (int)$request->input('item_id') : null;
        $sort = (string)$request->input('sort', 'group_code');
        $dir = (string)$request->input('dir', 'asc');

        $rows = $stock->stockSummaryWithLowFlag($groupId, $itemId, $sort, $dir);

        $groups = DB::table('groups')->orderBy('group_code')->get(['id', 'group_code', 'group_name']);
        $allItems = DB::table('items')->orderBy('item_code')->get(['id', 'group_id', 'item_code', 'name']);
        $items = $groupId
            ? $allItems->where('group_id', $groupId)->values()
            : $allItems;

        return view('stock.index', compact('rows', 'groups', 'items', 'allItems'));
    }
}
