<?php

namespace App\Services\ETL;

use App\Models\CaseModel;
use App\Models\Client;
use App\Models\ClientDocument;

class DocumentsImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'clients_matters_documents.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'المستندات'; // Arabic sheet name
    }

    protected function getColumnMapping(): array
    {
        return [
            'document_id' => 'legacy_id',
            'client_ID' => 'legacy_client_id',
            'العميل' => 'client_name',
            'المحامي/الموظف المسئول' => 'responsible_lawyer',
            'بطاقة الحركة' => 'movement_card',
            'بيان المستند' => 'document_description',
            'تاريخ الإيداع' => 'deposit_date',
            'تاريخ المستند' => 'document_date',
            'رقم الدعوى' => 'case_number',
            'عدد الأوراق' => 'pages_count',
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

        // Try to match case by case_number
        $matterId = null;
        $caseNumber = $this->cleanString($row['case_number'] ?? null);
        
        if ($caseNumber) {
            $case = CaseModel::where('client_id', $client->id)
                ->where(function ($q) use ($caseNumber) {
                    $q->where('matter_name_ar', 'like', '%' . $caseNumber . '%')
                      ->orWhere('matter_name_en', 'like', '%' . $caseNumber . '%');
                })
                ->first();
            
            $matterId = $case?->id;
        }

        $data = [
            'client_id' => $client->id,
            'matter_id' => $matterId,
            'client_name' => $this->cleanString($row['client_name'] ?? null),
            'responsible_lawyer' => $this->cleanString($row['responsible_lawyer'] ?? null),
            'movement_card' => $this->parseBoolean($row['movement_card'] ?? false),
            'document_description' => $this->cleanString($row['document_description'] ?? null),
            'deposit_date' => $this->parseDate($row['deposit_date'] ?? null),
            'document_date' => $this->parseDate($row['document_date'] ?? null),
            'case_number' => $caseNumber,
            'pages_count' => $this->cleanString($row['pages_count'] ?? null),
            'notes' => $this->cleanString($row['notes'] ?? null),
        ];

        // Validation
        if (empty($data['document_description'])) {
            throw new \RuntimeException("document_description is required");
        }

        if (empty($data['deposit_date'])) {
            throw new \RuntimeException("deposit_date is required");
        }

        // Idempotent upsert
        $legacyId = $this->parseInt($row['legacy_id'] ?? null);

        if ($legacyId) {
            $existing = ClientDocument::where('client_id', $client->id)
                ->where('deposit_date', $data['deposit_date'])
                ->where('document_description', $data['document_description'])
                ->first();

            if ($existing) {
                $existing->update($data);
            } else {
                ClientDocument::create($data);
            }
        } else {
            ClientDocument::create($data);
        }
    }
}

