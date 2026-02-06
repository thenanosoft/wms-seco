<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('issue_return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_return_transaction_id')->constrained('issue_return_transactions')->cascadeOnDelete();
            $table->foreignId('issue_line_id')->constrained('issue_lines')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->string('specification')->nullable();
            // Business rule: integer quantities and prices
            $table->unsignedInteger('issue_price')->default(0);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_return_lines');
    }
};
