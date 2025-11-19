<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HearingsCommitStagingCommand extends Command
{
    protected $signature = 'hearings:commit-staging {--batch=2000} {--dry-run}';
    protected $description = 'Commit hearings_staging to hearings table with backups and batch processing';

    public function handle(): int
    {
        // Set UTF-8 encoding
        mb_internal_encoding('UTF-8');

        $batchSize = (int)$this->option('batch');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting commit from hearings_staging to hearings...');
        $this->newLine();

        // Step 1: Re-verify gate conditions
        $this->info('Step 1: Re-verifying gate conditions...');
        if (!$this->verifyGateConditions()) {
            $this->error('Gate conditions failed. Aborting commit.');
            return 1;
        }
        $this->line('  ✓ Gate conditions passed');

        // Step 2: Create backup
        if (!$dryRun) {
            $this->info('Step 2: Creating database backup...');
            $backupPath = $this->createBackup();
            $this->line("  ✓ Backup created: {$backupPath}");
        } else {
            $this->line('  ⏭ Skipped (dry-run)');
        }

        // Step 3: Count rows to migrate
        $totalRows = DB::table('hearings_staging')->count();
        $this->info("Step 3: Found {$totalRows} rows to migrate");

        if ($totalRows === 0) {
            $this->warn('No rows to migrate');
            return 0;
        }

        // Step 4: Batch processing
        $this->info("Step 4: Migrating in batches of {$batchSize}...");
        $totalInserted = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $failed = 0;
        $batches = ceil($totalRows / $batchSize);

        for ($batch = 0; $batch < $batches; $batch++) {
            $offset = $batch * $batchSize;
            $this->line("  Processing batch " . ($batch + 1) . "/{$batches}...");

            try {
                if ($dryRun) {
                    // Dry run: just count
                    $batchRows = DB::table('hearings_staging')
                        ->offset($offset)
                        ->limit($batchSize)
                        ->count();
                    $totalInserted += $batchRows;
                } else {
                    // Actual migration
                    $batchResult = $this->migrateBatch($offset, $batchSize);
                    $totalInserted += $batchResult['inserted'];
                    $totalUpdated += $batchResult['updated'];
                    $totalSkipped += $batchResult['skipped'];
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("  ✗ Batch " . ($batch + 1) . " failed: {$e->getMessage()}");
                if (!$dryRun) {
                    $this->error('  Aborting - staging table left intact');
                    return 1;
                }
            }
        }

        // Step 5: Display summary
        $this->displaySummary($totalInserted, $totalUpdated, $totalSkipped, $failed, $dryRun);

        // Step 6: Sample diff (first 10 records)
        if (!$dryRun && $totalInserted > 0) {
            $this->displaySampleDiff();
        }

        return 0;
    }

    private function verifyGateConditions(): bool
    {
        // Check for invalid dates (0000-00-00)
        $invalidDate = DB::table('hearings_staging')
            ->where('date', '0000-00-00')
            ->count();

        $invalidNext = DB::table('hearings_staging')
            ->where('next_hearing', '0000-00-00')
            ->count();

        if ($invalidDate > 0 || $invalidNext > 0) {
            $this->error("  ✗ Found {$invalidDate} invalid dates and {$invalidNext} invalid next_hearing dates (0000-00-00)");
            return false;
        }

        // Check for orphaned matter_ids
        $orphanedMatterIds = DB::table('hearings_staging', 'stg')
            ->leftJoin('cases', 'stg.matter_id', '=', 'cases.id')
            ->whereNull('cases.id')
            ->whereNotNull('stg.matter_id')
            ->count();

        if ($orphanedMatterIds > 0) {
            $this->error("  ✗ Found {$orphanedMatterIds} orphaned matter_ids");
            return false;
        }

        return true;
    }

    /**
     * Check if hearings table has external_id column.
     */
    private function hasExternalIdColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn === null) {
            $columns = DB::select("SHOW COLUMNS FROM hearings LIKE 'external_id'");
            $hasColumn = !empty($columns);
        }
        return $hasColumn;
    }

    private function createBackup(): string
    {
        $backupDir = storage_path('app/backup');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = date('Ymd-His');
        $backupFile = "{$backupDir}/hearings_backup_{$timestamp}.sql";

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s hearings cases lawyers > %s 2>&1',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($backupFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \RuntimeException("Backup failed: " . implode("\n", $output));
        }

        return $backupFile;
    }

    private function migrateBatch(int $offset, int $limit): int
    {
        return DB::transaction(function () use ($offset, $limit) {
            // Get batch from staging
            $stagingRows = DB::table('hearings_staging')
                ->offset($offset)
                ->limit($limit)
                ->get();

            if ($stagingRows->isEmpty()) {
                return ['inserted' => 0, 'updated' => 0, 'skipped' => 0];
            }

            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($stagingRows as $stagingRow) {
                try {
                    // Prepare data for hearings table (exclude staging-specific columns and id)
                    // Let hearings.id auto-increment
                    $hearingData = [
                        'matter_id' => $stagingRow->matter_id,
                        'lawyer_id' => $stagingRow->lawyer_id,
                        'date' => $stagingRow->date,
                        'procedure' => $stagingRow->procedure,
                        'court' => $stagingRow->court,
                        'circuit' => $stagingRow->circuit,
                        'destination' => $stagingRow->destination,
                        'decision' => $stagingRow->decision,
                        'short_decision' => $stagingRow->short_decision,
                        'last_decision' => $stagingRow->last_decision,
                        'next_hearing' => $stagingRow->next_hearing,
                        'report' => (bool)$stagingRow->report,
                        'notify_client' => (bool)$stagingRow->notify_client,
                        'attendee' => $stagingRow->attendee,
                        'attendee_1' => $stagingRow->attendee_1,
                        'attendee_2' => $stagingRow->attendee_2,
                        'attendee_3' => $stagingRow->attendee_3,
                        'attendee_4' => $stagingRow->attendee_4,
                        'next_attendee' => $stagingRow->next_attendee,
                        'evaluation' => $stagingRow->evaluation,
                        'notes' => $stagingRow->notes,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Add external_id if present (check if column exists in hearings table)
                    if ($stagingRow->external_id) {
                        $hearingData['external_id'] = $stagingRow->external_id;
                    }

                    // Upsert by external_id if present and unique, otherwise insert
                    if ($stagingRow->external_id && $this->hasExternalIdColumn()) {
                        $existing = DB::table('hearings')
                            ->where('external_id', $stagingRow->external_id)
                            ->first();
                        
                        if ($existing) {
                            // Update existing by external_id
                            DB::table('hearings')
                                ->where('external_id', $stagingRow->external_id)
                                ->update($hearingData);
                            $updated++;
                        } else {
                            // Insert new
                            DB::table('hearings')->insert($hearingData);
                            $inserted++;
                        }
                    } else {
                        // No external_id or column doesn't exist, just insert (will auto-increment id)
                        // Remove external_id from data if column doesn't exist
                        if (!$this->hasExternalIdColumn()) {
                            unset($hearingData['external_id']);
                        }
                        DB::table('hearings')->insert($hearingData);
                        $inserted++;
                    }
                } catch (\Exception $e) {
                    // Log error but continue with batch
                    \Log::error("Failed to migrate staging row {$stagingRow->staging_id}: {$e->getMessage()}");
                    $skipped++;
                    throw $e; // Re-throw to abort batch transaction
                }
            }

            return ['inserted' => $inserted, 'updated' => $updated, 'skipped' => $skipped];
        });
    }

    private function displaySummary(int $inserted, int $updated, int $skipped, int $failed, bool $dryRun): void
    {
        $this->newLine();
        $this->info('=== Migration Summary ===');
        $this->line("<fg=green>Inserted: {$inserted}</>");
        $this->line("<fg=yellow>Updated: {$updated}</>");
        if ($skipped > 0) {
            $this->line("<fg=yellow>Skipped: {$skipped}</>");
        }
        if ($failed > 0) {
            $this->line("<fg=red>Failed: {$failed}</>");
        }
        if ($dryRun) {
            $this->warn('DRY RUN - No actual changes made');
        } else {
            $this->line("<fg=green>✓ Migration complete</>");
        }
    }

    private function displaySampleDiff(): void
    {
        $this->newLine();
        $this->info('=== Sample Diff (First 10 Records) ===');

        $sample = DB::table('hearings')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get(['id', 'matter_id', 'date', 'court', 'circuit']);

        $this->table(
            ['ID', 'Matter ID', 'Date', 'Court', 'Circuit'],
            $sample->map(function ($row) {
                return [
                    $row->id,
                    $row->matter_id,
                    $row->date,
                    mb_substr($row->court ?? '', 0, 30),
                    mb_substr($row->circuit ?? '', 0, 30),
                ];
            })->toArray()
        );
    }
}

