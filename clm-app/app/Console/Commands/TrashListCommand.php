<?php

namespace App\Console\Commands;

use App\Models\DeletionBundle;
use Illuminate\Console\Command;

class TrashListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trash:list
                            {--type= : Filter by root type (Client, Case, Document, etc.)}
                            {--status= : Filter by status (trashed, restored, purged)}
                            {--limit=20 : Number of results to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List deletion bundles in the recycle bin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = DeletionBundle::with('deletedBy')->orderBy('created_at', 'desc');

        // Apply filters
        if ($type = $this->option('type')) {
            $query->ofType($type);
        }

        if ($status = $this->option('status')) {
            switch ($status) {
                case 'trashed':
                    $query->trashed();
                    break;
                case 'restored':
                    $query->restored();
                    break;
                case 'purged':
                    $query->purged();
                    break;
            }
        }

        $limit = (int) $this->option('limit');
        $bundles = $query->limit($limit)->get();

        if ($bundles->isEmpty()) {
            $this->info('No deletion bundles found.');
            return 0;
        }

        // Display summary statistics
        $this->displayStatistics();

        // Display table
        $headers = ['Bundle ID', 'Type', 'Label', 'Items', 'Deleted By', 'Deleted At', 'Status'];
        $rows = $bundles->map(function ($bundle) {
            return [
                substr($bundle->id, 0, 8) . '...',
                $bundle->root_type,
                \Illuminate\Support\Str::limit($bundle->root_label, 40),
                $bundle->cascade_count,
                $bundle->deletedBy->name ?? 'Unknown',
                $bundle->created_at->diffForHumans(),
                $bundle->status,
            ];
        })->toArray();

        $this->table($headers, $rows);

        $this->newLine();
        $this->info("Showing {$bundles->count()} of " . DeletionBundle::count() . " total bundles");
        $this->info("Use --type=<Type> to filter by model type");
        $this->info("Use --status=<Status> to filter by status");

        return 0;
    }

    /**
     * Display summary statistics.
     */
    protected function displayStatistics()
    {
        $this->info('=== Deletion Bundle Statistics ===');
        $this->newLine();

        // Count by type
        $byType = DeletionBundle::selectRaw('root_type, count(*) as count')
            ->groupBy('root_type')
            ->get();

        $this->line('<fg=cyan>By Type:</>');
        foreach ($byType as $stat) {
            $this->line("  {$stat->root_type}: {$stat->count}");
        }

        $this->newLine();

        // Count by status
        $byStatus = DeletionBundle::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        $this->line('<fg=cyan>By Status:</>');
        foreach ($byStatus as $stat) {
            $this->line("  {$stat->status}: {$stat->count}");
        }

        $this->newLine();
    }
}
