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
        DB::statement("
            UPDATE cases
            SET notes_1 = REPLACE(notes_1, '_x000D_', '-')
            WHERE notes_1 LIKE '%_x000D_%'
        ");

        DB::statement("
            UPDATE cases
            SET notes_2 = REPLACE(notes_2, '_x000D_', '-')
            WHERE notes_2 LIKE '%_x000D_%'
        ");

        DB::statement("
            UPDATE cases
            SET financial_provision = REPLACE(financial_provision, '_x000D_', '-')
            WHERE financial_provision LIKE '%_x000D_%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: lossy cleanup.
    }
};

