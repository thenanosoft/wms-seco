<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_line_id')
                ->unique()
                ->constrained('purchase_lines')
                ->cascadeOnDelete();

            $table->date('purchase_date');

            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('specification')->nullable();

            $table->unsignedInteger('qty_purchased');
            $table->unsignedInteger('qty_available');

            $table->unsignedInteger('unit_price')->nullable();

            $table->timestamps();

            $table->index(['item_id', 'purchase_date']);
            $table->index(['item_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
