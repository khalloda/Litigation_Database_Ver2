<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearHearingsStagingCommand extends Command
{
    protected $signature = 'hearings:clear-staging {--force}';
    protected $description = 'Clear all data from hearings_staging table';

    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to clear the hearings_staging table?', true)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            $count = DB::table('hearings_staging')->count();
            DB::table('hearings_staging')->truncate();
            $this->info("✓ Cleared {$count} rows from hearings_staging table");
            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to clear table: {$e->getMessage()}");
            return 1;
        }
    }
}

