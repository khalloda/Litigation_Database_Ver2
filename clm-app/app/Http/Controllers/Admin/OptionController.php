<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OptionSet;
use App\Models\OptionValue;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    /**
     * Get active option values for a specific set key
     */
    public function getOptions(string $setKey)
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
                    'label' => $option->label, // This uses the accessor for current locale
                    'label_en' => $option->label_en,
                    'label_ar' => $option->label_ar,
                ];
            });

        return response()->json($options);
    }

    /**
     * Get all option sets for admin management
     */
    public function index()
    {
        $optionSets = OptionSet::with('optionValues')->orderBy('name_en')->get();

        return view('admin.options.index', compact('optionSets'));
    }

    /**
     * Show option set details with values
     */
    public function show(OptionSet $optionSet)
    {
        $optionSet->load('optionValues');

        return view('admin.options.show', compact('optionSet'));
    }

    /**
     * Show form to create new option set
     */
    public function create()
    {
        return view('admin.options.create');
    }

    /**
     * Store new option set
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:option_sets,key',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
        ]);

        OptionSet::create($request->all());

        return redirect()->route('admin.options.index')
            ->with('success', 'Option set created successfully.');
    }

    /**
     * Show form to edit option set
     */
    public function edit(OptionSet $optionSet)
    {
        $optionSet->load('optionValues');

        return view('admin.options.edit', compact('optionSet'));
    }

    /**
     * Update option set
     */
    public function update(Request $request, OptionSet $optionSet)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $optionSet->update($request->all());

        return redirect()->route('admin.options.index')
            ->with('success', 'Option set updated successfully.');
    }

    /**
     * Delete option set
     */
    public function destroy(OptionSet $optionSet)
    {
        $usageCount = $optionSet->getUsageCount();

        if ($usageCount > 0) {
            return back()->with('error', "Cannot delete option set. It is being used by {$usageCount} records.");
        }

        $optionSet->delete();

        return redirect()->route('admin.options.index')
            ->with('success', 'Option set deleted successfully.');
    }

    /**
     * Store new option value
     */
    public function storeValue(Request $request, OptionSet $optionSet)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:option_values,code,NULL,id,set_id,' . $optionSet->id,
            'label_en' => 'required|string|max:255',
            'label_ar' => 'required|string|max:255',
            'position' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $optionSet->optionValues()->create($request->all());

        return back()->with('success', 'Option value created successfully.');
    }

    /**
     * Update option value
     */
    public function updateValue(Request $request, OptionValue $optionValue)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:option_values,code,' . $optionValue->id . ',id,set_id,' . $optionValue->set_id,
            'label_en' => 'required|string|max:255',
            'label_ar' => 'required|string|max:255',
            'position' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $optionValue->update($request->all());

        return back()->with('success', 'Option value updated successfully.');
    }

    /**
     * Delete option value
     */
    public function destroyValue(OptionValue $optionValue)
    {
        $usageCount = $optionValue->getUsageCount();

        if ($usageCount > 0) {
            return back()->with('error', "Cannot delete option value. It is being used by {$usageCount} records.");
        }

        $optionValue->delete();

        return back()->with('success', 'Option value deleted successfully.');
    }
}
