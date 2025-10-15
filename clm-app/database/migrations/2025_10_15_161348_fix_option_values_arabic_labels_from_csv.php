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
        $map = [
            'case.category'   => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_categories_translated.csv'),
            'case.degree'     => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_degrees_translated.csv'),
            'case.status'     => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_status_translated.csv'),
            'case.importance' => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/matter_importance_translated.csv'),
            'capacity.type'   => base_path('../Access_Data_Export/Cases_Fields_Options_Lists/capacity_translated.csv'),
        ];

        foreach ($map as $setKey => $csvPath) {
            $set = OptionSet::where('key', $setKey)->first();
            if (!$set || !file_exists($csvPath)) {
                continue;
            }

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

                // Detect EN/AR columns by known headers
                $labelEn = $data['label_en']
                    ?? $data['matter_category_english']
                    ?? $data['matter_degree_english']
                    ?? $data['matter_status_english']
                    ?? $data['matter_importance_english']
                    ?? $data['client_capacity_english']
                    ?? ($data['col1'] ?? null);

                $labelAr = $data['label_ar']
                    ?? $data['matter_category_arabic']
                    ?? $data['matter_degree_arabic']
                    ?? $data['matter_status_arabic']
                    ?? $data['matter_importance_arabic']
                    ?? $data['client_capacity_arabic']
                    ?? ($data['col0'] ?? null);

                if (empty($labelEn) || empty($labelAr)) {
                    continue;
                }

                // Try to find option_value row: prefer label_en match; fallback to code slug of EN
                $q = OptionValue::where('set_id', $set->id)
                    ->where(function ($q) use ($labelEn) {
                        $q->where('label_en', $labelEn);
                    });

                $ov = $q->first();
                if (!$ov) {
                    // Fallback by code based on EN
                    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $labelEn));
                    $slug = trim($slug, '_');
                    $ov = OptionValue::where('set_id', $set->id)->where('code', $slug)->first();
                }

                if ($ov) {
                    // Only fix Arabic label if it's empty or equals English incorrectly
                    if (empty($ov->label_ar) || $ov->label_ar === $ov->label_en) {
                        $ov->label_ar = $labelAr;
                        $ov->save();
                    }
                }
            }
            fclose($handle);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: we won't revert label fixes automatically
    }
};
