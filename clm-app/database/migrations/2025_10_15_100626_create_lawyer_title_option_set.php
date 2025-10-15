<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\OptionSet;
use App\Models\OptionValue;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create option set `lawyer.title` and seed 8 standard titles
        $maxId = DB::table('option_sets')->max('id') ?? 0;
        $nextId = $maxId + 1;

        // Guard against duplicates if migration re-run partially
        if (!OptionSet::where('key', 'lawyer.title')->exists()) {
            DB::table('option_sets')->insert([
                'id' => $nextId,
                'key' => 'lawyer.title',
                'name_en' => 'Lawyer Titles',
                'name_ar' => 'مسميات المحامين',
                'description_en' => 'Standardized titles for lawyers',
                'description_ar' => 'مسميات قياسية للمحامين',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $setId = $nextId;

            $values = [
                ['code' => 'title_managing_partner', 'en' => 'Managing Partner', 'ar' => 'الشريك المدير'],
                ['code' => 'title_senior_partner',   'en' => 'Senior Partner',   'ar' => 'شريك رئيسي'],
                ['code' => 'title_partner',           'en' => 'Partner',          'ar' => 'شريك'],
                ['code' => 'title_junior_partner',    'en' => 'Junior Partner',   'ar' => 'شريك مبتدئ'],
                ['code' => 'title_senior_associate',  'en' => 'Senior Associate', 'ar' => 'محامي رئيسي'],
                ['code' => 'title_associate',         'en' => 'Associate',        'ar' => 'محامي'],
                ['code' => 'title_junior_associate',  'en' => 'Junior Associate', 'ar' => 'محامي مبتدئ'],
                ['code' => 'title_secretary',         'en' => 'Secretary',        'ar' => 'مساعد إداري'],
            ];

            foreach ($values as $v) {
                // option_values has AUTO_INCREMENT enabled; we must set set_id and code
                OptionValue::create([
                    'set_id'   => $setId,
                    'code'     => $v['code'],
                    'label_en' => $v['en'],
                    'label_ar' => $v['ar'],
                    'is_active'=> true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $set = OptionSet::where('key', 'lawyer.title')->first();
        if ($set) {
            DB::table('option_values')->where('set_id', $set->id)->delete();
            DB::table('option_sets')->where('id', $set->id)->delete();
        }
    }
};
