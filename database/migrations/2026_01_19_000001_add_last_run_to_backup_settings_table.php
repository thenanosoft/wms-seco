<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('backup_settings', function (Blueprint $table) {
            $table->timestamp('last_run_at')->nullable()->after('backup_path');
            $table->string('last_backup_file')->nullable()->after('last_run_at');
        });
    }

    public function down(): void
    {
        Schema::table('backup_settings', function (Blueprint $table) {
            $table->dropColumn(['last_run_at', 'last_backup_file']);
        });
    }
};
