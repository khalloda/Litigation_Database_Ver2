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
            if (!$set || !file_exists($csvPath)) {
                continue;
            }

            $rows = $this->readCsv($csvPath);
            foreach ($rows as $row) {
                $labelEn = $row['label_en'] ?? $row['english'] ?? null;
                $labelAr = $row['label_ar'] ?? $row['arabic'] ?? null;

                if (!$labelEn || !$labelAr) {
                    continue;
                }

                // Generate code slug from EN like we did during seeding
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $labelEn));
                $slug = trim($slug, '_');

                $ov = OptionValue::where('set_id', $set->id)
                    ->where('code', $slug)
                    ->first();

                if ($ov) {
                    $ov->label_ar = $labelAr; // force update
                    $ov->save();
                }
            }
        }
    }

    public function down(): void
    {
        // no-op
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }
        $rows = [];
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
            // Normalize expected keys to label_en/label_ar for our logic
            if (isset($data['matter_category_english'])) $data['label_en'] = $data['matter_category_english'];
            if (isset($data['matter_category_arabic']))  $data['label_ar'] = $data['matter_category_arabic'];
            if (isset($data['matter_degree_english']))   $data['label_en'] = $data['matter_degree_english'];
            if (isset($data['matter_degree_arabic']))    $data['label_ar'] = $data['matter_degree_arabic'];
            if (isset($data['matter_status_english']))   $data['label_en'] = $data['matter_status_english'];
            if (isset($data['matter_status_arabic']))    $data['label_ar'] = $data['matter_status_arabic'];
            if (isset($data['matter_importance_english'])) $data['label_en'] = $data['matter_importance_english'];
            if (isset($data['matter_importance_arabic']))  $data['label_ar'] = $data['matter_importance_arabic'];
            if (isset($data['client_capacity_english'])) $data['label_en'] = $data['client_capacity_english'];
            if (isset($data['client_capacity_arabic']))  $data['label_ar'] = $data['client_capacity_arabic'];

            // Handle different CSV header patterns
            if (isset($data['matter_category_english']) && !isset($data['label_en'])) $data['label_en'] = $data['matter_category_english'];
            if (isset($data['matter_category_arabic']) && !isset($data['label_ar'])) $data['label_ar'] = $data['matter_category_arabic'];

            $rows[] = $data;
        }
        fclose($handle);
        return $rows;
    }
};
