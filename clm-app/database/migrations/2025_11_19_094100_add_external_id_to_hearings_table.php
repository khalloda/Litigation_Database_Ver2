<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            if (!Schema::hasColumn('hearings', 'external_id')) {
                $table->string('external_id', 191)->nullable()->after('id')->unique();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            if (Schema::hasColumn('hearings', 'external_id')) {
                // Drop unique index first
                $indexes = DB::select("SHOW INDEX FROM hearings WHERE Key_name = 'hearings_external_id_unique'");
                if (!empty($indexes)) {
                    $table->dropUnique(['external_id']);
                }
                $table->dropColumn('external_id');
            }
        });
    }
};

