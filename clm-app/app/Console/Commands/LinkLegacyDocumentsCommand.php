<?php

namespace App\Console\Commands;

use App\Models\CaseModel;
use App\Models\ClientDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkLegacyDocumentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:link-legacy-documents {--dry-run : Verify logic without updating data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link legacy documents to cases using double-validation (client_id + normalized name match)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $this->info($dryRun ? 'Starting DRY RUN...' : 'Executing LINKING process...');

        // 1. Fetch Lookup: Load all cases grouped by client_id for performance
        $this->info('Loading cases grouped by client...');
        $casesByClient = CaseModel::select('id', 'client_id', 'matter_name_ar')
            ->get()
            ->groupBy('client_id');

        $this->info("Loaded cases for " . $casesByClient->count() . " clients.");

        // 2. Fetch Data: Client documents with missing matter_id
        $documents = ClientDocument::whereNull('matter_id')
            ->whereNotNull('legacy_matter_name')
            ->whereNotNull('client_id')
            ->select('id', 'client_id', 'legacy_matter_name')
            ->get();

        $this->info("Found " . $documents->count() . " documents to process.");

        $matchesFound = 0;
        $noClientCases = 0;
        $noCandidates = 0;

        foreach ($documents as $doc) {
            // Condition A: Check if this client has any cases
            if (!isset($casesByClient[$doc->client_id])) {
                $noClientCases++;
                if ($dryRun) {
                    $this->line("[Client #{$doc->client_id}] Doc \"{$doc->legacy_matter_name}\" -> NO MATCH (client has no cases)");
                }
                continue;
            }

            // Get cases for this specific client
            $clientCases = $casesByClient[$doc->client_id];

            // Condition B: Normalize and search
            $normalizedSource = $this->normalizeString($doc->legacy_matter_name);
            $matchedCase = null;

            foreach ($clientCases as $case) {
                $normalizedTarget = $this->normalizeString($case->matter_name_ar);

                if ($normalizedSource === $normalizedTarget) {
                    $matchedCase = $case;
                    break;
                }
            }

            if ($matchedCase) {
                $matchesFound++;

                if ($dryRun) {
                    $this->info("[Client #{$doc->client_id}] Doc \"{$doc->legacy_matter_name}\" -> MATCH -> Case #{$matchedCase->id} (\"{$matchedCase->matter_name_ar}\")");
                } else {
                    $doc->matter_id = $matchedCase->id;
                    $doc->save();
                }
            } else {
                $noCandidates++;
                if ($dryRun) {
                    $this->line("[Client #{$doc->client_id}] Doc \"{$doc->legacy_matter_name}\" -> NO MATCH ({$clientCases->count()} candidates checked)");
                }
            }
        }

        $this->line("---");
        $this->info("Summary:");
        $this->info("  Total documents: " . $documents->count());
        $this->info("  Matches found: $matchesFound");
        $this->info("  No client cases: $noClientCases");
        $this->info("  No matching candidates: $noCandidates");

        if ($dryRun) {
            $this->info("Dry Run Complete. Re-run without --dry-run to execute updates.");
        } else {
            $this->info("Update Complete. Linked $matchesFound documents.");
        }

        return self::SUCCESS;
    }

    /**
     * Normalize string according to strict rules:
     * 1. Replace " لسنة " (and variants) with " / "
     * 2. Remove ALL spaces
     */
    private function normalizeString($text)
    {
        if (!$text) return '';

        // Normalize Arabic "for year" variations to slash
        $text = str_replace([' لسنة ', 'لسنة', ' لسنه '], ' / ', $text);

        // Remove ALL spaces
        $text = str_replace(' ', '', $text);

        return $text;
    }
}
