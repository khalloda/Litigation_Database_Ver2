<?php

namespace Database\Seeders;

use App\Models\OptionSet;
use App\Models\OptionValue;
use Illuminate\Database\Seeder;

class OptionSetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $optionSets = [
            [
                'key' => 'client.cash_or_probono',
                'name_en' => 'Cash or Pro Bono',
                'name_ar' => 'نقدي أو مجاني',
                'description_en' => 'Client payment type classification',
                'description_ar' => 'تصنيف نوع دفع العميل',
                'values' => [
                    ['code' => 'cash', 'label_en' => 'Cash', 'label_ar' => 'نقدي', 'position' => 1],
                    ['code' => 'probono', 'label_en' => 'Pro Bono', 'label_ar' => 'مجاني', 'position' => 2],
                    ['code' => 'unknown', 'label_en' => 'Unknown', 'label_ar' => 'غير محدد', 'position' => 3],
                ]
            ],
            [
                'key' => 'client.status',
                'name_en' => 'Client Status',
                'name_ar' => 'حالة العميل',
                'description_en' => 'Current status of the client relationship',
                'description_ar' => 'الحالة الحالية لعلاقة العميل',
                'values' => [
                    ['code' => 'disabled', 'label_en' => 'Disabled', 'label_ar' => 'معطل', 'position' => 1],
                    ['code' => 'active', 'label_en' => 'Active', 'label_ar' => 'نشط', 'position' => 2],
                    ['code' => 'potential', 'label_en' => 'Potential', 'label_ar' => 'محتمل', 'position' => 3],
                ]
            ],
            [
                'key' => 'client.power_of_attorney_location',
                'name_en' => 'Power of Attorney Location',
                'name_ar' => 'مكان التوكيل',
                'description_en' => 'Physical location of power of attorney document',
                'description_ar' => 'الموقع المادي لمستند التوكيل',
                'values' => [
                    ['code' => 'archive', 'label_en' => 'Archive', 'label_ar' => 'أرشيف', 'position' => 1],
                    ['code' => 'safe', 'label_en' => 'Safe', 'label_ar' => 'خزنة', 'position' => 2],
                    ['code' => 'handed_to_client', 'label_en' => 'Handed to Client', 'label_ar' => 'مسلم للعميل', 'position' => 3],
                ]
            ],
            [
                'key' => 'client.documents_location',
                'name_en' => 'Documents Location',
                'name_ar' => 'مكان المستندات',
                'description_en' => 'Physical location of client documents',
                'description_ar' => 'الموقع المادي لمستندات العميل',
                'values' => [
                    ['code' => 'archive', 'label_en' => 'Archive', 'label_ar' => 'أرشيف', 'position' => 1],
                    ['code' => 'safe', 'label_en' => 'Safe', 'label_ar' => 'خزنة', 'position' => 2],
                    ['code' => 'handed_to_client', 'label_en' => 'Handed to Client', 'label_ar' => 'مسلم للعميل', 'position' => 3],
                ]
            ],
        ];

        foreach ($optionSets as $setData) {
            $values = $setData['values'];
            unset($setData['values']);

            $optionSet = OptionSet::create($setData);

            foreach ($values as $valueData) {
                $valueData['set_id'] = $optionSet->id;
                OptionValue::create($valueData);
            }
        }
    }
}