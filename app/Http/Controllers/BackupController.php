<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

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
            return back()->withErrors(['restore' => 'Restore failed. Ensure mysql CLI exists in PATH and SQL is valid.']);
        }

        return redirect()->route('backup.index')->with('status', 'Restore completed successfully');
    }
}
