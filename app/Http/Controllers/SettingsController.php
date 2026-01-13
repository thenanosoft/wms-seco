<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\AppSetting;
use App\Models\BackupSetting;

class SettingsController extends Controller
{
    public function index()
    {
        $defaultLow = AppSetting::get('default_low_stock_threshold', '0');
        $backup = BackupSetting::query()->latest('id')->first();

        return view('settings.index', compact('defaultLow','backup'));
    }

    public function update(UpdateSettingsRequest $request)
    {
        $data = $request->validated();

        AppSetting::set('default_low_stock_threshold', (string)($data['default_low_stock_threshold'] ?? '0'));

        BackupSetting::updateOrCreate(
            ['id' => optional(BackupSetting::query()->latest('id')->first())->id],
            [
                'enabled' => (bool)($data['backup_enabled'] ?? false),
                'frequency' => $data['backup_frequency'],
                'weekly_day' => (int)$data['backup_weekly_day'],
                'time_hm' => $data['backup_time_hm'],
                'backup_path' => $data['backup_path'] ?? null,
            ]
        );

        return redirect()->route('settings.index')->with('status', 'Settings saved');
    }
}
