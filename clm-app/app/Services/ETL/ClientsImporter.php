<?php

namespace App\Services\ETL;

use App\Models\Client;

class ClientsImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'clients.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'العملاء'; // Arabic sheet name
    }

    protected function getColumnMapping(): array
    {
        return [
            'client_ID' => 'legacy_id',
            'ClientName_ar' => 'client_name_ar',
            'ClientName_en' => 'client_name_en',
            'ClientPrintName' => 'client_print_name',
            'Status' => 'status',
            'Cash/probono' => 'cash_or_probono',
            'clientStart' => 'client_start',
            'clientEnd' => 'client_end',
            'contactLawyer' => 'contact_lawyer',
            'logo' => 'logo',
            'مكان التوكيل' => 'power_of_attorney_location',
            'مكان المستندات' => 'documents_location',
        ];
    }

    protected function processRow(array $row): void
    {
        $data = [
            'client_name_ar' => $this->cleanString($row['client_name_ar'] ?? null),
            'client_name_en' => $this->cleanString($row['client_name_en'] ?? null),
            'client_print_name' => $this->cleanString($row['client_print_name'] ?? null),
            'status' => $this->cleanString($row['status'] ?? 'Active'),
            'cash_or_probono' => $this->cleanString($row['cash_or_probono'] ?? null),
            'client_start' => $this->parseDate($row['client_start'] ?? null),
            'client_end' => $this->parseDate($row['client_end'] ?? null),
            'contact_lawyer' => $this->cleanString($row['contact_lawyer'] ?? null),
            'logo' => $this->cleanString($row['logo'] ?? null),
            'power_of_attorney_location' => $this->cleanString($row['power_of_attorney_location'] ?? 'الخزينة'),
            'documents_location' => $this->cleanString($row['documents_location'] ?? null),
        ];

        // Validation
        if (empty($data['client_name_ar'])) {
            throw new \RuntimeException("client_name_ar is required");
        }

        if (empty($data['client_print_name'])) {
            $data['client_print_name'] = $data['client_name_ar'];
        }

        // Idempotent upsert based on legacy ID or unique name
        $legacyId = $this->parseInt($row['legacy_id'] ?? null);

        if ($legacyId) {
            $client = Client::where('client_name_ar', $data['client_name_ar'])
                ->where('client_print_name', $data['client_print_name'])
                ->first();

            if ($client) {
                $client->update($data);
            } else {
                Client::create($data);
            }
        } else {
            Client::create($data);
        }
    }
}

