<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;

class DataQualityController extends Controller
{
    public function index()
    {
        // Record counts
        $counts = [
            'lawyers' => Lawyer::count(),
            'clients' => Client::count(),
            'engagement_letters' => EngagementLetter::count(),
            'cases' => CaseModel::count(),
            'hearings' => Hearing::count(),
            'contacts' => Contact::count(),
            'power_of_attorneys' => PowerOfAttorney::count(),
            'admin_tasks' => AdminTask::count(),
            'admin_subtasks' => AdminSubtask::count(),
            'documents' => ClientDocument::count(),
        ];
        $counts['total'] = array_sum($counts);

        // Referential integrity
        $integrity = [
            'cases_client' => [
                'valid' => CaseModel::whereHas('client')->count(),
                'total' => CaseModel::count(),
            ],
            'hearings_case' => [
                'valid' => Hearing::whereHas('case')->count(),
                'total' => Hearing::count(),
            ],
            'tasks_case' => [
                'valid' => AdminTask::whereHas('case')->count(),
                'total' => AdminTask::count(),
            ],
            'subtasks_task' => [
                'valid' => AdminSubtask::whereHas('task')->count(),
                'total' => AdminSubtask::count(),
            ],
            'documents_client' => [
                'valid' => ClientDocument::whereHas('client')->count(),
                'total' => ClientDocument::count(),
            ],
            'contacts_client' => [
                'valid' => Contact::whereHas('client')->count(),
                'total' => Contact::count(),
            ],
        ];

        // Calculate percentages
        foreach ($integrity as $key => &$item) {
            $item['percentage'] = $item['total'] > 0 ? round(($item['valid'] / $item['total']) * 100, 2) : 0;
            $item['orphans'] = $item['total'] - $item['valid'];
        }

        // Data completeness
        $completeness = [
            'cases_start_date' => [
                'filled' => CaseModel::whereNotNull('matter_start_date')->count(),
                'total' => CaseModel::count(),
            ],
            'cases_status' => [
                'filled' => CaseModel::whereNotNull('matter_status')->count(),
                'total' => CaseModel::count(),
            ],
            'hearings_date' => [
                'filled' => Hearing::whereNotNull('date')->count(),
                'total' => Hearing::count(),
            ],
            'tasks_status' => [
                'filled' => AdminTask::whereNotNull('status')->count(),
                'total' => AdminTask::count(),
            ],
            'documents_date' => [
                'filled' => ClientDocument::whereNotNull('deposit_date')->count(),
                'total' => ClientDocument::count(),
            ],
        ];

        // Calculate percentages
        foreach ($completeness as $key => &$item) {
            $item['percentage'] = $item['total'] > 0 ? round(($item['filled'] / $item['total']) * 100, 2) : 0;
        }

        // Relationship stats
        $stats = [
            'avg_cases_per_client' => Client::count() > 0 ? round(CaseModel::count() / Client::count(), 2) : 0,
            'avg_hearings_per_case' => CaseModel::count() > 0 ? round(Hearing::count() / CaseModel::count(), 2) : 0,
            'avg_tasks_per_case' => CaseModel::count() > 0 ? round(AdminTask::count() / CaseModel::count(), 2) : 0,
        ];

        // Top clients
        $topClients = Client::withCount('cases')
            ->orderBy('cases_count', 'desc')
            ->limit(10)
            ->get();

        return view('data-quality.index', compact('counts', 'integrity', 'completeness', 'stats', 'topClients'));
    }
}
