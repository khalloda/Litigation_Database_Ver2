<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OptionSet;
use App\Models\OptionValue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OptionController extends Controller
{
    /**
     * Get options by set key
     */
    public function getBySetKey(string $setKey): JsonResponse
    {
        $optionSet = OptionSet::byKey($setKey)->first();

        if (!$optionSet) {
            return response()->json(['error' => 'Option set not found'], 404);
        }

        $options = $optionSet->activeOptionValues()
            ->select('id', 'code', 'label_en', 'label_ar')
            ->get()
            ->map(function ($option) {
                return [
                    'id' => $option->id,
                    'code' => $option->code,
                    'label_en' => $option->label_en,
                    'label_ar' => $option->label_ar,
                ];
            });

        return response()->json($options);
    }

    /**
     * List all option sets
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $optionSets = OptionSet::with('optionValues')
                ->orderBy('name_en')
                ->paginate($request->get('per_page', 25));

            return response()->json($optionSets);
        } catch (\Exception $e) {
            \Log::error('OptionController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch option sets',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show option set with values
     */
    public function show(OptionSet $optionSet): JsonResponse
    {
        $optionSet->load('optionValues');
        return response()->json(['data' => $optionSet]);
    }

    /**
     * Store option set
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:option_sets,key',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        $optionSet = OptionSet::create($validated);

        return response()->json([
            'data' => $optionSet,
            'message' => 'Option set created successfully',
        ], 201);
    }

    /**
     * Update option set
     */
    public function update(Request $request, OptionSet $optionSet): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'sometimes|required|string|max:255|unique:option_sets,key,' . $optionSet->id,
            'name_en' => 'sometimes|required|string|max:255',
            'name_ar' => 'sometimes|required|string|max:255',
        ]);

        $optionSet->update($validated);
        $optionSet->load('optionValues');

        return response()->json([
            'data' => $optionSet,
            'message' => 'Option set updated successfully',
        ]);
    }

    /**
     * Delete option set
     */
    public function destroy(OptionSet $optionSet): JsonResponse
    {
        $optionSet->delete();
        return response()->json(['message' => 'Option set deleted successfully']);
    }

    /**
     * Store option value
     */
    public function storeValue(Request $request, OptionSet $optionSet): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'label_en' => 'required|string|max:255',
            'label_ar' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $optionValue = $optionSet->optionValues()->create($validated);

        return response()->json([
            'data' => $optionValue,
            'message' => 'Option value created successfully',
        ], 201);
    }

    /**
     * Update option value
     */
    public function updateValue(Request $request, OptionValue $optionValue): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:255',
            'label_en' => 'sometimes|required|string|max:255',
            'label_ar' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $optionValue->update($validated);

        return response()->json([
            'data' => $optionValue,
            'message' => 'Option value updated successfully',
        ]);
    }

    /**
     * Delete option value
     */
    public function destroyValue(OptionValue $optionValue): JsonResponse
    {
        $optionValue->delete();
        return response()->json(['message' => 'Option value deleted successfully']);
    }
}

