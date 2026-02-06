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
            // Business rule: prices & quantities are stored as integers
            // (e.g., PKR as whole currency; qty as whole units).
            $table->unsignedInteger('purchase_price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total');
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
