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
        // Add M-Files ID to cases table (nullable)
        if (!Schema::hasColumn('cases', 'mfiles_id')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->string('mfiles_id')->nullable()->after('id')->comment('M-Files system identifier');
            });
        }

        // Add M-Files ID to power_of_attorneys table (nullable)
        if (!Schema::hasColumn('power_of_attorneys', 'mfiles_id')) {
            Schema::table('power_of_attorneys', function (Blueprint $table) {
                $table->string('mfiles_id')->nullable()->after('id')->comment('M-Files system identifier');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cases', 'mfiles_id')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->dropColumn('mfiles_id');
            });
        }

        if (Schema::hasColumn('power_of_attorneys', 'mfiles_id')) {
            Schema::table('power_of_attorneys', function (Blueprint $table) {
                $table->dropColumn('mfiles_id');
            });
        }
    }
};


