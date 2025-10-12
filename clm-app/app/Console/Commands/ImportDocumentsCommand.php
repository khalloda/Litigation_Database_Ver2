<?php

namespace App\Console\Commands;

use App\Services\ETL\DocumentsImporter;
use Illuminate\Console\Command;

class ImportDocumentsCommand extends Command
{
    protected $signature = 'import:documents';
    protected $description = 'Import client documents from clients_matters_documents.xlsx';

    public function handle(DocumentsImporter $importer)
    {
        $this->info('Starting documents import...');
        $this->newLine();

        try {
            $stats = $importer->import();
            $this->displayStats($stats);
            return 0;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return 1;
        }
    }

    protected function displayStats(array $stats)
    {
        $this->info('=== Import Complete ===');
        $this->line("File: {$stats['file']}");
        $this->line("Total Rows: {$stats['total']}");
        $this->line("<fg=green>Success: {$stats['success']}</>");
        $this->line("<fg=red>Failed: {$stats['failed']}</>");
        $this->line("Success Rate: {$stats['success_rate']}%");

        if ($stats['failed'] > 0) {
            $this->newLine();
            $this->warn("⚠ {$stats['failed']} rows failed. Check reject log.");
        }
    }
}
