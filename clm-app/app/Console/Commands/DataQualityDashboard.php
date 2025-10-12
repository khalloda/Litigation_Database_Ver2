<?php

namespace App\Console\Commands;

use App\Models\AdminSubtask;
use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\Contact;
use App\Models\EngagementLetter;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\PowerOfAttorney;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DataQualityDashboard extends Command
{
    protected $signature = 'data:quality';
    protected $description = 'Display comprehensive data quality dashboard';

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('    DATA QUALITY DASHBOARD');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Record Counts
        $this->displayRecordCounts();
        $this->newLine();

        // Referential Integrity
        $this->displayReferentialIntegrity();
        $this->newLine();

        // Data Completeness
        $this->displayDataCompleteness();
        $this->newLine();

        // Relationship Stats
        $this->displayRelationshipStats();
        $this->newLine();

        $this->info('═══════════════════════════════════════════════════════');
        return 0;
    }

    protected function displayRecordCounts()
    {
        $this->line('<fg=cyan>📊 RECORD COUNTS</>');
        $this->line('─────────────────────────────────────────────────────');

        $counts = [
            'Lawyers' => Lawyer::count(),
            'Clients' => Client::count(),
            'Engagement Letters' => EngagementLetter::count(),
            'Cases' => CaseModel::count(),
            'Hearings' => Hearing::count(),
            'Contacts' => Contact::count(),
            'Power of Attorneys' => PowerOfAttorney::count(),
            'Admin Tasks' => AdminTask::count(),
            'Admin Subtasks' => AdminSubtask::count(),
            'Documents' => ClientDocument::count(),
        ];

        $total = 0;
        foreach ($counts as $entity => $count) {
            $this->line(sprintf('  %-20s %s', $entity . ':', number_format($count)));
            $total += $count;
        }

        $this->line('─────────────────────────────────────────────────────');
        $this->line(sprintf('  <fg=green>%-20s %s</>', 'TOTAL:', number_format($total)));
    }

    protected function displayReferentialIntegrity()
    {
        $this->line('<fg=cyan>🔗 REFERENTIAL INTEGRITY</>');
        $this->line('─────────────────────────────────────────────────────');

        // Cases → Clients
        $totalCases = CaseModel::count();
        $casesWithClient = CaseModel::whereHas('client')->count();
        $this->displayIntegrity('Cases → Client', $casesWithClient, $totalCases);

        // Hearings → Cases
        $totalHearings = Hearing::count();
        $hearingsWithCase = Hearing::whereHas('case')->count();
        $this->displayIntegrity('Hearings → Case', $hearingsWithCase, $totalHearings);

        // Tasks → Cases
        $totalTasks = AdminTask::count();
        $tasksWithCase = AdminTask::whereHas('case')->count();
        $this->displayIntegrity('Tasks → Case', $tasksWithCase, $totalTasks);

        // Subtasks → Tasks
        $totalSubtasks = AdminSubtask::count();
        $subtasksWithTask = AdminSubtask::whereHas('task')->count();
        $this->displayIntegrity('Subtasks → Task', $subtasksWithTask, $totalSubtasks);

        // Documents → Clients
        $totalDocs = ClientDocument::count();
        $docsWithClient = ClientDocument::whereHas('client')->count();
        $this->displayIntegrity('Documents → Client', $docsWithClient, $totalDocs);

        // Contacts → Clients
        $totalContacts = Contact::count();
        $contactsWithClient = Contact::whereHas('client')->count();
        $this->displayIntegrity('Contacts → Client', $contactsWithClient, $totalContacts);
    }

    protected function displayIntegrity(string $relationship, int $valid, int $total)
    {
        if ($total === 0) {
            $percentage = 0;
            $status = '<fg=gray>N/A</>';
        } else {
            $percentage = round(($valid / $total) * 100, 2);
            $status = $percentage >= 95 ? '<fg=green>✓</>' : ($percentage >= 50 ? '<fg=yellow>!</>' : '<fg=red>✗</>');
        }

        $orphans = $total - $valid;
        $this->line(sprintf(
            '  %-25s %s %6.2f%% (%d/%d) [%d orphans]',
            $relationship,
            $status,
            $percentage,
            $valid,
            $total,
            $orphans
        ));
    }

    protected function displayDataCompleteness()
    {
        $this->line('<fg=cyan>✅ DATA COMPLETENESS (Key Fields)</>');
        $this->line('─────────────────────────────────────────────────────');

        // Cases with dates
        $casesWithStartDate = CaseModel::whereNotNull('matter_start_date')->count();
        $totalCases = CaseModel::count();
        $this->displayCompleteness('Cases with start date', $casesWithStartDate, $totalCases);

        // Cases with status
        $casesWithStatus = CaseModel::whereNotNull('matter_status')->count();
        $this->displayCompleteness('Cases with status', $casesWithStatus, $totalCases);

        // Hearings with dates
        $hearingsWithDate = Hearing::whereNotNull('date')->count();
        $totalHearings = Hearing::count();
        $this->displayCompleteness('Hearings with date', $hearingsWithDate, $totalHearings);

        // Tasks with status
        $tasksWithStatus = AdminTask::whereNotNull('status')->count();
        $totalTasks = AdminTask::count();
        $this->displayCompleteness('Tasks with status', $tasksWithStatus, $totalTasks);

        // Documents with dates
        $docsWithDate = ClientDocument::whereNotNull('deposit_date')->count();
        $totalDocs = ClientDocument::count();
        $this->displayCompleteness('Documents with date', $docsWithDate, $totalDocs);
    }

    protected function displayCompleteness(string $field, int $filled, int $total)
    {
        if ($total === 0) {
            $percentage = 0;
            $status = '<fg=gray>N/A</>';
        } else {
            $percentage = round(($filled / $total) * 100, 2);
            $status = $percentage >= 90 ? '<fg=green>✓</>' : ($percentage >= 50 ? '<fg=yellow>!</>' : '<fg=red>✗</>');
        }

        $this->line(sprintf(
            '  %-30s %s %6.2f%% (%d/%d)',
            $field,
            $status,
            $percentage,
            $filled,
            $total
        ));
    }

    protected function displayRelationshipStats()
    {
        $this->line('<fg=cyan>📈 RELATIONSHIP STATISTICS</>');
        $this->line('─────────────────────────────────────────────────────');

        // Average cases per client
        $avgCasesPerClient = CaseModel::count() > 0 && Client::count() > 0
            ? round(CaseModel::count() / Client::count(), 2)
            : 0;
        $this->line(sprintf('  %-40s %.2f', 'Average cases per client:', $avgCasesPerClient));

        // Average hearings per case
        $avgHearingsPerCase = Hearing::count() > 0 && CaseModel::count() > 0
            ? round(Hearing::count() / CaseModel::count(), 2)
            : 0;
        $this->line(sprintf('  %-40s %.2f', 'Average hearings per case:', $avgHearingsPerCase));

        // Average tasks per case
        $avgTasksPerCase = AdminTask::count() > 0 && CaseModel::count() > 0
            ? round(AdminTask::count() / CaseModel::count(), 2)
            : 0;
        $this->line(sprintf('  %-40s %.2f', 'Average tasks per case:', $avgTasksPerCase));

        // Top 5 clients by case count
        $this->newLine();
        $this->line('<fg=yellow>Top 5 Clients by Case Count:</>');

        $topClients = Client::withCount('cases')
            ->orderBy('cases_count', 'desc')
            ->limit(5)
            ->get();

        foreach ($topClients as $index => $client) {
            $this->line(sprintf(
                '  %d. %s (%d cases)',
                $index + 1,
                $client->client_name_ar ?? $client->client_name_en,
                $client->cases_count
            ));
        }
    }
}
