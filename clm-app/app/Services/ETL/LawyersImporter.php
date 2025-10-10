<?php

namespace App\Services\ETL;

use App\Models\Lawyer;
use Illuminate\Support\Facades\DB;

class LawyersImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'lawyers.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'lawyers';
    }

    protected function getColumnMapping(): array
    {
        return [
            'Lawyer_ID' => 'legacy_id',
            'lawyer_name_ar' => 'lawyer_name_ar',
            'lawyer_name_en' => 'lawyer_name_en',
            'lawyer_name_title' => 'lawyer_name_title',
            'lawyer_email' => 'lawyer_email',
            'AttTrack' => 'attendance_track',
        ];
    }

    protected function processRow(array $row): void
    {
        $data = [
            'lawyer_name_ar' => $this->cleanString($row['lawyer_name_ar'] ?? null),
            'lawyer_name_en' => $this->cleanString($row['lawyer_name_en'] ?? null),
            'lawyer_name_title' => $this->cleanString($row['lawyer_name_title'] ?? null),
            'lawyer_email' => $this->cleanString($row['lawyer_email'] ?? null),
            'attendance_track' => $this->parseBoolean($row['attendance_track'] ?? false),
        ];

        // Validation
        if (empty($data['lawyer_name_ar'])) {
            throw new \RuntimeException("lawyer_name_ar is required");
        }

        // Idempotent upsert - PRESERVE ORIGINAL ID for FK integrity
        $legacyId = $this->parseInt($row['legacy_id'] ?? null);

        if ($legacyId) {
            $existing = Lawyer::find($legacyId);

            if ($existing) {
                $existing->update($data);
            } else {
                // Preserve original ID
                $data['id'] = $legacyId;
                Lawyer::create($data);
            }
        } else {
            Lawyer::create($data);
        }
    }
}

