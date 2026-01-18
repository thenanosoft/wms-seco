<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // MySQL enum alter. Safe for fresh installs and most local setups.
        DB::statement("ALTER TABLE stock_ledger MODIFY txn_type ENUM('PURCHASE','ISSUE','ISSUE_RETURN_IN','RETURN_IN','RETURN_OUT','ADJUSTMENT','OPENING')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_ledger MODIFY txn_type ENUM('PURCHASE','ISSUE','RETURN_IN','RETURN_OUT','ADJUSTMENT','OPENING')");
    }
};
