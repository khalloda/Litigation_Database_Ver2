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
        // Drop old structure
        Schema::dropIfExists('court_circuit');

        // Create new structure with 3 circuit FKs
        Schema::create('court_circuit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('circuit_name_id')->constrained('option_values')->cascadeOnDelete();
            $table->foreignId('circuit_serial_id')->nullable()->constrained('option_values')->cascadeOnDelete();
            $table->foreignId('circuit_shift_id')->constrained('option_values')->cascadeOnDelete();
            $table->timestamps();

            // Unique constraint: same court can't have same circuit combination twice
            $table->unique(['court_id', 'circuit_name_id', 'circuit_serial_id', 'circuit_shift_id'], 'unique_court_circuit_combo');

            $table->index('court_id');
            $table->index('circuit_name_id');
            $table->index('circuit_serial_id');
            $table->index('circuit_shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_circuit');

        // Restore old structure
        Schema::create('court_circuit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained('option_values')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['court_id', 'option_value_id']);
        });
    }
};
