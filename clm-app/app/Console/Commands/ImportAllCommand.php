<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportAllCommand extends Command
{
    protected $signature = 'import:all';

    protected $description = 'Import all data from Excel files in dependency order';

    public function handle()
    {
        $this->info('🚀 Starting complete data import...');
        $this->info('Import order: Lawyers → Clients → Engagement Letters → Cases → Hearings → Contacts → POAs → Tasks → Subtasks → Documents');
        $this->newLine();

        $startTime = microtime(true);
        $allStats = [];

        // Import in dependency order
        $imports = [
            'lawyers' => 'import:lawyers',
            'clients' => 'import:clients',
            'engagement-letters' => 'import:engagement-letters',
            'contacts' => 'import:contacts',
            'poas' => 'import:poas',
            'cases' => 'import:cases',
            'hearings' => 'import:hearings',
            'admin-tasks' => 'import:admin-tasks',
            'admin-subtasks' => 'import:admin-subtasks',
            'documents' => 'import:documents',
        ];

        foreach ($imports as $name => $command) {
            $this->line("<fg=cyan>Importing {$name}...</>");

            $exitCode = $this->call($command);

            if ($exitCode !== 0) {
                $this->error("✗ {$name} import failed");

                if (!$this->confirm('Continue with remaining imports?')) {
                    return 1;
                }
            } else {
                $this->line("<fg=green>✓ {$name} imported successfully</>");
            }

            $this->newLine();
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info("✅ All imports completed in {$duration} seconds");

        return 0;
    }
}
