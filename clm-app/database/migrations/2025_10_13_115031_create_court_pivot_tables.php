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
        // Pivot table for court circuits (many-to-many)
        Schema::create('court_circuit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained('option_values')->cascadeOnDelete();
            $table->timestamps();
            
            // Prevent duplicates
            $table->unique(['court_id', 'option_value_id']);
            
            // Indexes for performance
            $table->index('court_id');
            $table->index('option_value_id');
        });

        // Pivot table for court secretaries (many-to-many)
        Schema::create('court_secretary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained('option_values')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['court_id', 'option_value_id']);
            $table->index('court_id');
            $table->index('option_value_id');
        });

        // Pivot table for court floors (many-to-many)
        Schema::create('court_floor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained('option_values')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['court_id', 'option_value_id']);
            $table->index('court_id');
            $table->index('option_value_id');
        });

        // Pivot table for court halls (many-to-many)
        Schema::create('court_hall', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained('option_values')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['court_id', 'option_value_id']);
            $table->index('court_id');
            $table->index('option_value_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_hall');
        Schema::dropIfExists('court_floor');
        Schema::dropIfExists('court_secretary');
        Schema::dropIfExists('court_circuit');
    }
};
