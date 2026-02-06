<?php

namespace App\Services;

use App\Models\BackupSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class BackupService
{
    public const DEFAULT_DIR = 'wms_backups';
    public const RETENTION_COUNT = 7;

    /**
     * Resolve backup directory under storage/app.
     */
    public function backupDir(?BackupSetting $setting = null): string
    {
        $dir = trim((string)($setting?->backup_path ?? ''), " \t\n\r\0\x0B/");
        if ($dir === '') {
            $dir = self::DEFAULT_DIR;
        }

        // Security: prevent path traversal and Windows drive/UNC paths.
        // Allowed: folder names and subfolders like "wms_backups" or "wms_backups/daily".
        $dir = str_replace('\\', '/', $dir);
        $isUnsafe =
            str_contains($dir, '..') ||
            str_starts_with($dir, '/') ||
            str_starts_with($dir, '.') ||
            preg_match('/^[A-Za-z]:\//', $dir) ||
            str_starts_with($dir, '//') ||
            !preg_match('/^[A-Za-z0-9_\-\/]+$/', $dir);

        if ($isUnsafe) {
            $dir = self::DEFAULT_DIR;
        }

        return storage_path('app/' . $dir);
    }

    /**
     * Create DB-only backup (.sql) in the configured folder.
     * Returns absolute path.
     */
    public function createBackup(?BackupSetting $setting = null): string
    {
        $dir = $this->backupDir($setting);
        File::ensureDirectoryExists($dir);

        $filename = 'backup_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.sql';
        $full = $dir . DIRECTORY_SEPARATOR . $filename;

        // Prefer mysqldump if available.
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host') ?: '127.0.0.1';
        $port = config('database.connections.mysql.port') ?: 3306;

        $dumpCmd = "mysqldump --single-transaction --quick --routines --triggers "
    . "--add-drop-table --add-drop-database "
    . "--databases \"{$db}\" --host=\"{$host}\" --port=\"{$port}\" --user=\"{$user}\"";

        if ($pass !== null && $pass !== '') {
            $dumpCmd .= " --password=\"" . addslashes($pass) . "\"";
        }
        $dumpCmd .= " > \"" . addslashes($full) . "\" 2>&1";

        $exit = 1;
        @system($dumpCmd, $exit);

        if ($exit !== 0 || !File::exists($full) || File::size($full) === 0) {
            // Fallback: schema + data via Laravel (best-effort, works without mysqldump)
            $sql = $this->generateSchemaAndDataSql();
            File::put($full, $sql);
        }

        $this->enforceRetention($dir);

        return $full;
    }

    /**
     * Restore DB from a .sql file.
     * If file is data-only (no CREATE TABLE), this will run migrate:fresh before applying.
     */
    public function restoreFromSqlFile(string $absoluteSqlPath): void
{
    if (!File::exists($absoluteSqlPath)) {
        throw new \RuntimeException('SQL file not found: ' . $absoluteSqlPath);
    }

    $sql = File::get($absoluteSqlPath);

    // If dump contains schema, always wipe current DB to avoid "table exists" errors.
    $looksLikeSchema =
        stripos($sql, 'CREATE TABLE') !== false ||
        stripos($sql, 'DROP TABLE') !== false ||
        stripos($sql, 'CREATE DATABASE') !== false;

    // Restore is destructive, so we explicitly clean DB before applying SQL.
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    if ($looksLikeSchema) {
        $this->dropAllTables();
    } else {
        // Data-only dump, rebuild schema via migrations first.
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    try {
        $this->executeSqlLarge($sql);
    } finally {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}


    public function listBackups(?BackupSetting $setting = null): array
{
    $dir = $this->backupDir($setting);
    if (!File::exists($dir)) return [];

    return collect(File::files($dir))
        ->filter(fn($f) => str_ends_with(strtolower($f->getFilename()), '.sql'))
        ->map(function ($f) {
            $name = $f->getFilename();

            // Extract datetime from filename: backup_YYYYMMDD_HHMMSS_xxx.sql
            preg_match('/backup_(\d{8})_(\d{6})/', $name, $m);
            $sortKey = ($m[1] ?? '00000000') . ($m[2] ?? '000000');

            return [
                'name'      => $name,
                'full_path'=> $f->getRealPath(),
                'sort_key' => $sortKey,
                'display'  => date('Y-m-d H:i:s', $f->getMTime()),
            ];
        })
        ->sortByDesc('sort_key') // ✅ 100% accurate
        ->values()
        ->all();
}



    public function enforceRetention(string $dir): void
    {
        $files = collect(File::files($dir))
            ->filter(fn($f) => str_ends_with(strtolower($f->getFilename()), '.sql'))
            ->sortByDesc(fn($f) => $f->getMTime())
            ->values();

        $toDelete = $files->slice(self::RETENTION_COUNT);
        foreach ($toDelete as $f) {
            @File::delete($f->getRealPath());
        }
    }

    private function executeSqlLarge(string $sql): void
    {
        // Basic SQL splitter for typical mysqldump output.
        $statements = preg_split('/;\s*\n/', $sql);
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '' || str_starts_with($stmt, '--') || str_starts_with($stmt, '/*')) continue;
            DB::unprepared($stmt . ';');
        }
    }

    private function generateSchemaAndDataSql(): string
    {
        // Best-effort: export current schema (tables) and data for all tables.
        // This fallback is used only if mysqldump is missing.
        $dbName = config('database.connections.mysql.database');
        $tables = DB::select("SELECT table_name as name FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
        $out = "-- WMS Backup (fallback)\n-- Generated: " . now()->toDateTimeString() . "\n\n";

        foreach ($tables as $t) {
            $table = $t->name;
            $create = DB::select("SHOW CREATE TABLE `{$table}`");
            if (!empty($create[0]->{'Create Table'})) {
                $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $out .= $create[0]->{'Create Table'} . ";\n\n";
            }

            $rows = DB::table($table)->get();
            if ($rows->count() === 0) continue;

            foreach ($rows->chunk(500) as $chunk) {
                $cols = array_keys((array)$chunk->first());
                $colList = implode(',', array_map(fn($c) => "`{$c}`", $cols));
                $out .= "INSERT INTO `{$table}` ({$colList}) VALUES\n";

                $values = [];
                foreach ($chunk as $row) {
                    $arr = (array)$row;
                    $vals = array_map(function ($v) {
                        if ($v === null) return 'NULL';
                        return "'" . str_replace("'", "''", (string)$v) . "'";
                    }, $arr);
                    $values[] = '(' . implode(',', $vals) . ')';
                }

                $out .= implode(",\n", $values) . ";\n\n";
            }
        }

        return $out;
    }

    public function resolveBackupFullPath(BackupSetting $settings, string $fileName): string
{
    $fileName = basename($fileName); // security, no path traversal
    $dir = $this->backupDir($settings);
    $full = $dir . DIRECTORY_SEPARATOR . $fileName;

    if (!File::exists($full)) {
        throw new \RuntimeException('Backup file not found: ' . $fileName);
    }

    return $full;
}

private function dropAllTables(): void
{
    $dbName = config('database.connections.mysql.database');

    $tables = DB::select(
        "SELECT table_name AS name FROM information_schema.tables WHERE table_schema = ?",
        [$dbName]
    );

    foreach ($tables as $t) {
        $table = $t->name ?? null;
        if (!$table) continue;
        DB::statement("DROP TABLE IF EXISTS `{$table}`");
    }
}

}
