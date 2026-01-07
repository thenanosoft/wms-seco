<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('return_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('return_date');
            $table->enum('type', ['IN', 'OUT']); // IN = inward, OUT = outward
            $table->string('reference_no')->nullable();
            $table->string('party')->nullable(); // supplier / department
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_transactions');
    }
};
