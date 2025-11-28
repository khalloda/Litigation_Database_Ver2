<?php

namespace App\Http\Controllers;

use App\Services\FuzzyMatchingChoiceService;
use App\Models\ImportSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Lawyer;
use App\Models\Court;
use App\Models\OptionValue;
use App\Models\OptionSet;
use Illuminate\Support\Facades\DB;

class FuzzyMatchingController extends Controller
{
    protected $choiceService;

    public function __construct(FuzzyMatchingChoiceService $choiceService)
    {
        $this->choiceService = $choiceService;
    }

    /**
     * Get choices for a failed fuzzy match.
     */
    public function getChoices(Request $request): JsonResponse
    {
        // Check if this is a CSRF token refresh request
        if ($request->has('refresh_csrf')) {
            // Force session regeneration to get a fresh token
            session()->regenerate();
            return response()->json([
                'success' => true,
                'csrf_token' => csrf_token()
            ]);
        }

        $request->validate([
            'field' => 'required|string',
            'search_value' => 'required|string',
            'import_session_id' => 'required|integer'
        ]);

        $choices = $this->choiceService->getChoicesForField(
            $request->field,
            $request->search_value
        );

        return response()->json([
            'success' => true,
            'choices' => $choices
        ]);
    }

    /**
     * Apply user's choice to resolve a fuzzy match.
     */
    public function applyChoice(Request $request): JsonResponse
    {
        \Log::info('FuzzyMatchingController::applyChoice called', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'csrf_token' => $request->input('_token'),
            'session_token' => session()->token(),
            'csrf_match' => hash_equals(session()->token(), $request->input('_token'))
        ]);

        // Check for CSRF token mismatch and regenerate session if needed
        if (!hash_equals(session()->token(), $request->input('_token'))) {
            \Log::warning('CSRF token mismatch detected, regenerating session', [
                'session_token' => session()->token(),
                'request_token' => $request->input('_token')
            ]);
            session()->regenerate();
        }

        $request->validate([
            'field' => 'required|string',
            'search_value' => 'required|string',
            'choice_type' => 'required|in:existing,create',
            'choice_data' => 'required',
            'import_session_id' => 'required|integer'
        ]);

        // Handle choice_data as either array or JSON string
        $choiceData = $request->input('choice_data');
        if (is_string($choiceData)) {
            $choiceData = json_decode($choiceData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid choice_data JSON format'
                ], 422);
            }
        }

        if (!is_array($choiceData)) {
            return response()->json([
                'success' => false,
                'message' => 'choice_data must be an array'
            ], 422);
        }

        try {
            $result = null;

            if ($request->choice_type === 'existing') {
                $result = $this->applyExistingChoice($request->field, $choiceData);
            } else {
                $result = $this->applyCreateChoice($request->field, $choiceData);
            }

            // Update the import session data with the resolved ID
            $this->updateImportSessionData($request->import_session_id, $request->field, $request->search_value, $result);

            return response()->json([
                'success' => true,
                'resolved_id' => $result,
                'message' => __('app.fuzzy_match_resolved_successfully')
            ]);
        } catch (\Exception $e) {
            \Log::error('FuzzyMatchingController::applyChoice error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply existing choice (user selected from existing values).
     */
    private function applyExistingChoice(string $field, array $choiceData): int
    {
        return (int) $choiceData['id'];
    }

    /**
     * Apply create choice (user chose to create new value).
     */
    private function applyCreateChoice(string $field, array $choiceData): int
    {
        return DB::transaction(function () use ($field, $choiceData) {
            switch ($field) {
                case 'matter_partner_id':
                    return $this->createLawyer($choiceData);

                case 'circuit_secretary':
                    return $this->createCircuitSecretary($choiceData);

                case 'court_id':
                    return $this->createCourt($choiceData);

                case 'client_capacity_id':
                case 'opponent_capacity_id':
                    return $this->createCapacity($choiceData);

                case 'circuit_name_id':
                    return $this->createCircuit($choiceData);

                default:
                    throw new \InvalidArgumentException("Cannot create new value for field: {$field}");
            }
        });
    }

    /**
     * Create new lawyer.
     */
    private function createLawyer(array $data): int
    {
        $lawyer = Lawyer::create([
            'lawyer_name_ar' => $data['lawyer_name_ar'],
            'lawyer_name_en' => $data['lawyer_name_en'],
            'email' => $data['email'],
            'title' => $data['title'] ?? 'Associate',
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id()
        ]);

        return $lawyer->id;
    }

    /**
     * Create new court.
     */
    private function createCourt(array $data): int
    {
        $court = Court::create([
            'court_name_ar' => $data['court_name_ar'],
            'court_name_en' => $data['court_name_en'],
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id()
        ]);

        return $court->id;
    }

    /**
     * Create new capacity option value.
     */
    private function createCapacity(array $data): int
    {
        $optionSet = \App\Models\OptionSet::where('key', 'capacity.type')->first();

        if (!$optionSet) {
            throw new \Exception('Capacity option set not found');
        }

        $optionValue = OptionValue::create([
            'set_id' => $optionSet->id,
            'code' => strtolower(str_replace(' ', '_', $data['label_en'])),
            'label_ar' => $data['label_ar'],
            'label_en' => $data['label_en'],
            'position' => 0,
            'is_active' => true,
        ]);

        return $optionValue->id;
    }

    /**
     * Create new circuit option value.
     */
    private function createCircuit(array $data): int
    {
        $optionSet = \App\Models\OptionSet::where('key', 'circuit.name')->first();

        if (!$optionSet) {
            throw new \Exception('Circuit option set not found');
        }

        $optionValue = OptionValue::create([
            'set_id' => $optionSet->id,
            'code' => strtolower(str_replace(' ', '_', $data['label_en'])),
            'label_ar' => $data['label_ar'],
            'label_en' => $data['label_en'],
            'position' => 0,
            'is_active' => true,
        ]);

        return $optionValue->id;
    }

    /**
     * Create new circuit secretary option value.
     */
    private function createCircuitSecretary(array $data): int
    {
        $optionSet = \App\Models\OptionSet::where('key', 'court.circuit_secretary')->first();

        if (!$optionSet) {
            throw new \Exception('Circuit secretary option set not found');
        }

        $optionValue = OptionValue::create([
            'set_id' => $optionSet->id,
            'code' => $data['code'] ?? strtolower(str_replace(' ', '_', $data['label_en'])),
            'label_ar' => $data['label_ar'],
            'label_en' => $data['label_en'],
            'position' => $data['position'] ?? 0,
            'is_active' => true,
        ]);

        return $optionValue->id;
    }

    /**
     * Update import session data with resolved ID.
     */
    private function updateImportSessionData(int $importSessionId, string $field, string $searchValue, int $resolvedId): void
    {
        $session = ImportSession::findOrFail($importSessionId);

        if (!$session->preflight_errors) {
            return;
        }

        $errors = $session->preflight_errors;
        $updated = false;

        // Find and update ALL matching errors (there might be multiple rows with same field/value)
        foreach ($errors as $index => $error) {
            if ($error['column'] === $field && $error['value'] === $searchValue) {
                // Update the error to show it's been resolved
                $errors[$index]['resolved'] = true;
                $errors[$index]['resolved_id'] = $resolvedId;
                $errors[$index]['resolved_at'] = now()->toISOString();
                $updated = true;
                // Don't break - continue to update all matching errors
            }
        }

        if ($updated) {
            // Recalculate error counts
            $errorCount = collect($errors)->where('resolved', false)->count();
            $warningCount = collect($errors)->where('type', 'warning')->where('resolved', false)->count();

            $session->update([
                'preflight_errors' => $errors,
                'preflight_error_count' => $errorCount,
                'preflight_warning_count' => $warningCount,
            ]);

            \Log::info('Import session data updated with resolved ID', [
                'session_id' => $importSessionId,
                'field' => $field,
                'search_value' => $searchValue,
                'resolved_id' => $resolvedId,
                'remaining_errors' => $errorCount
            ]);
        }
    }
}
