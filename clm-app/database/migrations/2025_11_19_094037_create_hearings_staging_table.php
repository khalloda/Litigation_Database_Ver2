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
        Schema::create('hearings_staging', function (Blueprint $table) {
            // Staging surrogate PK
            $table->bigIncrements('staging_id');
            
            // External ID from source file (if any)
            $table->string('external_id', 64)->nullable()->index();
            
            // Legacy id column (kept for compatibility, nullable)
            $table->integer('id')->nullable();
            
            // Template columns (mirroring hearings table structure)
            $table->integer('matter_id'); // NOT NULL
            $table->integer('lawyer_id')->nullable();
            
            // Date columns (nullable with raw keepers)
            $table->date('date')->nullable();
            $table->string('date_raw', 64)->nullable();
            $table->date('next_hearing')->nullable();
            $table->string('next_hearing_raw', 64)->nullable();
            
            $table->string('procedure', 255)->nullable();
            $table->string('court', 255)->nullable();
            $table->string('circuit', 255)->nullable();
            $table->string('destination', 255)->nullable();
            
            $table->text('decision')->nullable();
            $table->string('short_decision', 255)->nullable();
            $table->string('last_decision', 255)->nullable();
            
            $table->boolean('report')->default(false);
            $table->boolean('notify_client')->default(false);
            
            // Attendees
            $table->string('attendee', 255)->nullable();
            $table->string('attendee_1', 255)->nullable();
            $table->string('attendee_2', 255)->nullable();
            $table->string('attendee_3', 255)->nullable();
            $table->string('attendee_4', 255)->nullable();
            $table->string('next_attendee', 255)->nullable();
            
            $table->string('evaluation', 255)->nullable();
            $table->text('notes')->nullable();
            
            // Audit columns for staging
            $table->string('source_file', 255)->nullable();
            $table->integer('source_row')->nullable();
            $table->string('mapping_profile', 128)->nullable();
            $table->json('transform_warnings')->nullable();
            $table->dateTime('loaded_at')->useCurrent();
            
            // Indexes
            $table->index('matter_id', 'idx_stg_matter_id');
            $table->index('lawyer_id', 'idx_stg_lawyer_id');
            $table->index('date', 'idx_stg_date');
            // Note: Unique constraint on nullable 'id' not supported in MySQL
            // Uniqueness validated in validate-staging command instead
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearings_staging');
    }
};
