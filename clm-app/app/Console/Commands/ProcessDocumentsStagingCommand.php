<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\ClientDocumentStaging;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessDocumentsStagingCommand extends Command
{
    protected $signature = 'documents:staging-process {--execute : Persist the data instead of performing a dry run}';

    protected $description = 'Convert staged document rows into client_documents (dry run by default)';

    public function handle(): int
    {
        $dryRun = !$this->option('execute');
        $this->info($dryRun ? 'Starting dry run...' : 'Executing import...');

        $total = 0;
        $success = 0;
        $failed = 0;
        $errors = [];

        ClientDocumentStaging::orderBy('id')
            ->chunk(200, function ($rows) use (&$total, &$success, &$failed, &$errors, $dryRun) {
                foreach ($rows as $row) {
                    $total++;

                    $result = $this->processRow($row, $dryRun);

                    if ($result === true) {
                        $success++;
                    } else {
                        $failed++;
                        if (count($errors) < 25) {
                            $errors[] = "Row {$row->id}: {$result}";
                        }
                    }
                }
            });

        $this->line("---");
        $this->info("Total staged rows: {$total}");
        $this->info("Ready: {$success}");
        $this->info("Issues: {$failed}");

        if (!empty($errors)) {
            $this->warn('Sample issues:');
            foreach ($errors as $message) {
                $this->warn(" - {$message}");
            }
        }

        if ($dryRun && $failed === 0) {
            $this->info('Dry run succeeded with zero validation issues. Re-run with --execute to import.');
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function processRow(ClientDocumentStaging $stagingRow, bool $dryRun)
    {
        if (!$stagingRow->legacy_client_id) {
            return 'legacy_client_id missing';
        }

        $client = Client::find($stagingRow->legacy_client_id);
        if (!$client) {
            return "Client {$stagingRow->legacy_client_id} not found";
        }

        if (!$stagingRow->document_description) {
            return 'document_description missing';
        }

        if (!$stagingRow->deposit_date && !$stagingRow->deposit_date_raw) {
            return 'deposit_date missing';
        }

        $payload = [
            'id' => $stagingRow->legacy_document_id,
            'client_id' => $client->id,
            'legacy_document_id' => $stagingRow->legacy_document_id,
            'legacy_matter_name' => $stagingRow->legacy_matter_name,
            'client_name' => $stagingRow->client_name ?? $client->client_name_ar ?? $client->client_name_en,
            'department' => $stagingRow->department,
            'admin_staff' => $stagingRow->admin_staff,
            'lawyer' => $stagingRow->lawyer,
            'responsible_lawyer' => $stagingRow->responsible_lawyer,
            'movement_card' => $stagingRow->movement_card ?? false,
            'document_description' => $stagingRow->document_description,
            'deposit_date' => $this->normalizeDateValue($stagingRow->deposit_date ?? $stagingRow->deposit_date_raw),
            'document_date' => $this->normalizeDateValue($stagingRow->document_date ?? $stagingRow->document_date_raw),
            'pages_count' => $stagingRow->pages_count ?? $stagingRow->pages_count_raw,
            'notes' => $stagingRow->notes,
            'document_location' => $client->documentsLocation?->label,
        ];

        if ($dryRun) {
            return true;
        }

        try {
            DB::transaction(function () use ($payload, $stagingRow) {
                if ($payload['legacy_document_id']) {
                    ClientDocument::updateOrCreate(
                        [
                            'id' => $payload['legacy_document_id'],
                        ],
                        array_merge($payload, [
                            'created_by' => null,
                            'updated_by' => null,
                        ])
                    );
                } else {
                    ClientDocument::updateOrCreate(
                        [
                            'client_id' => $payload['client_id'],
                            'deposit_date' => $payload['deposit_date'],
                            'document_description' => $payload['document_description'],
                        ],
                        array_merge($payload, [
                            'created_by' => null,
                            'updated_by' => null,
                        ])
                    );
                }

                $stagingRow->touch();
            });
        } catch (\Throwable $e) {
            return $e->getMessage();
        }

        return true;
    }

    private function normalizeDateValue($value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}

