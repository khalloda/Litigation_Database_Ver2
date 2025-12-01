<?php

namespace App\Console\Commands;

use App\Models\ClientDocumentStaging;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportDocumentsStagingCommand extends Command
{
    protected $signature = 'documents:staging-import
        {--path=DocumentsImport/Documents-Original.csv : Relative path to the CSV file}
        {--keep-existing : Append instead of truncating the staging table}';

    protected $description = 'Load Documents-Original.csv rows into client_documents_staging for validation/dry runs';

    public function handle(): int
    {
        $path = base_path($this->option('path'));

        if (!file_exists($path)) {
            $this->error("CSV file not found at {$path}");
            return self::FAILURE;
        }

        if (!$this->option('keep-existing')) {
            DB::table('client_documents_staging')->truncate();
            $this->info('Truncated client_documents_staging');
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->error('Unable to open CSV file for reading.');
            return self::FAILURE;
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            $this->error('CSV file is empty.');
            fclose($handle);
            return self::FAILURE;
        }

        $headers = array_map(function ($value) {
            $value = trim($value, " \t\n\r\0\x0B\"");
            return preg_replace('/^\xEF\xBB\xBF/', '', $value);
        }, $headers);
        $total = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = $this->mapRow($headers, $row);

            ClientDocumentStaging::create($data);
            $total++;

            if ($total % 250 === 0) {
                $this->info("Imported {$total} rows...");
            }
        }

        fclose($handle);

        $this->info("Completed staging import for {$total} rows.");
        return self::SUCCESS;
    }

    private function mapRow(array $headers, array $row): array
    {
        $payload = [];
        foreach ($headers as $index => $header) {
            $payload[$header] = $row[$index] ?? null;
        }

        $documentIdRaw = $payload['document_id'] ?? $payload["\ufeffdocument_id"] ?? null;
        $documentDateRaw = $payload['document_date'] ?? null;
        $depositDateRaw = $payload['deposit_date'] ?? null;
        $pagesRaw = $payload['pages_count'] ?? null;

        $documentId = $this->toInt($documentIdRaw);

        return [
            'id' => $documentId,
            'legacy_document_id' => $documentId,
            'legacy_client_id' => $this->toInt($payload['client_id'] ?? null),
            'client_name' => $this->cleanString($payload['client_name'] ?? null),
            'legacy_matter_name' => $this->cleanString($payload['matter_id'] ?? null),
            'document_description' => $this->cleanString($payload['document_description'] ?? null),
            'document_date_raw' => $documentDateRaw,
            'document_date' => $this->parseDate($documentDateRaw),
            'pages_count_raw' => $pagesRaw,
            'pages_count' => $this->cleanString($pagesRaw),
            'deposit_date_raw' => $depositDateRaw,
            'deposit_date' => $this->parseDate($depositDateRaw),
            'department' => $this->cleanString($payload['department'] ?? null),
            'admin_staff' => $this->cleanString($payload['admin_staff'] ?? null),
            'lawyer' => $this->cleanString($payload['lawyer'] ?? null),
            'responsible_lawyer' => $this->cleanString($payload["responsible_lawyer\t"] ?? $payload['responsible_lawyer'] ?? null),
            'notes' => $this->cleanString($payload['notes'] ?? null),
            'movement_card_raw' => $payload["movement_card\t"] ?? $payload['movement_card'] ?? null,
            'movement_card' => $this->parseBoolean($payload["movement_card\t"] ?? $payload['movement_card'] ?? null),
            'raw_payload' => $payload,
        ];
    }

    private function cleanString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function toInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function parseBoolean($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'yes'], true);
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }
    }
}

