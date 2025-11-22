<?php

namespace App\Services\ETL;

use App\Models\AdminSubtask;
use App\Models\AdminTask;
use App\Models\Lawyer;

class AdminSubtasksImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'admin_work_subtasks.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'إجراءات_المهام'; // Arabic sheet name
    }

    protected function getColumnMapping(): array
    {
        return [
            'Subtask_ID' => 'legacy_id',
            'Task_ID' => 'legacy_task_id',
            'Lawyer_ID' => 'legacy_lawyer_id',
            'القائم بالعمل' => 'performer',
            'الموعد القادم' => 'next_date',
            'النتيجة' => 'result',
            'تاريخ الإجراء' => 'procedure_date',
            'تقرير' => 'report',
        ];
    }

    protected function processRow(array $row): void
    {
        $legacyTaskId = $this->parseInt($row['legacy_task_id'] ?? null);

        // Some rows have null task_id (1% per data dict), skip them
        if (!$legacyTaskId) {
            throw new \RuntimeException("task_id is required (null found)");
        }

        $task = AdminTask::find($legacyTaskId);

        if (!$task) {
            throw new \RuntimeException("Admin task not found: {$legacyTaskId}");
        }

        // Try to find lawyer
        $lawyerId = null;
        $legacyLawyerId = $this->parseInt($row['legacy_lawyer_id'] ?? null);
        
        if ($legacyLawyerId) {
            $lawyer = Lawyer::skip(0)->take(50)->get()->firstWhere('id', $legacyLawyerId);
            $lawyerId = $lawyer?->id;
        }

        $data = [
            'task_id' => $task->id,
            'lawyer_id' => $lawyerId,
            'performer' => $this->cleanString($row['performer'] ?? null),
            'next_date' => $this->parseDate($row['next_date'] ?? null),
            'result' => $this->cleanString($row['result'] ?? null),
            'procedure_date' => $this->parseDate($row['procedure_date'] ?? null),
            'report' => $this->parseBoolean($row['report'] ?? false),
        ];

        // Idempotent upsert by legacy subtask ID
        $legacyId = $this->parseInt($row['legacy_id'] ?? null);

        if ($legacyId) {
            $existing = AdminSubtask::find($legacyId);

            if ($existing) {
                $existing->update($data);
            } else {
                $data['id'] = $legacyId;
                AdminSubtask::create($data);
            }
        } else {
            AdminSubtask::create($data);
        }
    }
}

