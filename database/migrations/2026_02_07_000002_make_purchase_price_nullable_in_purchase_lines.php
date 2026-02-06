<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('purchase_lines')) {
            DB::statement("ALTER TABLE purchase_lines MODIFY purchase_price INT UNSIGNED NULL");
            DB::statement("ALTER TABLE purchase_lines MODIFY line_total BIGINT UNSIGNED NOT NULL DEFAULT 0");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_lines')) {
            DB::statement("ALTER TABLE purchase_lines MODIFY purchase_price INT UNSIGNED NOT NULL DEFAULT 0");
        }
    }
};
