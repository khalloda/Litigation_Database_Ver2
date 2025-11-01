<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change matter_evaluation from VARCHAR(255) to TEXT to accommodate longer content.
     */
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->text('matter_evaluation')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     * Revert matter_evaluation back to VARCHAR(255).
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('matter_evaluation')->nullable()->change();
        });
    }
};
