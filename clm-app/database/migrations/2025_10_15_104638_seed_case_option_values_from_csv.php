<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\OptionSet;
use App\Models\OptionValue;

return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'case.category'   => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_categories_translated.csv'),
            'case.degree'     => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_degrees_translated.csv'),
            'case.status'     => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_status_translated.csv'),
            'case.importance' => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_importance_translated.csv'),
            'capacity.type'   => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/capacity_translated.csv'),
        ];

        foreach ($map as $setKey => $csvPath) {
            $set = OptionSet::where('key', $setKey)->first();
            if (!$set) {
                // Skip if the set doesn't exist (created in previous migration)
                continue;
            }
            if (!file_exists($csvPath)) {
                continue;
            }

            $existingCodes = OptionValue::where('set_id', $set->id)->pluck('code')->all();

            $handle = fopen($csvPath, 'r');
            if ($handle === false) {
                continue;
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
                    $key = $headers[$i] ?? 'col' . $i;
                    $data[$key] = trim($value);
                }

                // Flexible extraction of EN/AR labels
                $labelEn = $data['label_en']
                    ?? $data['matter_category_english']
                    ?? $data['matter_degree_english']
                    ?? $data['matter_status_english']
                    ?? $data['matter_importance_english']
                    ?? $data['client_capacity_english']
                    ?? ($data['col1'] ?? $data['col0'] ?? null);

                $labelAr = $data['label_ar']
                    ?? $data['matter_category_arabic']
                    ?? $data['matter_degree_arabic']
                    ?? $data['matter_status_arabic']
                    ?? $data['matter_importance_arabic']
                    ?? $data['client_capacity_arabic']
                    ?? ($data['col0'] ?? $data['col1'] ?? null);

                if (empty($labelEn) && empty($labelAr)) {
                    continue;
                }

                // Code handling: use provided code or generate a slug from EN then AR
                $code = $data['code'] ?? null;
                if (!$code) {
                    $base = $labelEn ?: $labelAr ?: 'value';
                    $code = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $base));
                    $code = trim($code, '_');
                    if ($code === '') {
                        $code = 'value';
                    }
                    // Ensure uniqueness within set
                    $orig = $code;
                    $n = 1;
                    while (in_array($code, $existingCodes, true)) {
                        $code = $orig . '_' . $n;
                        $n++;
                    }
                }

                if (in_array($code, $existingCodes, true)) {
                    // Skip duplicates
                    continue;
                }

                OptionValue::create([
                    'set_id'   => $set->id,
                    'code'     => $code,
                    'label_en' => $labelEn ?? $code,
                    'label_ar' => $labelAr ?? $code,
                    'is_active' => true,
                ]);
                $existingCodes[] = $code;
            }

            fclose($handle);
        }
    }

    public function down(): void
    {
        $keys = ['case.category', 'case.degree', 'case.status', 'case.importance', 'capacity.type'];
        $sets = OptionSet::whereIn('key', $keys)->get();
        foreach ($sets as $set) {
            DB::table('option_values')->where('set_id', $set->id)->delete();
        }
    }
};
