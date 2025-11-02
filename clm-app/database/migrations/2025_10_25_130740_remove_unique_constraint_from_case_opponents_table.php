<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove the unique constraint on (case_id, opponent_id) to allow:
     * 1. Re-attaching opponents after soft delete
     * 2. Same opponent with different capacities
     *
     * Uniqueness will be enforced in the service layer with whereNull('deleted_at')
     * and business rules for capacity multiplicity.
     */
    public function up(): void
    {
        Schema::table('case_opponents', function (Blueprint $table) {
            // Drop the unique constraint that prevents re-attaching after soft delete
            $table->dropUnique(['case_id', 'opponent_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Re-add the unique constraint if needed for rollback.
     */
    public function down(): void
    {
        Schema::table('case_opponents', function (Blueprint $table) {
            // Re-add the unique constraint
            $table->unique(['case_id', 'opponent_id']);
        });
    }
};
