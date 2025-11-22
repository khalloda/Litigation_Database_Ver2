<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Opponent;
use App\Models\OptionValue;
use App\Models\ImportSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CaseOpponentsImportService
{
    protected CaseOpponentService $caseOpponentService;

    public function __construct(CaseOpponentService $caseOpponentService)
    {
        $this->caseOpponentService = $caseOpponentService;
    }

    /**
     * Process case opponents import data.
     */
    public function processImport(array $rows, ImportSession $session, array $decisions = []): array
    {
        $imported = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                // Map row data
                $data = [];
                foreach ($session->column_mapping as $sourceCol => $targetCol) {
                    if (!empty($targetCol)) {
                        $data[$targetCol] = $row[$sourceCol] ?? null;
                    }
                }

                // Resolve case
                $case = $this->resolveCase($data);
                if (!$case) {
                    $errors[] = [
                        'row' => $index,
                        'message' => 'Case not found. Provide either case_id or case_number.',
                    ];
                    $failed++;
                    continue;
                }

                // Resolve opponent
                $opponent = $this->resolveOpponent($data);
                if (!$opponent) {
                    $errors[] = [
                        'row' => $index,
                        'message' => 'Opponent not found. Provide either opponent_id or opponent_name.',
                    ];
                    $failed++;
                    continue;
                }

                // Resolve capacity
                $capacityId = $this->resolveCapacity($data);

                // Check if opponent is already attached to this case
                $existing = $case->opponents()->wherePivot('opponent_id', $opponent->id)->first();
                if ($existing) {
                    $errors[] = [
                        'row' => $index,
                        'message' => "Opponent '{$opponent->display_name}' is already attached to case '{$case->matter_name_ar}'.",
                    ];
                    $skipped++;
                    continue;
                }

                // Validate max opponents limit
                try {
                    $this->caseOpponentService->validateMaxOpponents($case);
                } catch (\InvalidArgumentException $e) {
                    $errors[] = [
                        'row' => $index,
                        'message' => $e->getMessage(),
                    ];
                    $failed++;
                    continue;
                }

                // Attach opponent to case
                $this->caseOpponentService->attachOpponent(
                    $case,
                    $opponent->id,
                    $capacityId,
                    (bool) ($data['is_primary'] ?? false),
                    (int) ($data['display_order'] ?? null),
                    $data['alias_text'] ?? null
                );

                $imported++;

                Log::info('Case opponent attached via import', [
                    'case_id' => $case->id,
                    'opponent_id' => $opponent->id,
                    'is_primary' => (bool) ($data['is_primary'] ?? false),
                    'user_id' => Auth::id(),
                ]);
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => $imported,
            'failed' => $failed,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 100), // Limit errors
        ];
    }

    /**
     * Resolve case from import data.
     */
    private function resolveCase(array $data): ?CaseModel
    {
        // Try by case_id first
        if (!empty($data['case_id'])) {
            return CaseModel::find($data['case_id']);
        }

        // Try by case_number
        if (!empty($data['case_number'])) {
            return CaseModel::where('matter_name_ar', $data['case_number'])
                ->orWhere('matter_name_en', $data['case_number'])
                ->first();
        }

        return null;
    }

    /**
     * Resolve opponent from import data.
     */
    private function resolveOpponent(array $data): ?Opponent
    {
        // Try by opponent_id first
        if (!empty($data['opponent_id'])) {
            return Opponent::find($data['opponent_id']);
        }

        // Try by opponent_name
        if (!empty($data['opponent_name'])) {
            return Opponent::where('opponent_name_ar', $data['opponent_name'])
                ->orWhere('opponent_name_en', $data['opponent_name'])
                ->first();
        }

        return null;
    }

    /**
     * Resolve capacity from import data.
     */
    private function resolveCapacity(array $data): ?int
    {
        // Try by capacity_id first
        if (!empty($data['capacity_id'])) {
            return (int) $data['capacity_id'];
        }

        // Try by capacity name
        if (!empty($data['capacity'])) {
            $capacity = OptionValue::whereHas('optionSet', function ($q) {
                $q->where('set_name', 'capacity');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['capacity'])
                    ->orWhere('label_ar', $data['capacity']);
            })->first();

            if ($capacity) {
                return $capacity->id;
            }
        }

        return null;
    }

    /**
     * Validate import data before processing.
     */
    public function validateImportData(array $rows, ImportSession $session): array
    {
        $errors = [];
        $warnings = [];

        foreach ($rows as $index => $row) {
            $data = [];
            foreach ($session->column_mapping as $sourceCol => $targetCol) {
                if (!empty($targetCol)) {
                    $data[$targetCol] = $row[$sourceCol] ?? null;
                }
            }

            // Check required fields
            if (empty($data['case_id']) && empty($data['case_number'])) {
                $errors[] = [
                    'row' => $index,
                    'message' => 'Either case_id or case_number is required.',
                ];
            }

            if (empty($data['opponent_id']) && empty($data['opponent_name'])) {
                $errors[] = [
                    'row' => $index,
                    'message' => 'Either opponent_id or opponent_name is required.',
                ];
            }

            // Check for ID vs Name conflicts
            if (!empty($data['case_id']) && !empty($data['case_number'])) {
                $warnings[] = [
                    'row' => $index,
                    'message' => 'Both case_id and case_number provided. case_id will be used.',
                ];
            }

            if (!empty($data['opponent_id']) && !empty($data['opponent_name'])) {
                $warnings[] = [
                    'row' => $index,
                    'message' => 'Both opponent_id and opponent_name provided. opponent_id will be used.',
                ];
            }

            if (!empty($data['capacity_id']) && !empty($data['capacity'])) {
                $warnings[] = [
                    'row' => $index,
                    'message' => 'Both capacity_id and capacity provided. capacity_id will be used.',
                ];
            }

            // Validate is_primary field
            if (isset($data['is_primary']) && !in_array($data['is_primary'], ['0', '1', 'true', 'false', true, false])) {
                $errors[] = [
                    'row' => $index,
                    'message' => 'is_primary must be 0, 1, true, or false.',
                ];
            }

            // Validate display_order field
            if (isset($data['display_order']) && !is_numeric($data['display_order'])) {
                $errors[] = [
                    'row' => $index,
                    'message' => 'display_order must be a number.',
                ];
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'error_count' => count($errors),
            'warning_count' => count($warnings),
        ];
    }
}
