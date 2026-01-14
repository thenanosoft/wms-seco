<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table) {
            // Prevent duplicate ledger inserts for same document line
            $table->unique(['txn_type', 'ref_table', 'ref_line_id'], 'uq_stock_ledger_ref_line');
        });
    }

    public function down(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropUnique('uq_stock_ledger_ref_line');
        });
    }
};
