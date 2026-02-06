<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('issue_lines', function (Blueprint $table) {
            $table->foreignId('purchase_line_id')
                ->nullable()
                ->after('issue_id')
                ->constrained('purchase_lines')
                ->nullOnDelete();
            $table->index(['item_id', 'purchase_line_id']);
        });
    }

    public function down(): void
    {
        Schema::table('issue_lines', function (Blueprint $table) {
            $table->dropIndex(['item_id', 'purchase_line_id']);
            $table->dropConstrainedForeignId('purchase_line_id');
        });
    }
};
