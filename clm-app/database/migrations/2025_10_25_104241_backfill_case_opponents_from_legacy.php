<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Backfill existing single opponent data from cases table into case_opponents pivot table.
     * This migration transfers data from the legacy single opponent fields to the new multi-opponent structure.
     */
    public function up(): void
    {
        // Get all cases that have an opponent_id (existing single opponent data)
        $cases = DB::table('cases')
            ->whereNotNull('opponent_id')
            ->whereNull('deleted_at') // Only process non-deleted cases
            ->get();

        $insertData = [];
        $now = now();

        foreach ($cases as $case) {
            $insertData[] = [
                'case_id' => $case->id,
                'opponent_id' => $case->opponent_id,
                'capacity_id' => $case->opponent_capacity_id, // May be null
                'is_primary' => 1, // All existing opponents become primary
                'display_order' => 1, // First position
                'alias_text' => null, // No alias for existing data
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $case->created_by ?? null,
                'updated_by' => $case->updated_by ?? null,
            ];
        }

        // Batch insert all the data
        if (!empty($insertData)) {
            DB::table('case_opponents')->insert($insertData);

            // Log the migration results
            \Log::info('Case Opponents Backfill Migration', [
                'cases_processed' => count($insertData),
                'migration_timestamp' => $now->toDateTimeString(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Remove all case_opponents entries that were created by this backfill.
     * This is safe because we only remove entries that were created during backfill.
     */
    public function down(): void
    {
        // Get all cases that had opponent_id before migration
        $caseIds = DB::table('cases')
            ->whereNotNull('opponent_id')
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($caseIds->isNotEmpty()) {
            // Remove all case_opponents entries for these cases
            DB::table('case_opponents')
                ->whereIn('case_id', $caseIds)
                ->delete();

            \Log::info('Case Opponents Backfill Rollback', [
                'cases_affected' => $caseIds->count(),
                'rollback_timestamp' => now()->toDateTimeString(),
            ]);
        }
    }
};
