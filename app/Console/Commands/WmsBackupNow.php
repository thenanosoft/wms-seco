<?php

namespace App\Console\Commands;

use App\Models\BackupSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class WmsBackupNow extends Command
{
    protected $signature = 'wms:backup-now {--path=}';
    protected $description = 'Create SQL backup to configured folder';

    public function handle(): int
    {
        $setting = BackupSetting::query()->latest('id')->first();
        $path = $this->option('path') ?: ($setting?->backup_path);

        if (!$path) {
            $this->error('Backup path not set in settings.');
            return self::FAILURE;
        }

        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $conn = config('database.default');
        if ($conn !== 'mysql') {
            $this->error('This backup command currently supports MySQL only.');
            return self::FAILURE;
        }

        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $filename = 'wms_backup_' . now()->format('Ymd_His') . '.sql';
        $full = $path . DIRECTORY_SEPARATOR . $filename;

        $cmd = sprintf(
            'mysqldump -h%s -u%s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($user),
            $pass ? ('-p' . escapeshellarg($pass)) : '',
            escapeshellarg($db),
            escapeshellarg($full)
        );

        $code = 0;
        system($cmd, $code);

        if ($code !== 0) {
            $this->error('Backup failed. Ensure mysqldump exists in system PATH.');
            return self::FAILURE;
        }

        $this->info('Backup created: ' . $full);
        return self::SUCCESS;
    }
}
