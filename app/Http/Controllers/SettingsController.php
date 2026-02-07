<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\AppSetting;
use App\Models\BackupSetting;
use App\Models\Group;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Issue;

class SettingsController extends Controller
{
    public function index()
    {
        $defaultLow = AppSetting::get('default_low_stock_threshold', '0');
        $storeName = AppSetting::get('store_name', 'Warehouse Store Management System');
        $timezone = AppSetting::get('timezone', 'Asia/Karachi');

        $backup = BackupSetting::query()->latest('id')->first();

        // Export filters data
        $groups = Group::query()->orderBy('group_code')->get(['id','group_code','group_name']);
        $items = Item::query()->orderBy('item_code')->get(['id','group_id','item_code','name']);
        $suppliers = Purchase::query()
            ->whereNotNull('supplier_name')
            ->where('supplier_name','!=','')
            ->select('supplier_name')
            ->distinct()
            ->orderBy('supplier_name')
            ->pluck('supplier_name');
        $issuedTos = Issue::query()
            ->whereNotNull('issued_to')
            ->where('issued_to','!=','')
            ->select('issued_to')
            ->distinct()
            ->orderBy('issued_to')
            ->pluck('issued_to');

        return view('settings.index', compact('defaultLow','storeName','timezone','groups','items','suppliers','issuedTos'));
    }

    public function update(UpdateSettingsRequest $request)
    {
        $data = $request->validated();

        AppSetting::set('default_low_stock_threshold', (string)($data['default_low_stock_threshold'] ?? '0'));

        $storeName = trim((string)($data['store_name'] ?? ''));
        if ($storeName === '') {
            $storeName = 'Warehouse Store Management System';
        }
        AppSetting::set('store_name', $storeName);

        $timezone = (string)($data['timezone'] ?? 'Asia/Karachi');
        if ($timezone === '') {
            $timezone = 'Asia/Karachi';
        }
        AppSetting::set('timezone', $timezone);

        return redirect()->route('settings.index')->with('status', 'Settings saved');
    }
}
