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
        // Check if matter_circuit column exists (it might already be renamed to matter_circuit_legacy)
        if (Schema::hasColumn('cases', 'matter_circuit')) {
            Schema::table('cases', function (Blueprint $table) {
                // Drop old matter_circuit FK if it exists
                $table->dropForeign(['matter_circuit']);
                $table->dropIndex('cases_matter_circuit_foreign');
                
                // Rename for legacy/import
                $table->renameColumn('matter_circuit', 'matter_circuit_legacy');
            });
        }

        // Get default shift ID (Morning)
        $defaultShiftId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'circuit.shift');
        })->where('code', 'shift_morning')->value('id');

        Schema::table('cases', function (Blueprint $table) use ($defaultShiftId) {
            // Add 3 new FKs after court_id
            $table->foreignId('circuit_name_id')->nullable()->after('court_id')
                ->constrained('option_values')->nullOnDelete();
            $table->foreignId('circuit_serial_id')->nullable()->after('circuit_name_id')
                ->constrained('option_values')->nullOnDelete();
            $table->foreignId('circuit_shift_id')->nullable()->default($defaultShiftId)->after('circuit_serial_id')
                ->constrained('option_values')->nullOnDelete();

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
        Schema::table('cases', function (Blueprint $table) {
            $table->dropForeign(['circuit_name_id']);
            $table->dropForeign(['circuit_serial_id']);
            $table->dropForeign(['circuit_shift_id']);
            $table->dropColumn(['circuit_name_id', 'circuit_serial_id', 'circuit_shift_id']);
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->renameColumn('matter_circuit_legacy', 'matter_circuit');
            $table->foreign('matter_circuit')->references('id')->on('option_values')->nullOnDelete();
        });
    }
};
