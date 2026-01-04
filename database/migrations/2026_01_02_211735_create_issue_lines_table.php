<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('issue_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('issues')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->text('specification')->nullable();
            $table->decimal('issue_price', 15, 2)->default(0);
            $table->decimal('quantity', 15, 3);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->timestamps();

            $table->index('item_id');
            $table->index(['issue_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_lines');
    }
};
