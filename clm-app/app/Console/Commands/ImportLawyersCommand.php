<?php

namespace App\Console\Commands;

use App\Services\ETL\LawyersImporter;
use Illuminate\Console\Command;

class ImportLawyersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:lawyers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import lawyers from lawyers.xlsx';

    /**
     * Execute the console command.
     */
    public function handle(LawyersImporter $importer)
    {
        $this->info('Starting lawyers import...');
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
