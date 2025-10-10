<?php

namespace App\Services\ETL;

use App\Models\CaseModel;
use App\Models\Hearing;
use App\Models\Lawyer;

class HearingsImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'hearings.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'الجلسات'; // Arabic sheet name
    }

    protected function getColumnMapping(): array
    {
        return [
            'hearings_id' => 'legacy_id',
            'matter_id' => 'legacy_matter_id',
            'date' => 'date',
            'الإجراء' => 'procedure',
            'المحكمة' => 'court',
            'الدائرة' => 'circuit',
            'الجهة' => 'destination',
            'decision' => 'decision',
            'shortDecision' => 'short_decision',
            'lastDecision' => 'last_decision',
            'nextHearing' => 'next_hearing',
            'report' => 'report',
            'إخطار العميل بالقرار' => 'notify_client',
            'الحاضر' => 'attendee',
            'حاضر 1' => 'attendee_1',
            'حاضر 2' => 'attendee_2',
            'حاضر 3' => 'attendee_3',
            'حاضر 4' => 'attendee_4',
            'حضور الجلسة القادمة' => 'next_attendee',
            'صالح/ضد' => 'evaluation',
            'ملاحظات' => 'notes',
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
            'date' => $this->parseDate($row['date'] ?? null),
            'procedure' => $this->cleanString($row['procedure'] ?? null),
            'court' => $this->cleanString($row['court'] ?? null),
            'circuit' => $this->cleanString($row['circuit'] ?? null),
            'destination' => $this->cleanString($row['destination'] ?? null),
            'decision' => $this->cleanString($row['decision'] ?? null),
            'short_decision' => $this->cleanString($row['short_decision'] ?? null),
            'last_decision' => $this->cleanString($row['last_decision'] ?? null),
            'next_hearing' => $this->parseDate($row['next_hearing'] ?? null),
            'report' => $this->parseBoolean($row['report'] ?? false),
            'notify_client' => $this->parseBoolean($row['notify_client'] ?? false),
            'attendee' => $this->cleanString($row['attendee'] ?? null),
            'attendee_1' => $this->cleanString($row['attendee_1'] ?? null),
            'attendee_2' => $this->cleanString($row['attendee_2'] ?? null),
            'attendee_3' => $this->cleanString($row['attendee_3'] ?? null),
            'attendee_4' => $this->cleanString($row['attendee_4'] ?? null),
            'next_attendee' => $this->cleanString($row['next_attendee'] ?? null),
            'evaluation' => $this->cleanString($row['evaluation'] ?? null),
            'notes' => $this->cleanString($row['notes'] ?? null),
        ];

        // Idempotent upsert by legacy hearing ID
        $legacyId = $this->parseInt($row['legacy_id'] ?? null);

        if ($legacyId) {
            $existing = Hearing::where('matter_id', $case->id)
                ->where('date', $data['date'])
                ->whereNotNull($data['date'])
                ->first();

            if ($existing) {
                $existing->update($data);
            } else {
                Hearing::create($data);
            }
        } else {
            Hearing::create($data);
        }
    }
}

