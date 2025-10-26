<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Services\CaseOpponentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CaseOpponentController extends Controller
{
    public function __construct(
        private CaseOpponentService $caseOpponentService
    ) {}

    /**
     * Store a newly attached opponent to a case.
     */
    public function store(Request $request, CaseModel $case): JsonResponse
    {
        $this->authorize('manageOpponents', $case);

        try {
            $validated = $request->validate([
                'opponent_id' => 'required|integer|exists:opponents,id',
                'capacity_id' => 'nullable|integer|exists:option_values,id',
                'alias_text' => 'nullable|string|max:191',
                'is_primary' => 'boolean',
            ]);

            $opponent = $this->caseOpponentService->attachOpponent(
                $case,
                $validated['opponent_id'],
                $validated['capacity_id'] ?? null,
                $validated['is_primary'] ?? false,
                null, // order will be determined automatically
                $validated['alias_text'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => __('app.opponent_added_successfully'),
                'opponent' => [
                    'id' => $opponent->opponent_id,
                    'name_en' => $opponent->opponent->opponent_name_en,
                    'name_ar' => $opponent->opponent->opponent_name_ar,
                    'capacity' => $opponent->capacity ? [
                        'id' => $opponent->capacity->id,
                        'label_en' => $opponent->capacity->label_en,
                        'label_ar' => $opponent->capacity->label_ar,
                    ] : null,
                    'alias_text' => $opponent->alias_text,
                    'is_primary' => $opponent->is_primary,
                    'display_order' => $opponent->display_order,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.validation_error'),
                'errors' => $e->errors()
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.error_adding_opponent')
            ], 500);
        }
    }

    /**
     * Remove an opponent from a case.
     */
    public function destroy(Request $request, CaseModel $case): JsonResponse
    {
        $this->authorize('manageOpponents', $case);

        try {
            $validated = $request->validate([
                'opponent_id' => 'required|integer|exists:opponents,id',
            ]);

            $success = $this->caseOpponentService->detachOpponent(
                $case,
                $validated['opponent_id']
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => __('app.opponent_removed_successfully')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('app.opponent_not_found')
                ], 404);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.validation_error'),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.error_removing_opponent')
            ], 500);
        }
    }

    /**
     * Set an opponent as the primary opponent for a case.
     */
    public function setPrimary(Request $request, CaseModel $case): JsonResponse
    {
        $this->authorize('manageOpponents', $case);

        try {
            $validated = $request->validate([
                'opponent_id' => 'required|integer|exists:opponents,id',
            ]);

            $this->caseOpponentService->setPrimary(
                $case,
                $validated['opponent_id']
            );

            return response()->json([
                'success' => true,
                'message' => __('app.primary_opponent_set_successfully')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.validation_error'),
                'errors' => $e->errors()
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.error_setting_primary_opponent')
            ], 500);
        }
    }

    /**
     * Reorder opponents for a case.
     */
    public function reorder(Request $request, CaseModel $case): JsonResponse
    {
        $this->authorize('manageOpponents', $case);

        try {
            $validated = $request->validate([
                'opponent_ids' => 'required|array|min:1',
                'opponent_ids.*' => 'integer|exists:opponents,id',
            ]);

            $this->caseOpponentService->reorder(
                $case,
                $validated['opponent_ids']
            );

            return response()->json([
                'success' => true,
                'message' => __('app.opponents_reordered_successfully')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.validation_error'),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.error_reordering_opponents')
            ], 500);
        }
    }

    /**
     * Get opponents for a case with their details.
     */
    public function index(CaseModel $case): JsonResponse
    {
        $this->authorize('view', $case);

        try {
            $opponents = $this->caseOpponentService->getOpponentsWithCapacities($case);

            return response()->json([
                'success' => true,
                'opponents' => $opponents->map(function ($opponent) {
                    // Load capacity separately to avoid relationship issues
                    $capacity = null;
                    if ($opponent->pivot->capacity_id) {
                        $capacity = \App\Models\OptionValue::find($opponent->pivot->capacity_id);
                    }

                    return [
                        'id' => $opponent->id,
                        'name_en' => $opponent->opponent_name_en,
                        'name_ar' => $opponent->opponent_name_ar,
                        'capacity' => $capacity ? [
                            'id' => $capacity->id,
                            'label_en' => $capacity->label_en,
                            'label_ar' => $capacity->label_ar,
                        ] : null,
                        'alias_text' => $opponent->pivot->alias_text,
                        'is_primary' => $opponent->pivot->is_primary,
                        'display_order' => $opponent->pivot->display_order,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading opponents: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => __('app.error_loading_opponents')
            ], 500);
        }
    }
}
