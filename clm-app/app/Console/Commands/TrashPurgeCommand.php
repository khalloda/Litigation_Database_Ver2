<?php

namespace App\Console\Commands;

use App\Models\DeletionBundle;
use App\Services\DeletionBundleService;
use Illuminate\Console\Command;

class TrashPurgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trash:purge
                            {bundle? : Bundle UUID or partial UUID to purge}
                            {--older-than= : Purge bundles older than N days}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge deletion bundles from the recycle bin';

    /**
     * Execute the console command.
     */
    public function handle(DeletionBundleService $service)
    {
        $bundleInput = $this->argument('bundle');
        $olderThan = $this->option('older-than');
        $force = $this->option('force');

        if ($bundleInput) {
            return $this->purgeSingleBundle($bundleInput, $service, $force);
        } elseif ($olderThan) {
            return $this->purgeOldBundles((int) $olderThan, $service, $force);
        } else {
            $this->error('Please specify either a bundle ID or --older-than option');
            return 1;
        }
    }

    /**
     * Purge a single bundle.
     */
    protected function purgeSingleBundle(string $bundleInput, DeletionBundleService $service, bool $force): int
    {
        $bundle = $this->findBundle($bundleInput);

        if (!$bundle) {
            $this->error("Bundle not found: {$bundleInput}");
            return 1;
        }

        $this->info("=== Bundle Information ===");
        $this->line("ID: {$bundle->id}");
        $this->line("Type: {$bundle->root_type}");
        $this->line("Label: {$bundle->root_label}");
        $this->line("Items: {$bundle->cascade_count}");
        $this->line("Status: {$bundle->status}");
        $this->line("Deleted: {$bundle->created_at->format('Y-m-d H:i:s')}");
        $this->newLine();

        if (!$force && !$this->confirm('Permanently purge this bundle?')) {
            $this->info('Purge cancelled.');
            return 0;
        }

        try {
            $service->purgeBundle($bundle->id);
            $this->info("✓ Bundle purged successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("Purge failed: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Purge bundles older than specified days.
     */
    protected function purgeOldBundles(int $days, DeletionBundleService $service, bool $force): int
    {
        $cutoffDate = now()->subDays($days);
        
        $bundles = DeletionBundle::trashed()
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($bundles->isEmpty()) {
            $this->info("No trashed bundles older than {$days} days found.");
            return 0;
        }

        $this->warn("Found {$bundles->count()} bundles older than {$days} days:");
        
        // Show summary by type
        $byType = $bundles->groupBy('root_type');
        foreach ($byType as $type => $items) {
            $this->line("  {$type}: {$items->count()}");
        }
        
        $this->newLine();

        if (!$force && !$this->confirm("Purge all {$bundles->count()} bundles?")) {
            $this->info('Purge cancelled.');
            return 0;
        }

        $purged = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($bundles->count());

        foreach ($bundles as $bundle) {
            try {
                $service->purgeBundle($bundle->id);
                $purged++;
            } catch (\Exception $e) {
                $failed++;
                $this->error("Failed to purge {$bundle->id}: {$e->getMessage()}");
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✓ Purged {$purged} bundles");
        
        if ($failed > 0) {
            $this->warn("⚠ {$failed} bundles failed to purge");
        }

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Find bundle by full or partial UUID.
     */
    protected function findBundle(string $input): ?DeletionBundle
    {
        // Try exact match
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
}
