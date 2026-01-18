<?php

namespace App\Console\Commands;

use App\Models\BackupSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

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
            $this->warn('mysqldump not available. Falling back to PHP data-only export...');

            try {
                $this->phpDataOnlyExport($full);
                $this->info('Backup created (data-only): ' . $full);
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error('Backup failed. Ensure mysqldump exists in system PATH or use MySQL client install.');
                $this->error($e->getMessage());
                return self::FAILURE;
            }
        }

        $this->info('Backup created: ' . $full);
        return self::SUCCESS;
    }

    private function phpDataOnlyExport(string $fullPath): void
    {
        $tables = DB::select('SHOW TABLES');
        $db = config('database.connections.mysql.database');
        $tableKey = 'Tables_in_' . $db;

        $skip = [
            'failed_jobs', 'cache', 'cache_locks', 'sessions',
            'job_batches', 'jobs', 'password_reset_tokens'
        ];

        $fh = fopen($fullPath, 'w');
        if (!$fh) {
            throw new \RuntimeException('Cannot write backup file');
        }

        fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($fh, "SET sql_mode='NO_AUTO_VALUE_ON_ZERO';\n");

        foreach ($tables as $row) {
            $t = $row->$tableKey ?? null;
            if (!$t || in_array($t, $skip, true)) {
                continue;
            }

            $rows = DB::table($t)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            foreach ($rows as $r) {
                $arr = (array) $r;
                $cols = array_map(fn($c) => '`' . str_replace('`','``',$c) . '`', array_keys($arr));
                $vals = array_map(function ($v) {
                    if (is_null($v)) return 'NULL';
                    if (is_bool($v)) return $v ? '1' : '0';
                    if (is_numeric($v)) return (string) $v;
                    return "'" . str_replace("'", "''", (string) $v) . "'";
                }, array_values($arr));

                fwrite(
                    $fh,
                    'INSERT INTO `' . str_replace('`','``',$t) . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ");\n"
                );
            }

            fwrite($fh, "\n");
        }

        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
    }
}
