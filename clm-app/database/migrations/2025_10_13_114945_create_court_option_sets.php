<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\OptionSet;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create 4 option sets for court dropdown fields
        // Values will be seeded later by the user
        
        // Get the next available ID
        $maxId = DB::table('option_sets')->max('id') ?? 0;
        $nextId = $maxId + 1;
        
        // Check if option sets already exist before creating
        if (!OptionSet::where('key', 'court.circuit')->exists()) {
            DB::table('option_sets')->insert([
                'id' => $nextId++,
                'key' => 'court.circuit',
                'name_en' => 'Court Circuits',
                'name_ar' => 'دوائر المحاكم',
                'description_en' => 'List of court circuits',
                'description_ar' => 'قائمة دوائر المحاكم',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!OptionSet::where('key', 'court.circuit_secretary')->exists()) {
            DB::table('option_sets')->insert([
                'id' => $nextId++,
                'key' => 'court.circuit_secretary',
                'name_en' => 'Circuit Secretaries',
                'name_ar' => 'أمناء الدوائر',
                'description_en' => 'List of circuit secretaries',
                'description_ar' => 'قائمة أمناء الدوائر',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!OptionSet::where('key', 'court.floor')->exists()) {
            DB::table('option_sets')->insert([
                'id' => $nextId++,
                'key' => 'court.floor',
                'name_en' => 'Court Floors',
                'name_ar' => 'طوابق المحاكم',
                'description_en' => 'List of court floor numbers',
                'description_ar' => 'قائمة أرقام طوابق المحاكم',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!OptionSet::where('key', 'court.hall')->exists()) {
            DB::table('option_sets')->insert([
                'id' => $nextId++,
                'key' => 'court.hall',
                'name_en' => 'Court Halls',
                'name_ar' => 'قاعات المحاكم',
                'description_en' => 'List of court hall numbers',
                'description_ar' => 'قائمة أرقام قاعات المحاكم',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the option sets and their values
        $keys = ['court.circuit', 'court.circuit_secretary', 'court.floor', 'court.hall'];
        
        foreach ($keys as $key) {
            $optionSet = OptionSet::where('key', $key)->first();
            if ($optionSet) {
                $optionSet->optionValues()->delete();
                $optionSet->delete();
            }
        }
    }
};
