<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_return_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('return_date');
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_transactions');
    }
};
