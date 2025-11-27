<?php

namespace App\Console\Commands;

use App\Models\DeletionBundle;
use App\Services\DeletionBundleService;
use Illuminate\Console\Command;

class TrashRestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trash:restore
                            {bundle : Bundle UUID or partial UUID}
                            {--dry-run : Simulate restoration without applying changes}
                            {--resolve-conflicts=skip : How to handle ID conflicts (skip|overwrite|new_copy)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a deletion bundle from the recycle bin';

    /**
     * Execute the console command.
     */
    public function handle(DeletionBundleService $service)
    {
        $bundleInput = $this->argument('bundle');
        
        // Find bundle by full or partial UUID
        $bundle = $this->findBundle($bundleInput);
        
        if (!$bundle) {
            $this->error("Bundle not found: {$bundleInput}");
            return 1;
        }

        if (!$bundle->isTrashed()) {
            $this->warn("Bundle {$bundle->id} is not in trashed status (current: {$bundle->status})");
            
            if (!$this->confirm('Continue anyway?')) {
                return 0;
            }
        }

        // Show bundle info
        $this->info("=== Bundle Information ===");
        $this->line("ID: {$bundle->id}");
        $this->line("Type: {$bundle->root_type}");
        $this->line("Label: {$bundle->root_label}");
        $this->line("Items: {$bundle->cascade_count}");
        $this->line("Deleted: {$bundle->created_at->format('Y-m-d H:i:s')} by {$bundle->deletedBy->name}");
        
        if ($bundle->reason) {
            $this->line("Reason: {$bundle->reason}");
        }
        
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $conflictStrategy = $this->option('resolve-conflicts');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be applied');
        }

        if (!$dryRun && !$this->confirm("Restore this bundle?")) {
            $this->info('Restoration cancelled.');
            return 0;
        }

        // Perform restoration
        $this->info('Starting restoration...');
        
        try {
            $report = $service->restoreBundle($bundle->id, [
                'dry_run' => $dryRun,
                'resolve_conflicts' => $conflictStrategy,
            ]);

            $this->displayRestoreReport($report);

            if ($dryRun) {
                $this->newLine();
                $this->info('Dry run completed. Run without --dry-run to apply changes.');
            } else {
                $this->newLine();
                $this->info('✓ Bundle restored successfully!');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Restoration failed: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Find bundle by full or partial UUID.
     */
    protected function findBundle(string $input): ?DeletionBundle
    {
        // Try exact match first
        $bundle = DeletionBundle::find($input);
        
        if ($bundle) {
            return $bundle;
        }

        // Try partial match
        $matches = DeletionBundle::where('id', 'like', "{$input}%")->get();
        
        if ($matches->count() === 1) {
            return $matches->first();
        }

        if ($matches->count() > 1) {
            $this->warn("Multiple bundles match '{$input}':");
            foreach ($matches as $match) {
                $this->line("  {$match->id} - {$match->root_label}");
            }
        }

        return null;
    }

    /**
     * Display restore report.
     */
    protected function displayRestoreReport(array $report)
    {
        $this->newLine();
        $this->info('=== Restore Report ===');
        $this->line("Root Type: {$report['root_type']}");
        $this->line("Root Label: {$report['root_label']}");
        $this->line("Conflict Strategy: {$report['conflict_strategy']}");
        $this->newLine();

        $this->line("<fg=green>Restored:</> " . count($report['restored']));
        $this->line("<fg=yellow>Skipped:</> " . count($report['skipped']));
        $this->line("<fg=red>Errors:</> " . count($report['errors']));

        if (!empty($report['conflicts'])) {
            $this->newLine();
            $this->warn('Conflicts detected:');
            foreach ($report['conflicts'] as $conflict) {
                $this->line("  {$conflict['model']} #{$conflict['id']}");
            }
        }

        if (!empty($report['errors'])) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($report['errors'] as $error) {
                $this->line("  {$error['entity']}: {$error['error']}");
            }
        }
    }
}
