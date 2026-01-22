<?php

namespace App\Http\Middleware;

use App\Models\BackupSetting;
use App\Services\BackupService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutoBackupMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $user = $request->user();
            if (!$user || ($user->role ?? null) !== 'admin') {
                return $response;
            }

            $setting = BackupSetting::query()->latest('id')->first();
            if (!$setting || !($setting->auto_backup_enabled ?? false)) {
                return $response;
            }

            $freq = (string)($setting->auto_backup_frequency ?? 'weekly');
            $last = $setting->last_ran_at ? $setting->last_ran_at->copy() : null;
            $now = now();

            $due = false;
            if (!$last) {
                $due = true;
            } elseif ($freq === 'daily') {
                $due = $last->toDateString() !== $now->toDateString();
            } elseif ($freq === 'weekly') {
                $due = $last->diffInDays($now) >= 7;
            } else {
                $due = $last->diffInDays($now) >= 30;
            }

            if ($due) {
                /** @var BackupService $svc */
                $svc = app(BackupService::class);
                $fullPath = $svc->createBackup($setting);
                $setting->last_ran_at = $now;
                $setting->last_backup_file = basename($fullPath);
                $setting->save();
            }
        } catch (\Throwable $e) {
            Log::warning('AutoBackupMiddleware failed: ' . $e->getMessage());
        }

        return $response;
    }
}
