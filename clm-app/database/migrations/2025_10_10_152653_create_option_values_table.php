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
        Schema::create('option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('set_id')->constrained('option_sets')->onDelete('cascade');
            $table->string('code'); // Unique within set, e.g., 'cash', 'probono'
            $table->string('label_en');
            $table->string('label_ar');
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint: code must be unique within each set
            $table->unique(['set_id', 'code']);
            
            // Index for performance
            $table->index(['set_id', 'is_active', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_values');
    }
};