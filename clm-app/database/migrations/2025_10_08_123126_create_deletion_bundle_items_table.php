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
        Schema::create('deletion_bundle_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bundle_id');
            
            // Model identification
            $table->string('model'); // e.g., 'Case', 'Hearing', 'Task', etc.
            $table->unsignedBigInteger('model_id')->nullable(); // Nullable for new items on restore
            
            // Snapshot data
            $table->json('payload_json'); // Single-row snapshot (attributes only)
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('bundle_id')
                ->references('id')
                ->on('deletion_bundles')
                ->cascadeOnDelete();
            
            // Indexes
            $table->index('bundle_id');
            $table->index(['model', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deletion_bundle_items');
    }
};
