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
        // Add staging_id if missing (surrogate PK)
        Schema::table('hearings_staging', function (Blueprint $table) {
            if (!Schema::hasColumn('hearings_staging', 'staging_id')) {
                $table->bigIncrements('staging_id')->first();
            }
        });

        // Add external_id column
        Schema::table('hearings_staging', function (Blueprint $table) {
            if (!Schema::hasColumn('hearings_staging', 'external_id')) {
                // Add after staging_id if it exists, otherwise after id
                if (Schema::hasColumn('hearings_staging', 'staging_id')) {
                    $table->string('external_id', 191)->nullable()->after('staging_id')->index();
                } elseif (Schema::hasColumn('hearings_staging', 'id')) {
                    $table->string('external_id', 191)->nullable()->after('id')->index();
                } else {
                    $table->string('external_id', 191)->nullable()->first()->index();
                }
            }
        });

        // Add date_raw and next_hearing_raw columns if missing
        Schema::table('hearings_staging', function (Blueprint $table) {
            if (!Schema::hasColumn('hearings_staging', 'date_raw')) {
                $table->string('date_raw', 64)->nullable()->after('date');
            }
        });

        Schema::table('hearings_staging', function (Blueprint $table) {
            if (!Schema::hasColumn('hearings_staging', 'next_hearing_raw')) {
                $table->string('next_hearing_raw', 64)->nullable()->after('next_hearing');
            }
        });

        // Make date nullable if it's currently NOT NULL
        try {
            DB::statement('ALTER TABLE hearings_staging MODIFY COLUMN date DATE NULL');
        } catch (\Exception $e) {
            // Column might already be nullable, ignore
        }

        // Add composite de-dup index (using prefix indexes for VARCHAR columns to avoid key length limit)
        try {
            // Use raw SQL to create index with prefix lengths for VARCHAR columns
            // Note: 'procedure' is a reserved word, so we escape it with backticks
            DB::statement("
                CREATE INDEX idx_stg_composite_dedup 
                ON hearings_staging (
                    matter_id, 
                    date, 
                    court(50), 
                    `procedure`(50), 
                    source_file(100), 
                    source_row
                )
            ");
        } catch (\Exception $e) {
            // Index might already exist, ignore
            if (strpos($e->getMessage(), 'Duplicate key name') === false && 
                strpos($e->getMessage(), 'already exists') === false &&
                strpos($e->getMessage(), 'Duplicate entry') === false) {
                // Re-throw if it's a different error
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('hearings_staging', function (Blueprint $table) {
                $table->dropIndex('idx_stg_composite_dedup');
            });
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }

        // Note: We don't drop external_id column in down() to preserve data
    }
};

