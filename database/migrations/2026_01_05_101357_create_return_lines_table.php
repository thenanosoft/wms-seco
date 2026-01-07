<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained();
            $table->string('specification')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('quantity', 12, 3);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_lines');
    }
};
