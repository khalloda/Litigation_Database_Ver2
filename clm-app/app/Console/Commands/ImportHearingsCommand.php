<?php

namespace App\Console\Commands;

use App\Services\ETL\HearingsImporter;
use Illuminate\Console\Command;

class ImportHearingsCommand extends Command
{
    protected $signature = 'import:hearings';
    protected $description = 'Import hearings from hearings.xlsx (largest file ~5000 rows)';

    public function handle(HearingsImporter $importer)
    {
        $this->info('Starting hearings import (this may take a while for ~5000 rows)...');
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
