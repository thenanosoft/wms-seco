<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('issue_returns', function (Blueprint $table) {
            $table->id();

            $table->date('return_date')->index();
            $table->foreignId('issue_id')->constrained('issues');

            $table->string('received_from')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('notes')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['issue_id', 'return_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_returns');
    }
};
