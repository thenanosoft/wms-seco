<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('item_code');
            $table->string('name');
            $table->text('default_spec')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'item_code']);
            $table->index('item_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
