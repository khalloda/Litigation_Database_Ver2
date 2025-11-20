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

    private function migrateBatch(int $offset, int $limit): array
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
                    // Prepare data for hearings table
                    // hearings_id from CSV maps to both 'id' (if provided) and 'external_id' (for upsert)
                    // Truncate VARCHAR fields to max length (255 bytes) to match production schema
                    // MySQL VARCHAR counts bytes, not characters (UTF-8 Arabic chars = 2-3 bytes each)
                    $hearingData = [
                        'matter_id' => $stagingRow->matter_id,
                        'lawyer_id' => $stagingRow->lawyer_id,
                        'date' => $stagingRow->date,
                        'procedure' => $this->truncateToBytes($stagingRow->procedure ?? '', 255),
                        'court' => $this->truncateToBytes($stagingRow->court ?? '', 255),
                        'circuit' => $this->truncateToBytes($stagingRow->circuit ?? '', 255),
                        'destination' => $this->truncateToBytes($stagingRow->destination ?? '', 255),
                        'decision' => $stagingRow->decision, // TEXT field, no truncation
                        'short_decision' => $this->truncateToBytes($stagingRow->short_decision ?? '', 255),
                        'last_decision' => $this->truncateToBytes($stagingRow->last_decision ?? '', 255),
                        'next_hearing' => $stagingRow->next_hearing,
                        'report' => (bool)$stagingRow->report,
                        'notify_client' => (bool)$stagingRow->notify_client,
                        'attendee' => $this->truncateToBytes($stagingRow->attendee ?? '', 255),
                        'attendee_1' => $this->truncateToBytes($stagingRow->attendee_1 ?? '', 255),
                        'attendee_2' => $this->truncateToBytes($stagingRow->attendee_2 ?? '', 255),
                        'attendee_3' => $this->truncateToBytes($stagingRow->attendee_3 ?? '', 255),
                        'attendee_4' => $this->truncateToBytes($stagingRow->attendee_4 ?? '', 255),
                        'next_attendee' => $this->truncateToBytes($stagingRow->next_attendee ?? '', 255),
                        'evaluation' => $this->truncateToBytes($stagingRow->evaluation ?? '', 255),
                        'notes' => $stagingRow->notes, // TEXT field, no truncation
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Add id from hearings_id if present (preserve original ID from CSV)
                    if ($stagingRow->id && $stagingRow->id > 0) {
                        $hearingData['id'] = (int)$stagingRow->id;
                    }

                    // Add external_id (contains hearings_id as string, or synthesized)
                    if ($stagingRow->external_id) {
                        $hearingData['external_id'] = $stagingRow->external_id;
                    }

                    // Upsert by external_id (idempotent)
                    // external_id should always be present after synthesis
                    if ($stagingRow->external_id && $this->hasExternalIdColumn()) {
                        // Check if exists before upsert
                        $existing = DB::table('hearings')
                            ->where('external_id', $stagingRow->external_id)
                            ->first();
                        
                        if ($existing) {
                            // Update existing record (don't change id, it's immutable)
                            unset($hearingData['id']); // Remove id from update data
                            DB::table('hearings')
                                ->where('external_id', $stagingRow->external_id)
                                ->update($hearingData);
                            $updated++;
                        } else {
                            // Insert new record (can set id from hearings_id if provided)
                            try {
                                DB::table('hearings')->insert($hearingData);
                                $inserted++;
                            } catch (\Exception $e) {
                                // If id conflict, remove id and let auto-increment handle it
                                if (strpos($e->getMessage(), 'Duplicate entry') !== false && isset($hearingData['id'])) {
                                    unset($hearingData['id']);
                                    DB::table('hearings')->insert($hearingData);
                                    $inserted++;
                                } else {
                                    throw $e;
                                }
                            }
                        }
                    } elseif ($stagingRow->external_id && !$this->hasExternalIdColumn()) {
                        // external_id exists but column doesn't - remove it and insert
                        unset($hearingData['external_id']);
                        try {
                            DB::table('hearings')->insert($hearingData);
                            $inserted++;
                        } catch (\Exception $e) {
                            // If id conflict, remove id and let auto-increment handle it
                            if (strpos($e->getMessage(), 'Duplicate entry') !== false && isset($hearingData['id'])) {
                                unset($hearingData['id']);
                                DB::table('hearings')->insert($hearingData);
                                $inserted++;
                            } else {
                                throw $e;
                            }
                        }
                    } else {
                        // No external_id (shouldn't happen after synthesis, but handle gracefully)
                        if (isset($hearingData['external_id'])) {
                            unset($hearingData['external_id']);
                        }
                        try {
                            DB::table('hearings')->insert($hearingData);
                            $inserted++;
                        } catch (\Exception $e) {
                            // If id conflict, remove id and let auto-increment handle it
                            if (strpos($e->getMessage(), 'Duplicate entry') !== false && isset($hearingData['id'])) {
                                unset($hearingData['id']);
                                DB::table('hearings')->insert($hearingData);
                                $inserted++;
                            } else {
                                throw $e;
                            }
                        }
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

    /**
     * Truncate string to max bytes (for MySQL VARCHAR which counts bytes, not characters).
     * 
     * @param string|null $value
     * @param int $maxBytes
     * @return string|null
     */
    private function truncateToBytes(?string $value, int $maxBytes): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $valueBytes = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        if (strlen($valueBytes) <= $maxBytes) {
            return $value;
        }

        // Truncate byte by byte until we're under the limit
        $truncated = '';
        for ($i = 0; $i < mb_strlen($value, 'UTF-8'); $i++) {
            $char = mb_substr($value, $i, 1, 'UTF-8');
            $charBytes = mb_convert_encoding($char, 'UTF-8', 'UTF-8');
            if (strlen($truncated . $charBytes) > $maxBytes) {
                break;
            }
            $truncated .= $char;
        }

        return $truncated ?: null;
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

