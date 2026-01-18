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
            $table->foreignId('issue_line_id')->constrained('issue_lines');
            $table->foreignId('item_id')->constrained('items');

            $table->text('specification_snapshot')->nullable();

            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);

            $table->timestamps();

            $table->index(['item_id']);
            $table->index(['issue_line_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_return_lines');
    }
};