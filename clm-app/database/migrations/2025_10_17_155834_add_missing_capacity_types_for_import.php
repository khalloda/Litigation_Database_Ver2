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
            
            $newCapacities = [
                [
                    'id' => $nextId++,
                    'set_id' => $capacitySet->id,
                    'code' => 'defendant_female',
                    'label_ar' => 'مدعى عليها',
                    'label_en' => 'Defendant (Female)',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => $nextId++,
                    'set_id' => $capacitySet->id,
                    'code' => 'challengers_plural',
                    'label_ar' => 'طاعنان',
                    'label_en' => 'Challengers (Plural)',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => $nextId++,
                    'set_id' => $capacitySet->id,
                    'code' => 'appealed_against_them',
                    'label_ar' => 'مطعون ضدهم',
                    'label_en' => 'Appealed Against Them',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => $nextId++,
                    'set_id' => $capacitySet->id,
                    'code' => 'plaintiff_female',
                    'label_ar' => 'مدعية',
                    'label_en' => 'Plaintiff (Female)',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => $nextId++,
                    'set_id' => $capacitySet->id,
                    'code' => 'grievant_female',
                    'label_ar' => 'متظلمة',
                    'label_en' => 'Grievant (Female)',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => $nextId++,
                    'set_id' => $capacitySet->id,
                    'code' => 'arbitrants_plural',
                    'label_ar' => 'محتكمون',
                    'label_en' => 'Arbitrants (Plural)',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            DB::table('option_values')->insert($newCapacities);
        }
        
        // Also add missing circuit name
        $circuitSet = DB::table('option_sets')->where('key', 'circuit.name')->first();
        
        if ($circuitSet) {
            $nextId = DB::table('option_values')->max('id') + 1;
            
            $newCircuit = [
                'id' => $nextId,
                'set_id' => $circuitSet->id,
                'code' => 'criminal',
                'label_ar' => 'جنائي',
                'label_en' => 'Criminal',
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            DB::table('option_values')->insert($newCircuit);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the added option values
        $codes = [
            'defendant_female',
            'challengers_plural', 
            'appealed_against_them',
            'plaintiff_female',
            'grievant_female',
            'arbitrants_plural',
            'criminal'
        ];
        
        DB::table('option_values')->whereIn('code', $codes)->delete();
    }
};