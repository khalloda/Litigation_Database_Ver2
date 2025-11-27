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
        Schema::create('case_opponents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('opponent_id')->constrained('opponents')->restrictOnDelete();
            $table->foreignId('capacity_id')->nullable()->constrained('option_values')->nullOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->integer('display_order')->nullable();
            $table->string('alias_text', 191)->nullable();

            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['case_id', 'display_order']);
            $table->index('opponent_id');
            $table->index(['case_id', 'is_primary']);

            // Unique constraint to prevent duplicate opponents per case
            $table->unique(['case_id', 'opponent_id']);

            // Foreign keys for audit columns
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_opponents');
    }
};
