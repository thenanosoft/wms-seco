<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('backup_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('frequency')->default('daily'); // daily or weekly
            $table->string('backup_path')->nullable(); // local folder path
            $table->unsignedTinyInteger('weekly_day')->default(1); // 1=Mon ... 7=Sun
            $table->string('time_hm')->default('02:00'); // 24h format HH:MM
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_settings');
    }
};

