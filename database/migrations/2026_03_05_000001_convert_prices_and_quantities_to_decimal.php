<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Convert all price and quantity columns to DECIMAL(16,4) for 4-decimal support.
     */
    public function up(): void
    {
        // purchase_lines
        DB::statement('ALTER TABLE purchase_lines MODIFY purchase_price DECIMAL(16,4) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE purchase_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE purchase_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');

        // issue_lines
        DB::statement('ALTER TABLE issue_lines MODIFY issue_price DECIMAL(16,4) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE issue_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE issue_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');

        // stock_batches
        DB::statement('ALTER TABLE stock_batches MODIFY qty_purchased DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_batches MODIFY qty_available DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_batches MODIFY unit_price DECIMAL(16,4) NULL DEFAULT NULL');

        // stock_ledger
        DB::statement('ALTER TABLE stock_ledger MODIFY qty_in DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock_ledger MODIFY qty_out DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock_ledger MODIFY unit_price DECIMAL(16,4) NULL DEFAULT NULL');

        // issue_return_lines
        DB::statement('ALTER TABLE issue_return_lines MODIFY issue_price DECIMAL(16,4) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE issue_return_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE issue_return_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');

        // purchase_return_lines
        DB::statement('ALTER TABLE purchase_return_lines MODIFY purchase_price DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE purchase_return_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE purchase_return_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');

        // return_lines
        DB::statement('ALTER TABLE return_lines MODIFY unit_price DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE return_lines MODIFY quantity DECIMAL(16,4) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE return_lines MODIFY line_total DECIMAL(16,4) UNSIGNED NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        // Revert to integer types (data may truncate)
        DB::statement('ALTER TABLE purchase_lines MODIFY purchase_price INT UNSIGNED NULL DEFAULT NULL');
        DB::statement('ALTER TABLE purchase_lines MODIFY quantity INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE purchase_lines MODIFY line_total BIGINT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE issue_lines MODIFY issue_price INT UNSIGNED NULL DEFAULT NULL');
        DB::statement('ALTER TABLE issue_lines MODIFY quantity INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE issue_lines MODIFY line_total BIGINT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE stock_batches MODIFY qty_purchased INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_batches MODIFY qty_available INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_batches MODIFY unit_price INT UNSIGNED NULL DEFAULT NULL');

        DB::statement('ALTER TABLE stock_ledger MODIFY qty_in INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock_ledger MODIFY qty_out INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock_ledger MODIFY unit_price INT UNSIGNED NULL DEFAULT NULL');

        DB::statement('ALTER TABLE issue_return_lines MODIFY issue_price INT UNSIGNED NULL DEFAULT NULL');
        DB::statement('ALTER TABLE issue_return_lines MODIFY quantity INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE issue_return_lines MODIFY line_total BIGINT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE purchase_return_lines MODIFY purchase_price INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE purchase_return_lines MODIFY quantity INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE purchase_return_lines MODIFY line_total BIGINT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE return_lines MODIFY unit_price INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE return_lines MODIFY quantity INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE return_lines MODIFY line_total BIGINT UNSIGNED NOT NULL DEFAULT 0');
    }
};
