<?php

namespace App\Console\Commands;

use App\Services\ETL\AdminTasksImporter;
use Illuminate\Console\Command;

class ImportAdminTasksCommand extends Command
{
    protected $signature = 'import:admin-tasks';
    protected $description = 'Import admin tasks from admin_work_tasks.xlsx';

    public function handle(AdminTasksImporter $importer)
    {
        $this->info('Starting admin tasks import...');
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
