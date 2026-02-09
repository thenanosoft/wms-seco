<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Allow NULL issue_price when the original issue happened on a pending purchase price.
        // NOTE: We use raw SQL to avoid requiring doctrine/dbal.
        DB::statement('ALTER TABLE issue_return_lines MODIFY issue_price INT UNSIGNED NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE issue_return_lines MODIFY issue_price INT UNSIGNED NOT NULL DEFAULT 0');
    }
};
