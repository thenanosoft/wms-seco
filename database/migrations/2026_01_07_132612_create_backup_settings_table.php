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
            $table->enum('frequency', ['daily','weekly'])->default('daily');
            $table->unsignedTinyInteger('weekly_day')->default(1); // 1 Mon ... 7 Sun
            $table->string('time_hm')->default('02:00'); // HH:MM
            $table->string('backup_path')->nullable(); // local folder
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_settings');
    }
};


