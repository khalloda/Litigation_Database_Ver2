<?php

namespace App\Services\ETL;

use App\Models\Client;
use App\Models\PowerOfAttorney;

class POAsImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'power_of_attorneys.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'التوكيلات'; // Arabic sheet name
    }

    protected function getColumnMapping(): array
    {
        return [
            'client_ID' => 'legacy_client_id',
            'ClientPrintName' => 'client_print_name',
            'اسم الموكل' => 'principal_name',
            'السنة' => 'year',
            'الصفة' => 'capacity',
            'المحامون الصادر لهم التوكيل' => 'authorized_lawyers',
            'تاريخ الإصدار' => 'issue_date',
            'جرد' => 'inventory',
            'جهة الإصدار' => 'issuing_authority',
            'حرف' => 'letter',
            'رقم التوكيل' => 'poa_number',
            'صفة الموكل بالتوكيل' => 'principal_capacity',
            'عدد النسخ' => 'copies_count',
            'مسلسل' => 'serial',
            'ملاحظات' => 'notes',
        ];
    }

    protected function processRow(array $row): void
    {
        $legacyClientId = $this->parseInt($row['legacy_client_id'] ?? null);

        if (!$legacyClientId) {
            throw new \RuntimeException("client_id is required");
        }

        $client = Client::find($legacyClientId);

        if (!$client) {
            throw new \RuntimeException("Client not found: {$legacyClientId}");
        }

        $data = [
            'client_id' => $client->id,
            'client_print_name' => $this->cleanString($row['client_print_name'] ?? null),
            'principal_name' => $this->cleanString($row['principal_name'] ?? null),
            'year' => $this->parseInt($row['year'] ?? null),
            'capacity' => $this->cleanString($row['capacity'] ?? null),
            'authorized_lawyers' => $this->cleanString($row['authorized_lawyers'] ?? null),
            'issue_date' => $this->parseDate($row['issue_date'] ?? null),
            'inventory' => $this->parseBoolean($row['inventory'] ?? true),
            'issuing_authority' => $this->cleanString($row['issuing_authority'] ?? null),
            'letter' => $this->cleanString($row['letter'] ?? null),
            'poa_number' => $this->parseInt($row['poa_number'] ?? null),
            'principal_capacity' => $this->cleanString($row['principal_capacity'] ?? null),
            'copies_count' => $this->parseInt($row['copies_count'] ?? null),
            'serial' => $this->cleanString($row['serial'] ?? null),
            'notes' => $this->cleanString($row['notes'] ?? null),
        ];

        // Validation
        if (empty($data['principal_name'])) {
            throw new \RuntimeException("principal_name is required");
        }

        // Idempotent upsert by client + poa_number + year
        $existing = PowerOfAttorney::where('client_id', $client->id)
            ->where('poa_number', $data['poa_number'])
            ->where('year', $data['year'])
            ->whereNotNull($data['poa_number'])
            ->first();

        if ($existing) {
            $existing->update($data);
        } else {
            PowerOfAttorney::create($data);
        }
    }
}

