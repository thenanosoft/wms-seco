<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Increase price/quantity precision so values are not rounded to 4 decimals.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE purchase_lines MODIFY purchase_price DECIMAL(24,8) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE purchase_lines MODIFY quantity DECIMAL(24,8) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE purchase_lines MODIFY line_total DECIMAL(24,8) UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE issue_lines MODIFY issue_price DECIMAL(24,8) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE issue_lines MODIFY quantity DECIMAL(24,8) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE issue_lines MODIFY line_total DECIMAL(24,8) UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE stock_batches MODIFY qty_purchased DECIMAL(24,8) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_batches MODIFY qty_available DECIMAL(24,8) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_batches MODIFY unit_price DECIMAL(24,8) NULL DEFAULT NULL');

        DB::statement('ALTER TABLE stock_ledger MODIFY qty_in DECIMAL(24,8) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock_ledger MODIFY qty_out DECIMAL(24,8) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock_ledger MODIFY unit_price DECIMAL(24,8) NULL DEFAULT NULL');

        DB::statement('ALTER TABLE issue_return_lines MODIFY issue_price DECIMAL(24,8) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE issue_return_lines MODIFY quantity DECIMAL(24,8) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE issue_return_lines MODIFY line_total DECIMAL(24,8) UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE purchase_return_lines MODIFY purchase_price DECIMAL(24,8) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE purchase_return_lines MODIFY quantity DECIMAL(24,8) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE purchase_return_lines MODIFY line_total DECIMAL(24,8) UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE return_lines MODIFY unit_price DECIMAL(24,8) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE return_lines MODIFY quantity DECIMAL(24,8) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE return_lines MODIFY line_total DECIMAL(24,8) UNSIGNED NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE purchase_lines MODIFY purchase_price DECIMAL(16,4) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE purchase_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE purchase_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE issue_lines MODIFY issue_price DECIMAL(16,4) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE issue_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE issue_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE stock_batches MODIFY qty_purchased DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_batches MODIFY qty_available DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_batches MODIFY unit_price DECIMAL(16,4) NULL DEFAULT NULL');

        DB::statement('ALTER TABLE stock_ledger MODIFY qty_in DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock_ledger MODIFY qty_out DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock_ledger MODIFY unit_price DECIMAL(16,4) NULL DEFAULT NULL');

        DB::statement('ALTER TABLE issue_return_lines MODIFY issue_price DECIMAL(16,4) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE issue_return_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE issue_return_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE purchase_return_lines MODIFY purchase_price DECIMAL(16,4) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE purchase_return_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE purchase_return_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE return_lines MODIFY unit_price DECIMAL(16,4) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE return_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE return_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');
    }
};
