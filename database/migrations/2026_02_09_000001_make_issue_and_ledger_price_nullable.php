<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Allow NULL prices for pending invoices (industry requirement)
        DB::statement("ALTER TABLE issue_lines MODIFY issue_price INT UNSIGNED NULL");
        DB::statement("ALTER TABLE stock_ledger MODIFY unit_price INT UNSIGNED NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE issue_lines MODIFY issue_price INT UNSIGNED NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE stock_ledger MODIFY unit_price INT UNSIGNED NOT NULL DEFAULT 0");
    }
};
