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
        Schema::create('import_sessions', function (Blueprint $table) {
            $table->id();
            
            // Session metadata
            $table->string('session_id', 36)->unique();
            $table->string('table_name', 64)->index();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->enum('status', [
                'uploaded',
                'mapped',
                'validated',
                'importing',
                'completed',
                'failed',
                'cancelled'
            ])->default('uploaded')->index();
            
            // File information
            $table->string('file_type', 10); // xlsx, xls, csv
            $table->unsignedInteger('file_size');
            $table->string('file_hash', 64);
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('header_row')->default(1);
            
            // Mapping configuration
            $table->json('column_mapping')->nullable();
            $table->json('transforms')->nullable();
            
            // Validation results
            $table->json('preflight_errors')->nullable();
            $table->unsignedInteger('preflight_error_count')->default(0);
            $table->unsignedInteger('preflight_warning_count')->default(0);
            
            // Import progress
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->json('import_errors')->nullable();
            
            // Backup information
            $table->string('backup_file')->nullable();
            $table->unsignedBigInteger('backup_size')->nullable();
            $table->timestamp('backup_created_at')->nullable();
            
            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            
            // User tracking
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_sessions');
    }
};

