<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        // MySQL enum change requires raw SQL. For PostgreSQL, you would use a check constraint or enum type.
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE stock_ledger MODIFY txn_type ENUM('PURCHASE','ISSUE','ISSUE_RETURN_IN','RETURN_IN','RETURN_OUT','ADJUSTMENT','OPENING') NOT NULL");
        }

        // Unique guard against duplicate ledger writes for same source line
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->unique(['txn_type','ref_table','ref_id','ref_line_id','item_id'], 'uniq_stockledger_source');
        });
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropUnique('uniq_stockledger_source');
        });

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE stock_ledger MODIFY txn_type ENUM('PURCHASE','ISSUE','RETURN_IN','RETURN_OUT','ADJUSTMENT','OPENING') NOT NULL");
        }
    }
};
