<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\OptionSet;
use App\Models\OptionValue;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get max ID from option_sets to manually assign IDs (auto-increment disabled)
        $maxId = \DB::table('option_sets')->max('id') ?? 0;
        $nextId = $maxId + 1;

        // 1. Circuit Names (48 from CSV)
        \DB::table('option_sets')->insert([
            'id' => $nextId,
            'key' => 'circuit.name',
            'name_en' => 'Circuit Names',
            'name_ar' => 'أسماء الدوائر',
            'description_en' => 'List of circuit names',
            'description_ar' => 'قائمة أسماء الدوائر',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $circuitNamesSet = OptionSet::find($nextId);

        $circuitNames = [
            ['16 جنوب -137 س', '16 South – 137 s'],
            ['إداري', 'Administrative'],
            ['إداري أول أكتوبر', 'Administrative – First of October'],
            ['استئناف', 'Appeal'],
            ['جنح مستأنف', 'Appeals – Misdemeanors'],
            ['مباني', 'Buildings'],
            ['طعون', 'Cassations'],
            ['رئيس النيابة/ أحمد المرصفاوي', 'Chief Prosecutor/ Ahmed El-Marsafawy'],
            ['مدني', 'Civil'],
            ['مدني -عمال', 'Civil – Labor'],
            ['تجاري', 'Commercial'],
            ['مفوضين', 'Commissioners'],
            ['مفوضين استئناف', 'Commissioners Appeal'],
            ['لجنة (2) إنهاء المنازعات المالية', 'Committee (2) for Financial Disputes Resolution'],
            ['لجنة شركات رقم 27', 'Companies Committee No. 27'],
            ['تعويضات', 'Compensations'],
            ['اللجنة التأديبية', 'Disciplinary Committee'],
            ['اقتصادي', 'Economic'],
            ['دائرة اقتصادية', 'Economic Circuit'],
            ['جنح اقتصادي', 'Economic Misdemeanors'],
            ['فحص', 'Examination'],
            ['جنايات', 'Felonies'],
            ['الأولى', 'First'],
            ['لجنة تظلمات', 'Grievances Committee'],
            ['أفراد', 'Individuals'],
            ['لجنة تفتيش', 'Inspection Committee'],
            ['لجنة تفتيش برئاسة/ خالد مفتاح', 'Inspection Committee chaired by Khaled Moftah'],
            ['استثمار', 'Investment'],
            ['قطاع خدمات الاستثمار', 'Investment Services Sector'],
            ['عمال', 'Labor'],
            ['اللجنة الوزارية لفض منازعات الاستثمار', 'Ministerial Committee for Investment Dispute Resolution'],
            ['جنح', 'Misdemeanors'],
            ['جنح أول أكتوبر', 'Misdemeanors – First of October'],
            ['الاثنين تجاري ب', 'Monday Commercial Session B'],
            ['أول أكتوبر', 'October First'],
            ['مدني جزئي', 'Partial Civil'],
            ['لجنة تصالح برئاسة/ عبد العزيز الروبي', 'Reconciliation Committee chaired by Abdel Aziz El-Rouby'],
            ['حقوق وحريات', 'Rights and Freedoms'],
            ['جنح ثاني', 'Second Misdemeanors'],
            ['موضوعي', 'Subject Matter'],
            ['ضرائب', 'Taxes'],
            ['ضرائب ورسوم', 'Taxes and Fees'],
            ['الخميس تجاري', 'Thursday Commercial Session'],
            ['الثلاثاء مدني', 'Tuesday Civil Session'],
            ['الثلاثاء تجاري', 'Tuesday Commercial Session'],
            ['مستعجل', 'Urgent'],
            ['مستأنف مستعجل', 'Urgent Appeals'],
        ];

        foreach ($circuitNames as $index => $name) {
            OptionValue::create([
                'set_id' => $circuitNamesSet->id,
                'code' => 'circuit_name_' . ($index + 1),
                'label_ar' => $name[0],
                'label_en' => $name[1],
                'value' => $name[1], // Use English as value
                'is_active' => true,
            ]);
        }

        // 2. Circuit Serials (1-100, A-Z, أ-ي)
        $nextId++;
        \DB::table('option_sets')->insert([
            'id' => $nextId,
            'key' => 'circuit.serial',
            'name_en' => 'Circuit Serials',
            'name_ar' => 'أرقام الدوائر التسلسلية',
            'description_en' => 'Numeric and alphabetic serials for circuits',
            'description_ar' => 'الأرقام والحروف التسلسلية للدوائر',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $circuitSerialsSet = OptionSet::find($nextId);

        // Numbers 1-100
        for ($i = 1; $i <= 100; $i++) {
            OptionValue::create([
                'set_id' => $circuitSerialsSet->id,
                'code' => 'serial_' . $i,
                'label_ar' => (string)$i,
                'label_en' => (string)$i,
                'value' => (string)$i,
                'is_active' => true,
            ]);
        }

        // English letters A-Z
        foreach (range('A', 'Z') as $letter) {
            OptionValue::create([
                'set_id' => $circuitSerialsSet->id,
                'code' => 'serial_' . $letter,
                'label_ar' => $letter,
                'label_en' => $letter,
                'value' => $letter,
                'is_active' => true,
            ]);
        }

        // Arabic letters أ-ي
        $arabicLetters = ['أ', 'ب', 'ت', 'ث', 'ج', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ك', 'ل', 'م', 'ن', 'ه', 'و', 'ي'];
        foreach ($arabicLetters as $letter) {
            OptionValue::create([
                'set_id' => $circuitSerialsSet->id,
                'code' => 'serial_' . $letter,
                'label_ar' => $letter,
                'label_en' => $letter,
                'value' => $letter,
                'is_active' => true,
            ]);
        }

        // 3. Circuit Shifts (Morning/Night)
        $nextId++;
        \DB::table('option_sets')->insert([
            'id' => $nextId,
            'key' => 'circuit.shift',
            'name_en' => 'Circuit Shifts',
            'name_ar' => 'دوام الدوائر',
            'description_en' => 'Morning or night shift',
            'description_ar' => 'دوام صباحي أو مسائي',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $circuitShiftsSet = OptionSet::find($nextId);

        OptionValue::create([
            'set_id' => $circuitShiftsSet->id,
            'code' => 'shift_morning',
            'label_ar' => 'صباحي',
            'label_en' => 'Morning',
            'value' => 'morning',
            'is_active' => true,
        ]);

        OptionValue::create([
            'set_id' => $circuitShiftsSet->id,
            'code' => 'shift_night',
            'label_ar' => 'مسائي',
            'label_en' => 'Night',
            'value' => 'night',
            'is_active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        OptionSet::where('key', 'circuit.name')->delete();
        OptionSet::where('key', 'circuit.serial')->delete();
        OptionSet::where('key', 'circuit.shift')->delete();
    }
};
