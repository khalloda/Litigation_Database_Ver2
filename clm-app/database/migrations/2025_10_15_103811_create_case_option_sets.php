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
        // Prepare option sets to create
        $sets = [
            [
                'key' => 'case.category',
                'name_en' => 'Case Categories',
                'name_ar' => 'تصنيفات القضايا',
                'description_en' => 'List of case categories',
                'description_ar' => 'قائمة تصنيفات القضايا',
                'csv' => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_categories_translated.csv'),
            ],
            [
                'key' => 'case.degree',
                'name_en' => 'Case Degrees',
                'name_ar' => 'درجات التقاضي',
                'description_en' => 'List of case degrees',
                'description_ar' => 'قائمة درجات التقاضي',
                'csv' => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_degrees_translated.csv'),
            ],
            [
                'key' => 'case.status',
                'name_en' => 'Case Status',
                'name_ar' => 'حالة القضية',
                'description_en' => 'List of case statuses',
                'description_ar' => 'قائمة حالات القضايا',
                'csv' => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_status_translated.csv'),
            ],
            [
                'key' => 'case.importance',
                'name_en' => 'Case Importance',
                'name_ar' => 'أهمية القضية',
                'description_en' => 'List of case importance levels',
                'description_ar' => 'قائمة مستويات أهمية القضايا',
                'csv' => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_importance_translated.csv'),
            ],
            [
                'key' => 'case.branch',
                'name_en' => 'Case Branches',
                'name_ar' => 'فروع القضايا',
                'description_en' => 'List of case branches',
                'description_ar' => 'قائمة فروع القضايا',
                'csv' => null, // keep empty until further notice
            ],
            [
                'key' => 'capacity.type',
                'name_en' => 'Capacity Types',
                'name_ar' => 'الصفات القانونية',
                'description_en' => 'Capacity types for clients and opponents',
                'description_ar' => 'الصفات القانونية للعميل والخصم',
                'csv' => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/capacity_translated.csv'),
            ],
        ];

        $nextId = (DB::table('option_sets')->max('id') ?? 0) + 1;

        foreach ($sets as $set) {
            if (!OptionSet::where('key', $set['key'])->exists()) {
                DB::table('option_sets')->insert([
                    'id' => $nextId,
                    'key' => $set['key'],
                    'name_en' => $set['name_en'],
                    'name_ar' => $set['name_ar'],
                    'description_en' => $set['description_en'],
                    'description_ar' => $set['description_ar'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Seed values if CSV provided
                if (!empty($set['csv']) && file_exists($set['csv'])) {
                    $this->seedOptionValuesFromCsv($nextId, $set['csv']);
                }

                $nextId++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = ['case.category','case.degree','case.status','case.importance','case.branch','capacity.type'];
        $sets = OptionSet::whereIn('key', $keys)->get();
        foreach ($sets as $set) {
            DB::table('option_values')->where('set_id', $set->id)->delete();
            DB::table('option_sets')->where('id', $set->id)->delete();
        }
    }

    private function seedOptionValuesFromCsv(int $setId, string $csvPath): void
    {
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            return;
        }
        $headers = [];
        $rowIndex = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowIndex++;
            if ($rowIndex === 1) {
                $headers = array_map(fn($h) => strtolower(trim($h)), $row);
                continue;
            }
            $data = [];
            foreach ($row as $i => $value) {
                $key = $headers[$i] ?? 'col'.$i;
                $data[$key] = trim($value);
            }
            // Expecting headers: code, label_en, label_ar (tolerant to order)
            if (empty($data['code']) || (empty($data['label_en']) && empty($data['label_ar']))) {
                continue;
            }
            OptionValue::create([
                'set_id'   => $setId,
                'code'     => $data['code'],
                'label_en' => $data['label_en'] ?? $data['code'],
                'label_ar' => $data['label_ar'] ?? $data['code'],
                'is_active'=> true,
            ]);
        }
        fclose($handle);
    }
};
