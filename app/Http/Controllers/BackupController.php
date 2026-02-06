<?php

namespace App\Http\Controllers;

use App\Models\BackupSetting;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BackupController extends Controller
{
    public function index(BackupService $backupService)
    {
        $settings = BackupSetting::query()->latest('id')->first();
        if (!$settings) {
            $settings = BackupSetting::query()->create([
                'enabled' => false,
                'frequency' => 'daily',
                'weekly_day' => 1,
                'time_hm' => '02:00',
                'backup_path' => BackupService::DEFAULT_DIR,
            ]);
        }

        $autoBackups = $backupService->listBackups($settings);

        return view('backup.index', [
            'settings' => $settings,
            'autoBackups' => $autoBackups,
        ]);
    }

    /**
     * Manual backup (download).
     */
    public function manualBackup(BackupService $backupService)
    {
        $settings = BackupSetting::query()->latest('id')->first();
        if (!$settings) {
            abort(400, 'Backup settings not configured.');
        }

        $fullPath = $backupService->createBackup($settings);
return response()->download($fullPath, basename($fullPath), [
    'Content-Type' => 'application/sql',
]);
    }

    /**
 * Download a backup file from auto-backup folder (storage/app/<backup_path>).
 */
public function download(string $filename, BackupService $backupService)
{
    $settings = BackupSetting::query()->latest('id')->first();
    if (!$settings) {
        abort(400, 'Backup settings not configured.');
    }

    // Safety: only allow .sql filename, no path traversal
    if (!preg_match('/^[A-Za-z0-9._-]+\.sql$/', $filename)) {
        abort(400, 'Invalid filename.');
    }

    $full = $backupService->resolveBackupFullPath($settings, $filename);

    // Storage::download expects a path relative to disk, so use response()->download for absolute path
    if (!file_exists($full)) {
        abort(404, 'Backup file not found.');
    }

    return response()->download($full, $filename, [
        'Content-Type' => 'application/sql',
    ]);
}

/**
 * Download the latest backup (newest .sql) from configured backup folder.
 */
public function downloadLatest(BackupService $backupService)
{
    $settings = BackupSetting::query()->latest('id')->first();
    if (!$settings) {
        abort(400, 'Backup settings not configured.');
    }

    $list = $backupService->listBackups($settings); // newest first already
    if (empty($list)) {
        return back()->withErrors(['backup' => 'No backup files available.']);
    }

    $latest = $list[0];
    $full = $latest['full_path'] ?? null;
    $name = $latest['name'] ?? null;

    if (!$full || !$name || !file_exists($full)) {
        return back()->withErrors(['backup' => 'Latest backup file not found on disk.']);
    }

    return response()->download($full, $name, [
        'Content-Type' => 'application/sql',
    ]);
}


    /**
     * Update backup settings from backup screen.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'frequency' => ['required', Rule::in(['daily','weekly'])],
            'weekly_day' => ['nullable', 'integer', 'min:1', 'max:7'],
            'time_hm' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            // IMPORTANT: we store backups inside storage/app by default.
            // User can customize folder name. This is safe on a local LAN app.
            'backup_path' => ['required', 'string', 'max:180'],
        ]);

        $settings = BackupSetting::query()->latest('id')->first();
        if (!$settings) {
            $settings = new BackupSetting();
        }

        $settings->enabled = (bool)($validated['enabled'] ?? false);
        $settings->frequency = $validated['frequency'];
        $settings->weekly_day = (int)($validated['weekly_day'] ?? 1);
        $settings->time_hm = $validated['time_hm'];
        $settings->backup_path = trim($validated['backup_path']);
        $settings->save();

        return back()->with('success', 'Backup settings saved.');
    }

    /**
     * Restore database from uploaded SQL or from a selected auto backup.
     */
    public function restore(Request $request, BackupService $backupService)
    {
        $request->validate([
            'sql_file' => ['nullable', 'file', 'mimetypes:text/plain,application/sql,application/octet-stream', 'max:51200'],
            // Security: file name only, no path segments
            'selected_backup' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+\.sql$/'],
        ]);

        $settings = BackupSetting::query()->latest('id')->first();
        if (!$settings) {
            return back()->withErrors(['restore' => 'Backup settings not configured.']);
        }

        try {
            if ($request->hasFile('sql_file')) {
                $uploaded = $request->file('sql_file');
                $tmpName = 'restore_' . time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $uploaded->getClientOriginalName());
                $dir = storage_path('app/restore');
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $full = $uploaded->move($dir, $tmpName)->getPathname();
                $backupService->restoreFromSqlFile($full);
                return back()->with('success', 'Database restored successfully.');
            }

            if ($request->filled('selected_backup')) {
                $name = $request->string('selected_backup')->toString();
                if (!str_ends_with(strtolower($name), '.sql')) {
                    return back()->withErrors(['restore' => 'Invalid backup file selected.']);
                }
                $full = $backupService->resolveBackupFullPath($settings, $name);
                $backupService->restoreFromSqlFile($full);
                return back()->with('success', 'Database restored successfully.');
            }

            return back()->withErrors(['restore' => 'Please choose an SQL file or select a backup.']);
        } catch (\Throwable $e) {
            return back()->withErrors(['restore' => 'Restore failed. ' . $e->getMessage()]);
        }
    }
}
