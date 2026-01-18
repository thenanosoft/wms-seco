<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('issue_returns', function (Blueprint $table) {
            $table->id();
            $table->date('return_date');
            $table->foreignId('issue_id')->constrained('issues')->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_returns');
    }
};
