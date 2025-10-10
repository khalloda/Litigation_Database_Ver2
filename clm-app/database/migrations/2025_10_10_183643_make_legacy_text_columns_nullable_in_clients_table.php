<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Make legacy text columns nullable since we now use FK relationships
            $table->string('status')->nullable()->change();
            $table->string('cash_or_probono')->nullable()->change();
            $table->string('power_of_attorney_location')->nullable()->change();
            $table->string('documents_location')->nullable()->change();
            $table->string('contact_lawyer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Restore NOT NULL constraints (with default values)
            $table->string('status')->default('Active')->change();
            $table->string('cash_or_probono')->nullable()->change(); // Keep nullable
            $table->string('power_of_attorney_location')->default('Unknown')->change();
            $table->string('documents_location')->nullable()->change(); // Keep nullable
            $table->string('contact_lawyer')->nullable()->change(); // Keep nullable
        });
    }
};