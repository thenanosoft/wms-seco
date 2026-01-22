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
        if (!$setting || !($setting->enabled ?? false)) {
            return $response;
        }

        $tz = config('app.timezone', 'UTC');
        $now = now($tz);

        $timeHm = $setting->time_hm ?: '02:00';
        [$hh, $mm] = array_pad(explode(':', $timeHm), 2, '00');
        $dueTime = $now->copy()->setTime((int)$hh, (int)$mm, 0);

        // not reached run time yet
        if ($now->lt($dueTime)) {
            return $response;
        }

        $freq = (string)($setting->frequency ?? 'daily');

        // weekly day check only when weekly
        if ($freq === 'weekly') {
            $todayIso = (int)$now->isoWeekday(); // 1..7
            if ((int)($setting->weekly_day ?? 1) !== $todayIso) {
                return $response;
            }
        }

        // already ran today after dueTime?
        if ($setting->last_run_at) {
            $last = $setting->last_run_at->copy()->setTimezone($tz);
            if ($last->isSameDay($now) && $last->gte($dueTime)) {
                return $response;
            }
        }

        /** @var BackupService $svc */
        $svc = app(BackupService::class);
        $svc->createBackup($setting);

        $setting->last_run_at = $now;
        $setting->save();

    } catch (\Throwable $e) {
        Log::warning('AutoBackupMiddleware failed: ' . $e->getMessage());
    }

    return $response;
}

}
