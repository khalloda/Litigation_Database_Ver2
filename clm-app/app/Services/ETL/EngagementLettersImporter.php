<?php

namespace App\Services\ETL;

use App\Models\Client;
use App\Models\EngagementLetter;

class EngagementLettersImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'engagement_letters.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'خطابات_الأتعاب'; // Arabic sheet name
    }

    protected function getColumnMapping(): array
    {
        return [
            'contract_ID' => 'legacy_id',
            'client_ID' => 'legacy_client_id',
            'Client' => 'client_name',
            'Cont-Date' => 'contract_date',
            'Cont-Details' => 'contract_details',
            'Cont-Structure' => 'contract_structure',
            'Cont-Type' => 'contract_type',
            'Matter' => 'matters',
            'Status' => 'status',
            'mfiles_ID' => 'mfiles_id',
        ];
    }

    protected function processRow(array $row): void
    {
        $legacyClientId = $this->parseInt($row['legacy_client_id'] ?? null);

        // Find client by legacy ID (stored in import)
        // For now, we'll match by client_name if available
        $client = null;
        if ($legacyClientId) {
            // Simple approach: use client_id directly if it matches
            $client = Client::find($legacyClientId);
        }

        if (!$client && !empty($row['client_name'])) {
            $client = Client::where('client_name_ar', 'like', '%' . $this->cleanString($row['client_name']) . '%')
                ->orWhere('client_name_en', 'like', '%' . $this->cleanString($row['client_name']) . '%')
                ->first();
        }

        if (!$client) {
            throw new \RuntimeException("Client not found for engagement letter");
        }

        $data = [
            'client_id' => $client->id,
            'client_name' => $this->cleanString($row['client_name'] ?? null),
            'contract_date' => $this->parseDateTime($row['contract_date'] ?? null),
            'contract_details' => $this->cleanString($row['contract_details'] ?? null),
            'contract_structure' => $this->cleanString($row['contract_structure'] ?? null),
            'contract_type' => $this->cleanString($row['contract_type'] ?? null),
            'matters' => $this->cleanString($row['matters'] ?? null),
            'status' => $this->cleanString($row['status'] ?? null),
            'mfiles_id' => $this->parseInt($row['mfiles_id'] ?? null),
        ];

        // Idempotent upsert
        $legacyId = $this->parseInt($row['legacy_id'] ?? null);
        
        if ($legacyId) {
            $existing = EngagementLetter::where('client_id', $client->id)
                ->where('contract_date', $data['contract_date'])
                ->first();

            if ($existing) {
                $existing->update($data);
            } else {
                EngagementLetter::create($data);
            }
        } else {
            EngagementLetter::create($data);
        }
    }
}

