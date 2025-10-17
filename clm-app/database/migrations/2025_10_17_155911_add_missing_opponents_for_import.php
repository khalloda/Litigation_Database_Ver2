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
        // Get the next available ID
        $nextId = DB::table('opponents')->max('id') + 1;
        
        $newOpponents = [
            [
                'id' => $nextId++,
                'opponent_name_ar' => 'شركة جاز',
                'opponent_name_en' => 'Gaz Company',
                'description' => 'Gaz Company - Imported from cases data',
                'notes' => 'Auto-created during import process',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => $nextId++,
                'opponent_name_ar' => 'شركة القاهرة للاستثمار والتنمية',
                'opponent_name_en' => 'Cairo Investment and Development Company',
                'description' => 'Cairo Investment and Development Company - Imported from cases data',
                'notes' => 'Auto-created during import process',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        DB::table('opponents')->insert($newOpponents);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the added opponents
        $names = [
            'شركة جاز',
            'شركة القاهرة للاستثمار والتنمية'
        ];
        
        DB::table('opponents')->whereIn('opponent_name_ar', $names)->delete();
    }
};