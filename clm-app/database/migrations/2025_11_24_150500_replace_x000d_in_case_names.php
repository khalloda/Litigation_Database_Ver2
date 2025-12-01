<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Replace the hidden CR placeholder `_x000D_` with a hyphen for cleaner display
        DB::statement("
            UPDATE cases
            SET matter_name_ar = REPLACE(matter_name_ar, '_x000D_', '-')
            WHERE matter_name_ar LIKE '%_x000D_%'
        ");

        DB::statement("
            UPDATE cases
            SET matter_name_en = REPLACE(matter_name_en, '_x000D_', '-')
            WHERE matter_name_en LIKE '%_x000D_%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-reversible data cleanup (hyphen is a valid character),
        // so this down migration intentionally does nothing.
    }
};

