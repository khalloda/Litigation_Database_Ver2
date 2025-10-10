<?php

namespace App\Console\Commands;

use App\Services\ETL\EngagementLettersImporter;
use Illuminate\Console\Command;

class ImportEngagementLettersCommand extends Command
{
    protected $signature = 'import:engagement-letters';
    protected $description = 'Import engagement letters from engagement_letters.xlsx';

    public function handle(EngagementLettersImporter $importer)
    {
        $this->info('Starting engagement letters import...');
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
            return 1;
        }
    }
}
