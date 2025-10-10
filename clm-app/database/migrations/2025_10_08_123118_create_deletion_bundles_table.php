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
        Schema::create('deletion_bundles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Root entity identification
            $table->string('root_type'); // 'Client' or 'Case'
            $table->unsignedBigInteger('root_id');
            $table->string('root_label'); // e.g., client name or case number

            // Snapshot data
            $table->json('snapshot_json'); // Entire graph snapshot
            $table->json('files_json')->nullable(); // File descriptors
            $table->integer('cascade_count')->default(0); // Total items in bundle

            // Deletion metadata
            $table->foreignId('deleted_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();

            // Status tracking
            $table->enum('status', ['trashed', 'restored', 'purged'])->default('trashed');
            $table->dateTime('ttl_at')->nullable(); // Auto-purge date
            $table->dateTime('restored_at')->nullable();
            $table->text('restore_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['root_type', 'root_id']);
            $table->index('status');
            $table->index('deleted_by');
            $table->index('ttl_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deletion_bundles');
    }
};
