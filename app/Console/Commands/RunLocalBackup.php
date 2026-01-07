<?php

namespace App\Console\Commands;

use App\Models\BackupSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RunLocalBackup extends Command
{
    protected $signature = 'wms:backup';
    protected $description = 'Create a local SQL backup to configured folder';
    
    public function handle(): int
    {
        $setting = BackupSetting::query()->latest('id')->first();
        $now = now();
        [$hh, $mm] = array_pad(explode(':', $setting->time_hm), 2, '00');

        $shouldRun = false;

        if (!$setting || !$setting->enabled) {
            $this->info('Backup disabled.');
            return self::SUCCESS;
        }

        if (!$setting->backup_path) {
            $this->error('Backup path is not set.');
            return self::FAILURE;
        }

        if ($setting->frequency === 'daily') {
    $shouldRun = ($now->format('H') === $hh && $now->format('i') === $mm);
} else {
    // weekly
    $weekday = (int) $now->format('N'); // 1..7
    $shouldRun = ($weekday === (int)$setting->weekly_day && $now->format('H') === $hh && $now->format('i') === $mm);
}

if (!$shouldRun) {
    $this->info('Not scheduled time.');
    return self::SUCCESS;
}

        $path = rtrim($setting->backup_path, DIRECTORY_SEPARATOR);
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $filename = 'wms_backup_' . now()->format('Ymd_His') . '.sql';
        $full = $path . DIRECTORY_SEPARATOR . $filename;

        
        // Use mysqldump installed in system
        $cmd = sprintf(
            'mysqldump -h%s -u%s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($user),
            $pass ? ('-p' . escapeshellarg($pass)) : '',
            escapeshellarg($db),
            escapeshellarg($full)
        );

        $result = null;
        system($cmd, $result);

        if ($result !== 0) {
            $this->error('Backup failed. Check mysqldump availability and credentials.');
            return self::FAILURE;
        }

        $this->info('Backup created: ' . $full);
        return self::SUCCESS;
    }
}
