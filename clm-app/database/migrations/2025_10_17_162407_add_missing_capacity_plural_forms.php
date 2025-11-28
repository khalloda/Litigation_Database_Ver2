<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the capacity.type option set
        $capacitySet = DB::table('option_sets')->where('key', 'capacity.type')->first();

        if ($capacitySet) {
            // Get the next available ID
            $nextId = DB::table('option_values')->max('id') + 1;

            $newCapacity = [
                'id' => $nextId,
                'set_id' => $capacitySet->id,
                'code' => 'appealed_against_them_plural',
                'label_ar' => 'مستأنف ضدهم',
                'label_en' => 'Appealed Against Them (Plural)',
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::table('option_values')->insert($newCapacity);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the added option value
        DB::table('option_values')->where('code', 'appealed_against_them_plural')->delete();
    }
};
