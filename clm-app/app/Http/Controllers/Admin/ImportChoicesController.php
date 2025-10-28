<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportProfile;
use App\Models\ImportChoice;
use Illuminate\Http\Request;

class ImportChoicesController extends Controller
{
    public function index(Request $request, ImportProfile $profile)
    {
        $this->authorize('manage', ImportProfile::class);
        $query = $profile->choices()->orderBy('updated_at', 'desc');
        if ($c = $request->get('column')) $query->where('column', $c);
        if ($a = $request->get('action')) $query->where('action', $a);
        if ($s = $request->get('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('raw_value', 'like', "%$s%")
                  ->orWhere('normalized_value', 'like', "%$s%");
            });
        }
        $choices = $query->paginate(20);
        return view('admin.import.choices.index', compact('profile', 'choices'));
    }

    public function store(Request $request, ImportProfile $profile)
    {
        $this->authorize('manage', ImportProfile::class);
        $data = $request->validate([
            'column' => 'required|string|max:128',
            'raw_value' => 'nullable|string|max:255',
            'normalized_value' => 'required|string|max:255',
            'action' => 'required|in:match,alias,capacity,ignore',
            'entity_model' => 'nullable|string|max:128',
            'entity_id' => 'nullable|integer',
        ]);
        $data['table_name'] = $profile->table_name;
        $data['created_by'] = auth()->id();
        $profile->choices()->create($data);
        return back()->with('success', __('app.saved'));
    }

    public function update(Request $request, ImportProfile $profile, ImportChoice $choice)
    {
        $this->authorize('manage', ImportProfile::class);
        $data = $request->validate([
            'raw_value' => 'nullable|string|max:255',
            'action' => 'nullable|in:match,alias,capacity,ignore',
            'entity_model' => 'nullable|string|max:128',
            'entity_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $data['updated_by'] = auth()->id();
        $choice->update($data);
        return back()->with('success', __('app.saved'));
    }

    public function destroy(ImportProfile $profile, ImportChoice $choice)
    {
        $this->authorize('manage', ImportProfile::class);
        $choice->delete();
        return back()->with('success', __('app.deleted_successfully'));
    }
}


