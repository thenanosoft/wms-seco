<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id();

            $table->date('txn_date')->index();
            $table->enum('txn_type', [
                'PURCHASE',
                'ISSUE',
                'RETURN_IN',
                'RETURN_OUT',
                'ADJUSTMENT',
                'OPENING',
            ]);

            $table->string('ref_table');                  // purchases, issues, etc
            $table->unsignedBigInteger('ref_id');        // header id
            $table->unsignedBigInteger('ref_line_id')->nullable(); // line id

            $table->foreignId('item_id')->constrained('items');

            // Business rule: integer quantities and prices
            $table->unsignedInteger('qty_in')->default(0);
            $table->unsignedInteger('qty_out')->default(0);

            $table->unsignedInteger('unit_price')->default(0);
            $table->text('specification_snapshot')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['item_id', 'txn_date']);
            $table->index(['txn_type', 'txn_date']);
            $table->index(['ref_table', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};
