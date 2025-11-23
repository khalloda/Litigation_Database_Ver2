<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Client;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    protected array $columnOrder = [
        'serial',
        'matter',
        'court',
        'clientRole',
        'opponentRole',
        'subject',
        'latestDecision',
        'evaluation',
        'financialProvision',
    ];

    public function clientCasesPdf(Request $request)
    {
        $payload = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'columns' => ['array'],
            'columns.*' => ['boolean'],
        ]);

        $columns = $this->normalizeColumns($payload['columns'] ?? []);
        $client = Client::with('documentsLocation')->findOrFail($payload['client_id']);

        $cases = CaseModel::with([
            'client',
            'court',
            'clientCapacity',
            'opponentCapacity',
            'latestHearing',
        ])
            ->where('client_id', $client->id)
            ->orderBy('matter_name_ar')
            ->get();

        $rows = $cases->map(function (CaseModel $case, int $index) {
            return [
                'serial' => $index + 1,
                'matter' => $case->matter_name_ar ?? $case->matter_name_en ?? '—',
                'court' => $case->court?->court_name_ar
                    ?? $case->court?->court_name_en
                    ?? $case->matter_court_text
                    ?? '—',
                'clientRole' => $this->joinParts([
                    $case->client_in_case_name ?? $case->client?->client_name_ar ?? $case->client?->client_name_en,
                    $case->clientCapacity?->label_ar ?? $case->clientCapacity?->label_en ?? $case->client_capacity_note,
                ]),
                'opponentRole' => $this->joinParts([
                    $case->opponent_in_case_name,
                    $case->opponentCapacity?->label_ar ?? $case->opponentCapacity?->label_en ?? $case->opponent_capacity_note,
                ]),
                'subject' => $case->matter_description ?? '—',
                'latestDecision' => $case->latestHearing?->decision
                    ?? $case->current_status
                    ?? '—',
                'evaluation' => $case->matter_evaluation ?? '—',
                'financialProvision' => $case->financial_provision ?? '—',
            ];
        });

        $visibleColumnLabels = collect($this->columnLabels())
            ->filter(fn ($label, $key) => $columns[$key] ?? false)
            ->toArray();

        $pdf = SnappyPdf::loadView('reports.client_cases_pdf', [
            'client' => $client,
            'rows' => $rows,
            'columns' => $columns,
            'columnLabels' => $visibleColumnLabels,
            'generatedAt' => now('Africa/Cairo'),
            'totalCases' => $rows->count(),
        ])->setPaper('a4');

        $fileName = Str::slug($client->client_name_en ?? $client->client_name_ar ?? 'client-report') . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    protected function normalizeColumns(array $input): array
    {
        $columns = [];
        foreach ($this->columnOrder as $key) {
            $columns[$key] = array_key_exists($key, $input) ? (bool) $input[$key] : true;
        }

        return $columns;
    }

    protected function joinParts(array $parts): string
    {
        $filtered = array_values(array_filter(array_map(function ($value) {
            return $value !== null && $value !== '' ? trim($value) : null;
        }, $parts)));

        return !empty($filtered) ? implode(' - ', $filtered) : '—';
    }

    protected function columnLabels(): array
    {
        return [
            'serial' => 'م/#',
            'matter' => 'رقم الدعوى',
            'court' => 'المحكمة',
            'clientRole' => 'الموكل وصفته',
            'opponentRole' => 'الخصم وصفته',
            'subject' => 'موضوع الدعوى',
            'latestDecision' => 'آخر موقف',
            'evaluation' => 'التقييم',
            'financialProvision' => 'المخصص المالي',
        ];
    }
}

