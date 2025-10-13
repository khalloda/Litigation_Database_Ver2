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
        Schema::table('cases', function (Blueprint $table) {
            // 1. Rename matter_court to matter_court_text for import mapping
            $table->renameColumn('matter_court', 'matter_court_text');
        });

        Schema::table('cases', function (Blueprint $table) {
            // 2. Add court_id as foreign key to courts table
            $table->foreignId('court_id')->nullable()->after('matter_category')
                ->constrained('courts')->nullOnDelete();
        });

        // 3. Convert text fields to foreign keys (need to drop and recreate)
        Schema::table('cases', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['matter_circuit', 'circuit_secretary', 'court_floor', 'court_hall']);
        });

        Schema::table('cases', function (Blueprint $table) {
            // Add as foreign keys to option_values
            $table->foreignId('matter_circuit')->nullable()->after('court_id')
                ->constrained('option_values')->nullOnDelete();
            $table->foreignId('circuit_secretary')->nullable()->after('matter_circuit')
                ->constrained('option_values')->nullOnDelete();
            $table->foreignId('court_floor')->nullable()->after('circuit_secretary')
                ->constrained('option_values')->nullOnDelete();
            $table->foreignId('court_hall')->nullable()->after('court_floor')
                ->constrained('option_values')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['court_id']);
            $table->dropForeign(['matter_circuit']);
            $table->dropForeign(['circuit_secretary']);
            $table->dropForeign(['court_floor']);
            $table->dropForeign(['court_hall']);
            
            // Drop columns
            $table->dropColumn(['court_id', 'matter_circuit', 'circuit_secretary', 'court_floor', 'court_hall']);
        });

        // Recreate as text columns
        Schema::table('cases', function (Blueprint $table) {
            $table->string('matter_circuit')->nullable();
            $table->string('circuit_secretary')->nullable();
            $table->integer('court_floor')->nullable();
            $table->integer('court_hall')->nullable();
        });

        // Rename back
        Schema::table('cases', function (Blueprint $table) {
            $table->renameColumn('matter_court_text', 'matter_court');
        });
    }
};
