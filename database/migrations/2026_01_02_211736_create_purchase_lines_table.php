<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->text('specification')->nullable();
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('quantity', 15, 3);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();

            $table->index('item_id');
            $table->index(['purchase_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_lines');
    }
};
