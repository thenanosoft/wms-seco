<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('issue_return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_return_id')->constrained('issue_returns')->cascadeOnDelete();

            $table->foreignId('issue_line_id')->constrained('issue_lines')->restrictOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();

            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2); // locked from issue line
            $table->decimal('line_total', 14, 2);

            $table->timestamps();

            // Prevent duplicate return line for same issue_line inside one return
            $table->unique(['issue_return_id', 'issue_line_id'], 'uq_issue_return_line_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_return_lines');
    }
};
