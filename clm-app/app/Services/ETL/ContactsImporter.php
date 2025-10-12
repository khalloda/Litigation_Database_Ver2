<?php

namespace App\Services\ETL;

use App\Models\Client;
use App\Models\Contact;

class ContactsImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'contacts.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'Contacts';
    }

    protected function getColumnMapping(): array
    {
        return [
            'Contact_ID' => 'legacy_id',
            'clientID' => 'legacy_client_id',
            'Contact1' => 'contact_name',
            'Full_name' => 'full_name',
            'Job Title' => 'job_title',
            'Address' => 'address',
            'City' => 'city',
            'State/Province' => 'state',
            'Country/Region' => 'country',
            'ZIP/Postal Code' => 'zip_code',
            'Business Phone' => 'business_phone',
            'Home Phone' => 'home_phone',
            'Mobile Phone' => 'mobile_phone',
            'Fax Number' => 'fax_number',
            'E-mail Address' => 'email',
            'Web Page' => 'web_page',
            'Attachments' => 'attachments',
        ];
    }

    protected function processRow(array $row): void
    {
        $legacyClientId = $this->parseInt($row['legacy_client_id'] ?? null);

        if (!$legacyClientId) {
            throw new \RuntimeException("client_id is required");
        }

        // Find client by ID
        $client = Client::find($legacyClientId);

        if (!$client) {
            throw new \RuntimeException("Client not found: {$legacyClientId}");
        }

        $data = [
            'client_id' => $client->id,
            'contact_name' => $this->cleanString($row['contact_name'] ?? null),
            'full_name' => $this->cleanString($row['full_name'] ?? null),
            'job_title' => $this->cleanString($row['job_title'] ?? null),
            'address' => $this->cleanString($row['address'] ?? null),
            'city' => $this->cleanString($row['city'] ?? null),
            'state' => $this->cleanString($row['state'] ?? null),
            'country' => $this->cleanString($row['country'] ?? null),
            'zip_code' => $this->cleanString($row['zip_code'] ?? null),
            'business_phone' => $this->cleanString($row['business_phone'] ?? null),
            'home_phone' => $this->cleanString($row['home_phone'] ?? null),
            'mobile_phone' => $this->cleanString($row['mobile_phone'] ?? null),
            'fax_number' => $this->cleanString($row['fax_number'] ?? null),
            'email' => $this->cleanString($row['email'] ?? null),
            'web_page' => $this->cleanString($row['web_page'] ?? null),
            'attachments' => $this->cleanString($row['attachments'] ?? null),
        ];

        // Idempotent upsert
        $legacyId = $this->parseInt($row['legacy_id'] ?? null);

        if ($legacyId) {
            $existing = Contact::where('client_id', $client->id)
                ->where(function ($q) use ($data) {
                    $q->where('email', $data['email'])
                      ->orWhere('full_name', $data['full_name']);
                })
                ->whereNotNull($data['email'] ?? $data['full_name'])
                ->first();

            if ($existing) {
                $existing->update($data);
            } else {
                Contact::create($data);
            }
        } else {
            Contact::create($data);
        }
    }
}

