<?php

namespace App\Console\Commands;

use App\Services\ETL\CasesImporter;
use Illuminate\Console\Command;

class ImportCasesCommand extends Command
{
    protected $signature = 'import:cases';
    protected $description = 'Import cases from cases.xlsx';

    public function handle(CasesImporter $importer)
    {
        $this->info('Starting cases import (this may take a while for ~2000 rows)...');
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
