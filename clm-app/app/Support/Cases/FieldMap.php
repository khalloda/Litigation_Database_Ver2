<?php

namespace App\Support\Cases;

class FieldMap
{
    public static function all(): array
    {
        return [
            // Overview section
            'id' => ['section' => 'overview', 'label_en' => 'ID', 'label_ar' => 'المعرف', 'format' => 'raw'],
            'matter_name_ar' => ['section' => 'overview', 'label_en' => 'Matter (AR)', 'label_ar' => 'المسألة (عربي)', 'format' => 'text'],
            'matter_name_en' => ['section' => 'overview', 'label_en' => 'Matter (EN)', 'label_ar' => 'المسألة (إنجليزي)', 'format' => 'text'],
            'team_id' => ['section' => 'overview', 'label_en' => 'Team', 'label_ar' => 'الفريق', 'format' => 'raw'],
            'matter_start_date' => ['section' => 'overview', 'label_en' => 'Start Date', 'label_ar' => 'تاريخ البداية', 'format' => 'date'],
            'matter_end_date' => ['section' => 'overview', 'label_en' => 'End Date', 'label_ar' => 'تاريخ الانتهاء', 'format' => 'date'],

            // Parties section
            'client_id' => ['section' => 'parties', 'label_en' => 'Client', 'label_ar' => 'العميل', 'format' => 'fk:client'],
            'client_in_case_name' => ['section' => 'parties', 'label_en' => 'Client in Case Name', 'label_ar' => 'اسم العميل في القضية', 'format' => 'text'],
            'client_capacity_id' => ['section' => 'parties', 'label_en' => 'Client Capacity', 'label_ar' => 'صفة العميل', 'format' => 'fk:option:clientCapacity'],
            'client_capacity_note' => ['section' => 'parties', 'label_en' => 'Client Capacity Note', 'label_ar' => 'ملاحظة صفة العميل', 'format' => 'text'],
            'opponent_id' => ['section' => 'parties', 'label_en' => 'Opponent', 'label_ar' => 'الخصم', 'format' => 'fk:opponent'],
            'opponent_in_case_name' => ['section' => 'parties', 'label_en' => 'Opponent in Case Name', 'label_ar' => 'اسم الخصم في القضية', 'format' => 'text'],
            'opponent_capacity_id' => ['section' => 'parties', 'label_en' => 'Opponent Capacity', 'label_ar' => 'صفة الخصم', 'format' => 'fk:option:opponentCapacity'],
            'opponent_capacity_note' => ['section' => 'parties', 'label_en' => 'Opponent Capacity Note', 'label_ar' => 'ملاحظة صفة الخصم', 'format' => 'text'],
            'lawyer_a' => ['section' => 'parties', 'label_en' => 'Lawyer A', 'label_ar' => 'المحامي أ', 'format' => 'person'],
            'lawyer_b' => ['section' => 'parties', 'label_en' => 'Lawyer B', 'label_ar' => 'المحامي ب', 'format' => 'person'],

            // Court & Circuit section
            'court_id' => ['section' => 'court', 'label_en' => 'Court', 'label_ar' => 'المحكمة', 'format' => 'fk:court'],
            'matter_destination_id' => ['section' => 'court', 'label_en' => 'Matter Destination', 'label_ar' => 'جهة المسألة', 'format' => 'fk:matterDestinationRef'],
            'circuit_name_id' => ['section' => 'court', 'label_en' => 'Circuit Name', 'label_ar' => 'اسم الدائرة', 'format' => 'fk:option:circuitName'],
            'circuit_serial_id' => ['section' => 'court', 'label_en' => 'Circuit Serial', 'label_ar' => 'رقم الدائرة', 'format' => 'fk:option:circuitSerial'],
            'circuit_shift_id' => ['section' => 'court', 'label_en' => 'Circuit Shift', 'label_ar' => 'وردية الدائرة', 'format' => 'fk:option:circuitShift'],
            'circuit_secretary' => ['section' => 'court', 'label_en' => 'Circuit Secretary', 'label_ar' => 'أمين الدائرة', 'format' => 'fk:option:circuitSecretaryRef'],
            'court_floor' => ['section' => 'court', 'label_en' => 'Court Floor', 'label_ar' => 'طابق المحكمة', 'format' => 'fk:option:courtFloorRef'],
            'court_hall' => ['section' => 'court', 'label_en' => 'Court Hall', 'label_ar' => 'قاعة المحكمة', 'format' => 'fk:option:courtHallRef'],

            // Status & Progress section
            'matter_degree_id' => ['section' => 'status', 'label_en' => 'Degree', 'label_ar' => 'الدرجة', 'format' => 'fk:option:matterDegree'],
            'matter_status_id' => ['section' => 'status', 'label_en' => 'Status', 'label_ar' => 'الحالة', 'format' => 'fk:option:matterStatus'],
            'matter_importance_id' => ['section' => 'status', 'label_en' => 'Importance', 'label_ar' => 'الأهمية', 'format' => 'fk:option:matterImportance'],
            'matter_category_id' => ['section' => 'status', 'label_en' => 'Category', 'label_ar' => 'الفئة', 'format' => 'fk:option:matterCategory'],
            'current_status' => ['section' => 'status', 'label_en' => 'Current Status', 'label_ar' => 'الحالة الحالية', 'format' => 'longtext'],
            'matter_evaluation' => ['section' => 'status', 'label_en' => 'Evaluation', 'label_ar' => 'التقييم', 'format' => 'longtext'],

            // Financials section
            'client_type_id' => ['section' => 'financials', 'label_en' => 'Client Type', 'label_ar' => 'نوع العميل', 'format' => 'fk:option:clientType'],
            'allocated_budget' => ['section' => 'financials', 'label_en' => 'Allocated Budget', 'label_ar' => 'الميزانية المخصصة', 'format' => 'money'],
            'matter_asked_amount' => ['section' => 'financials', 'label_en' => 'Asked Amount', 'label_ar' => 'المبلغ المطلوب', 'format' => 'money'],
            'matter_judged_amount' => ['section' => 'financials', 'label_en' => 'Judged Amount', 'label_ar' => 'المبلغ المقضي به', 'format' => 'money'],
            'financial_provision' => ['section' => 'financials', 'label_en' => 'Financial Provision', 'label_ar' => 'المخصص المالي', 'format' => 'longtext'],
            'fee_letter' => ['section' => 'financials', 'label_en' => 'Fee Letter', 'label_ar' => 'رسوم الخطاب', 'format' => 'money'],
            'contract_id' => ['section' => 'financials', 'label_en' => 'Contract', 'label_ar' => 'العقد', 'format' => 'fk:contract'],

            // Meta & Audit section
            'matter_shelf' => ['section' => 'meta', 'label_en' => 'Shelf', 'label_ar' => 'الرف', 'format' => 'text'],
            'client_branch' => ['section' => 'meta', 'label_en' => 'Client Branch', 'label_ar' => 'فرع العميل', 'format' => 'text'],
            'matter_branch_id' => ['section' => 'meta', 'label_en' => 'Matter Branch', 'label_ar' => 'فرع المسألة', 'format' => 'fk:option:matterBranch'],
            'matter_destination' => ['section' => 'meta', 'label_en' => 'Matter Destination (legacy)', 'label_ar' => 'جهة المسألة (قديم)', 'format' => 'text'],
            'matter_category' => ['section' => 'meta', 'label_en' => 'Category (legacy)', 'label_ar' => 'الفئة (قديم)', 'format' => 'text'],
            'matter_degree' => ['section' => 'meta', 'label_en' => 'Degree (legacy)', 'label_ar' => 'الدرجة (قديم)', 'format' => 'text'],
            'matter_status' => ['section' => 'meta', 'label_en' => 'Status (legacy)', 'label_ar' => 'الحالة (قديم)', 'format' => 'text'],
            'matter_court_text' => ['section' => 'meta', 'label_en' => 'Court Text (legacy)', 'label_ar' => 'نص المحكمة (قديم)', 'format' => 'text'],
            'matter_circuit_legacy' => ['section' => 'meta', 'label_en' => 'Circuit (legacy)', 'label_ar' => 'الدائرة (قديم)', 'format' => 'raw'],
            'matter_description' => ['section' => 'meta', 'label_en' => 'Description', 'label_ar' => 'الوصف', 'format' => 'longtext'],
            'legal_opinion' => ['section' => 'meta', 'label_en' => 'Legal Opinion', 'label_ar' => 'الرأي القانوني', 'format' => 'longtext'],
            'notes_1' => ['section' => 'meta', 'label_en' => 'Notes 1', 'label_ar' => 'ملاحظات 1', 'format' => 'longtext'],
            'notes_2' => ['section' => 'meta', 'label_en' => 'Notes 2', 'label_ar' => 'ملاحظات 2', 'format' => 'longtext'],
            'engagement_letter_no' => ['section' => 'meta', 'label_en' => 'Engagement Letter No', 'label_ar' => 'رقم خطاب التفويض', 'format' => 'text'],
            'matter_partner_id' => ['section' => 'meta', 'label_en' => 'Matter Partner', 'label_ar' => 'شريك المسألة', 'format' => 'fk:matterPartnerRef'],
            'matter_partner' => ['section' => 'meta', 'label_en' => 'Matter Partner (legacy)', 'label_ar' => 'شريك المسألة (قديم)', 'format' => 'text'],
            'created_by' => ['section' => 'meta', 'label_en' => 'Created By', 'label_ar' => 'تم الإنشاء بواسطة', 'format' => 'fk:user'],
            'updated_by' => ['section' => 'meta', 'label_en' => 'Updated By', 'label_ar' => 'تم التحديث بواسطة', 'format' => 'fk:user'],
            'created_at' => ['section' => 'meta', 'label_en' => 'Created At', 'label_ar' => 'تاريخ الإنشاء', 'format' => 'datetime'],
            'updated_at' => ['section' => 'meta', 'label_en' => 'Updated At', 'label_ar' => 'تاريخ التحديث', 'format' => 'datetime'],
            'client_and_capacity' => ['section' => 'meta', 'label_en' => 'Client and Capacity (legacy)', 'label_ar' => 'العميل والصفة (قديم)', 'format' => 'longtext'],
            'opponent_and_capacity' => ['section' => 'meta', 'label_en' => 'Opponent and Capacity (legacy)', 'label_ar' => 'الخصم والصفة (قديم)', 'format' => 'longtext'],
            'matter_select' => ['section' => 'meta', 'label_en' => 'Matter Select', 'label_ar' => 'اختيار المسألة', 'format' => 'boolean'],
        ];
    }

    public static function bySection(string $section): array
    {
        return array_filter(self::all(), fn($config, $field) => $config['section'] === $section, ARRAY_FILTER_USE_BOTH);
    }

    public static function get(string $field): ?array
    {
        return self::all()[$field] ?? null;
    }
}
