<?php

namespace App\Console\Commands;

use App\Services\ETL\ClientsImporter;
use Illuminate\Console\Command;

class ImportClientsCommand extends Command
{
    protected $signature = 'import:clients';
    protected $description = 'Import clients from clients.xlsx';

    public function handle(ClientsImporter $importer)
    {
        $this->info('Starting clients import...');
        $this->newLine();

        try {
            $stats = $importer->import();

            $this->info('=== Import Complete ===');
            $this->line("File: {$stats['file']}");
            $this->line("Total Rows: {$stats['total']}");
            $this->line("<fg=green>Success: {$stats['success']}</>");
            $this->line("<fg=red>Failed: {$stats['failed']}</>");
            $this->line("Success Rate: {$stats['success_rate']}%");

            if ($stats['failed'] > 0) {
                $this->newLine();
                $this->warn("⚠ {$stats['failed']} rows failed. Check reject log in storage/app/imports/rejects/");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
