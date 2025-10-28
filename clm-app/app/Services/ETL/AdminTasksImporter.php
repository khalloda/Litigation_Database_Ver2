<?php

namespace App\Services\ETL;

use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Lawyer;

class AdminTasksImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'admin_work_tasks.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'admin_work_table';
    }

    protected function getColumnMapping(): array
    {
        return [
            'Task_ID' => 'legacy_id',
            'matter_ID' => 'legacy_matter_id',
            'آخر متابعة' => 'last_follow_up',
            'آخر موعد' => 'last_date',
            'الجهة' => 'authority',
            'الحالة' => 'status',
            'الدائرة' => 'circuit',
            'العمل المطلوب' => 'required_work',
            'القائم بالعمل' => 'performer',
            'القرار السابق' => 'previous_decision',
            'المحكمة' => 'court',
            'النتيجة' => 'result',
            'تاريخ الإنشاء' => 'creation_date',
            'تاريخ التنفيذ' => 'execution_date',
            'تنبيه' => 'alert',
        ];
    }

    protected function processRow(array $row): void
    {
        $legacyMatterId = $this->parseInt($row['legacy_matter_id'] ?? null);

        if (!$legacyMatterId) {
            throw new \RuntimeException("matter_id is required");
        }

        $case = CaseModel::find($legacyMatterId);

        if (!$case) {
            throw new \RuntimeException("Case not found: {$legacyMatterId}");
        }

        $data = [
            'matter_id' => $case->id,
            'lawyer_id' => null, // Will be mapped later if needed
            'last_follow_up' => $this->cleanString($row['last_follow_up'] ?? null),
            'last_date' => $this->parseDate($row['last_date'] ?? null),
            'authority' => $this->cleanString($row['authority'] ?? null),
            'status' => $this->cleanString($row['status'] ?? null),
            'circuit' => $this->cleanString($row['circuit'] ?? null),
            'required_work' => $this->cleanString($row['required_work'] ?? null),
            'performer' => $this->cleanString($row['performer'] ?? null),
            'previous_decision' => $this->cleanString($row['previous_decision'] ?? null),
            'court' => $this->cleanString($row['court'] ?? null),
            'result' => $this->cleanString($row['result'] ?? null),
            'creation_date' => $this->parseDateTime($row['creation_date'] ?? null),
            'execution_date' => $this->parseDateTime($row['execution_date'] ?? null),
            'alert' => $this->parseBoolean($row['alert'] ?? false),
        ];

        // Idempotent upsert by legacy task ID
        $legacyId = $this->parseInt($row['legacy_id'] ?? null);

        if ($legacyId) {
            $existing = AdminTask::find($legacyId);

            if ($existing) {
                $existing->update($data);
            } else {
                // Try to create with specific ID
                $data['id'] = $legacyId;
                AdminTask::create($data);
            }
        } else {
            AdminTask::create($data);
        }
    }
}

