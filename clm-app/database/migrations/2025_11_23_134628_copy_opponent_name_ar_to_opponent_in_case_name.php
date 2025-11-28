<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Copy opponent_name_ar from opponents table to opponent_in_case_name in cases table
     * where cases.opponent_id matches opponents.id
     */
    public function up(): void
    {
        DB::statement("
            UPDATE cases c
            INNER JOIN opponents o ON o.id = c.opponent_id
            SET c.opponent_in_case_name = o.opponent_name_ar
            WHERE c.opponent_id IS NOT NULL
              AND o.opponent_name_ar IS NOT NULL
              AND o.opponent_name_ar != ''
        ");
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This cannot be fully reversed as we don't know what the original values were.
     * This will set opponent_in_case_name to NULL for cases that had opponent_id.
     */
    public function down(): void
    {
        // Clear opponent_in_case_name for cases that have opponent_id
        // This is a best-effort rollback - original values cannot be restored
        DB::statement("
            UPDATE cases
            SET opponent_in_case_name = NULL
            WHERE opponent_id IS NOT NULL
        ");
    }
};
