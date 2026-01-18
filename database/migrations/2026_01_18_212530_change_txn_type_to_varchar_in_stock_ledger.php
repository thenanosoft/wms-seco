<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('stock_ledger', function (Blueprint $table) {
        $table->string('txn_type', 50)->change();
    });
}

public function down(): void
{
    Schema::table('stock_ledger', function (Blueprint $table) {
        $table->enum('txn_type', [
            'PURCHASE',
            'ISSUE',
            'ISSUE_RETURN_IN'
        ])->change();
    });
}

};
