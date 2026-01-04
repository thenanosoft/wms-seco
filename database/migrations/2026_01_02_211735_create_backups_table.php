<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('checksum')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('file_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
