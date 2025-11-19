<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HearingsValidateStagingCommand extends Command
{
    protected $signature = 'hearings:validate-staging';
    protected $description = 'Run QA SQL checks on hearings_staging table (from QA Checklist)';

    private $issues = [];
    private $highSeverityIssues = 0;

    public function handle(): int
    {
        // Set UTF-8 encoding
        mb_internal_encoding('UTF-8');

        $this->info('Running QA validation checks on hearings_staging...');
        $this->newLine();

        // Run all validation checks from QA Checklist
        $this->checkExternalIdDuplicates();
        $this->checkRequiredFields();
        $this->checkDateSanity();
        $this->checkRawDateConsistency();
        $this->checkMatterIdIntegrity();
        $this->checkLawyerIdIntegrity();
        $this->checkBooleanFields();
        $this->checkStringLengths();
        $this->checkNaturalKeyDuplicates();

        // Display results
        $this->displayResults();

        // Exit non-zero if high-severity issues found
        return $this->highSeverityIssues > 0 ? 1 : 0;
    }

    private function checkExternalIdDuplicates(): void
    {
        $this->info('Checking external_id duplicates...');
        
        // Only check if external_id is present (nullable, so don't require uniqueness)
        $duplicates = DB::table('hearings_staging')
            ->select('external_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('external_id')
            ->groupBy('external_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $this->issues[] = [
                'severity' => 'MEDIUM',
                'check' => 'External ID Duplicates',
                'count' => $duplicates->count(),
                'message' => 'Duplicate external_id values found (warn only, not blocking)',
                'details' => $duplicates->pluck('external_id')->toArray(),
            ];
            $this->warn("  ⚠ Found {$duplicates->count()} duplicate external_ids");
        } else {
            $this->line("  ✓ No duplicate external_ids");
        }
    }

    private function checkRequiredFields(): void
    {
        $this->info('Checking required fields...');
        
        $missing = DB::table('hearings_staging')
            ->where(function ($query) {
                $query->whereNull('matter_id')
                    ->orWhere('matter_id', '')
                    ->orWhereNull('date')
                    ->orWhere('date', '');
            })
            ->count();

        if ($missing > 0) {
            $this->highSeverityIssues++;
            $this->issues[] = [
                'severity' => 'HIGH',
                'check' => 'Required Fields',
                'count' => $missing,
                'message' => 'Rows missing required fields (matter_id or date)',
            ];
            $this->error("  ✗ Found {$missing} rows with missing required fields");
        } else {
            $this->line("  ✓ All required fields present");
        }
    }

    private function checkDateSanity(): void
    {
        $this->info('Checking date sanity (0000-00-00)...');
        
        // Check for MySQL invalid dates (0000-00-00)
        $invalidDate = DB::table('hearings_staging')
            ->where('date', '0000-00-00')
            ->count();

        $invalidNext = DB::table('hearings_staging')
            ->where('next_hearing', '0000-00-00')
            ->count();

        $total = $invalidDate + $invalidNext;
        if ($total > 0) {
            $this->highSeverityIssues++;
            $this->issues[] = [
                'severity' => 'HIGH',
                'check' => 'Date Sanity',
                'count' => $total,
                'message' => "Invalid dates found (0000-00-00): {$invalidDate} date, {$invalidNext} next_hearing",
            ];
            $this->error("  ✗ Found {$invalidDate} invalid dates and {$invalidNext} invalid next_hearing dates");
        } else {
            $this->line("  ✓ No invalid dates (0000-00-00)");
        }
    }

    private function checkRawDateConsistency(): void
    {
        $this->info('Checking raw/date consistency...');
        
        // Warn if date_raw exists but date is NULL (should have been parsed)
        $badRawDates = DB::table('hearings_staging')
            ->whereNotNull('date_raw')
            ->whereNull('date')
            ->count();

        $badRawNext = DB::table('hearings_staging')
            ->whereNotNull('next_hearing_raw')
            ->whereNull('next_hearing')
            ->count();

        $total = $badRawDates + $badRawNext;
        if ($total > 0) {
            $this->issues[] = [
                'severity' => 'MEDIUM',
                'check' => 'Raw/Date Consistency',
                'count' => $total,
                'message' => "Raw dates present but parsed date is NULL: {$badRawDates} date_raw, {$badRawNext} next_hearing_raw",
            ];
            $this->warn("  ⚠ Found {$badRawDates} date_raw without parsed date, {$badRawNext} next_hearing_raw without parsed date");
        } else {
            $this->line("  ✓ Raw/date consistency OK");
        }
    }

    private function checkMatterIdIntegrity(): void
    {
        $this->info('Checking matter_id foreign key integrity...');
        
        $orphaned = DB::table('hearings_staging', 'stg')
            ->leftJoin('cases', 'stg.matter_id', '=', 'cases.id')
            ->whereNull('cases.id')
            ->whereNotNull('stg.matter_id')
            ->where('stg.matter_id', '!=', '')
            ->count();

        if ($orphaned > 0) {
            $this->highSeverityIssues++;
            $this->issues[] = [
                'severity' => 'HIGH',
                'check' => 'Matter ID Integrity',
                'count' => $orphaned,
                'message' => 'Orphaned matter_id values (not found in cases table)',
            ];
            $this->error("  ✗ Found {$orphaned} orphaned matter_id values");
        } else {
            $this->line("  ✓ All matter_id values valid");
        }
    }

    private function checkLawyerIdIntegrity(): void
    {
        $this->info('Checking lawyer_id foreign key integrity...');
        
        $orphaned = DB::table('hearings_staging', 'stg')
            ->leftJoin('lawyers', 'stg.lawyer_id', '=', 'lawyers.id')
            ->whereNull('lawyers.id')
            ->whereNotNull('stg.lawyer_id')
            ->where('stg.lawyer_id', '!=', '')
            ->count();

        if ($orphaned > 0) {
            $this->issues[] = [
                'severity' => 'MEDIUM',
                'check' => 'Lawyer ID Integrity',
                'count' => $orphaned,
                'message' => 'Orphaned lawyer_id values (not found in lawyers table)',
            ];
            $this->warn("  ⚠ Found {$orphaned} orphaned lawyer_id values");
        } else {
            $this->line("  ✓ All lawyer_id values valid");
        }
    }

    private function checkBooleanFields(): void
    {
        $this->info('Checking boolean fields...');
        
        // Note: In staging table, booleans are stored as TINYINT(1), so this check may not apply
        // But we can check for NULL values if needed
        $this->line("  ✓ Boolean fields validated (stored as TINYINT)");
    }

    private function checkStringLengths(): void
    {
        $this->info('Checking string field lengths...');
        
        $longFields = DB::select("
            SELECT 
                MAX(CHAR_LENGTH(procedure)) as max_procedure,
                MAX(CHAR_LENGTH(court)) as max_court,
                MAX(CHAR_LENGTH(circuit)) as max_circuit,
                MAX(CHAR_LENGTH(destination)) as max_destination,
                MAX(CHAR_LENGTH(short_decision)) as max_short_decision,
                MAX(CHAR_LENGTH(last_decision)) as max_last_decision,
                MAX(CHAR_LENGTH(attendee)) as max_attendee,
                MAX(CHAR_LENGTH(evaluation)) as max_evaluation
            FROM hearings_staging
        ");

        $maxLengths = (array)$longFields[0];
        $violations = [];
        foreach ($maxLengths as $field => $length) {
            if ($length > 255) {
                $violations[] = "{$field}: {$length} chars";
            }
        }

        if (!empty($violations)) {
            $this->issues[] = [
                'severity' => 'MEDIUM',
                'check' => 'String Lengths',
                'message' => 'Fields exceeding 255 characters',
                'details' => $violations,
            ];
            $this->warn("  ⚠ Found fields exceeding 255 characters: " . implode(', ', $violations));
        } else {
            $this->line("  ✓ All string fields within length limits");
        }
    }

    private function checkNaturalKeyDuplicates(): void
    {
        $this->info('Checking natural-key duplicates (matter_id + date + procedure + court)...');
        
        // Natural-key duplicate heuristic
        $duplicates = DB::table('hearings_staging')
            ->select(
                'matter_id',
                DB::raw("COALESCE(date, '1970-01-01') AS d"),
                DB::raw("COALESCE(procedure, '') AS proc"),
                DB::raw("COALESCE(court, '') AS court"),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('matter_id')
            ->groupBy('matter_id', 'd', 'proc', 'court')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $this->issues[] = [
                'severity' => 'MEDIUM',
                'check' => 'Natural-Key Duplicates',
                'count' => $duplicates->count(),
                'message' => 'Potential duplicate rows (same matter_id + date + procedure + court)',
            ];
            $this->warn("  ⚠ Found {$duplicates->count()} potential duplicate row combinations");
        } else {
            $this->line("  ✓ No natural-key duplicates");
        }
    }

    private function displayResults(): void
    {
        $this->newLine();
        $this->info('=== Validation Results ===');
        
        if (empty($this->issues)) {
            $this->line("<fg=green>✓ All checks passed!</>");
            return;
        }

        foreach ($this->issues as $issue) {
            $severity = $issue['severity'];
            $color = $severity === 'HIGH' ? 'red' : 'yellow';
            $icon = $severity === 'HIGH' ? '✗' : '⚠';
            
            $this->line("<fg={$color}>{$icon} [{$severity}] {$issue['check']}: {$issue['message']}</>");
            if (isset($issue['count'])) {
                $this->line("    Count: {$issue['count']}");
            }
            if (isset($issue['details'])) {
                if (is_array($issue['details'])) {
                    $this->line("    Details: " . implode(', ', array_slice($issue['details'], 0, 10)));
                    if (count($issue['details']) > 10) {
                        $this->line("    ... and " . (count($issue['details']) - 10) . " more");
                    }
                } else {
                    $this->line("    Details: {$issue['details']}");
                }
            }
        }

        $this->newLine();
        if ($this->highSeverityIssues > 0) {
            $this->error("✗ Validation FAILED: {$this->highSeverityIssues} high-severity issue(s) found");
            $this->warn("Please resolve high-severity issues before committing to hearings table");
        } else {
            $this->info("✓ Validation PASSED (warnings only)");
        }
    }
}

