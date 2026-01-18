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
            $table->decimal('issue_price', 15, 2)->default(0);
            $table->decimal('quantity', 15, 3);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_return_lines');
    }
};
