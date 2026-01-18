<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        return view('backup.index');
    }

    public function manualBackup()
    {
        $path = storage_path('app/backups');
        if (!File::exists($path)) File::makeDirectory($path, 0755, true);

        Artisan::call('wms:backup-now', ['--path' => $path]);

        // Pick latest file
        $files = collect(File::files($path))
            ->sortByDesc(fn($f) => $f->getMTime())
            ->values();

        if ($files->isEmpty()) {
            return back()->withErrors(['backup' => 'Backup failed. No SQL file created.']);
        }

        $latest = $files->first();

        return Response::download($latest->getPathname(), $latest->getFilename());
    }

    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => ['required','file','mimes:sql,txt'],
        ]);

        $tmp = $request->file('sql_file')->storeAs('restore', 'restore_' . time() . '.sql');

        $full = storage_path('app/' . $tmp);

        // Restore using mysql CLI
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $cmd = sprintf(
            'mysql -h%s -u%s %s %s < %s',
            escapeshellarg($host),
            escapeshellarg($user),
            $pass ? ('-p' . escapeshellarg($pass)) : '',
            escapeshellarg($db),
            escapeshellarg($full)
        );

        $code = 0;
        system($cmd, $code);

        if ($code !== 0) {
            try {
                $sql = File::get($full);
                $this->runSqlFallback($sql);
            } catch (\Throwable $e) {
                return back()->withErrors(['restore' => 'Restore failed. mysql CLI not available and fallback execution failed: ' . $e->getMessage()]);
            }
        }

        return redirect()->route('backup.index')->with('status', 'Restore completed successfully');
    }

    private function runSqlFallback(string $sql): void
    {
        // Simple fallback for data-only / basic dumps (no DELIMITER blocks)
        $sql = preg_replace('/^\s*(--|#).*$/m', '', $sql) ?? $sql;
        $sql = str_replace("\r\n", "\n", $sql);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Split on semicolons followed by newline to reduce false splits
        $statements = preg_split('/;\s*\n/', $sql) ?: [];
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') continue;
            DB::unprepared($stmt);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
