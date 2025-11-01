<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ImportProfilesController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('import.manage');

        $query = ImportProfile::query()->orderBy('updated_at', 'desc');
        if ($t = $request->get('table')) {
            $query->where('table_name', $t);
        }
        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->boolean('active'));
        }
        $profiles = $query->paginate(20);

        return view('admin.import.profiles.index', compact('profiles'));
    }

    public function store(Request $request)
    {
        Gate::authorize('import.manage');

        $data = $request->validate([
            'name' => 'required|string|max:128|unique:import_profiles,name',
            'table_name' => 'required|string|max:64',
            'header_hash' => 'nullable|string|max:64',
        ]);
        $data['created_by'] = auth()->id();
        ImportProfile::create($data);

        return back()->with('success', __('app.saved'));
    }

    public function export(ImportProfile $profile)
    {
        Gate::authorize('import.manage');
        $payload = [
            'profile' => $profile->only(['name', 'table_name', 'header_hash', 'settings_json', 'is_active']),
            'choices' => $profile->choices()->get()->toArray(),
        ];
        return response()->json($payload);
    }

    public function import(Request $request, ImportProfile $profile)
    {
        Gate::authorize('import.manage');
        $data = $request->validate([
            'json' => 'required',
        ]);
        $payload = is_array($data['json']) ? $data['json'] : json_decode($data['json'], true);
        foreach (($payload['choices'] ?? []) as $c) {
            $profile->choices()->updateOrCreate([
                'table_name' => $profile->table_name,
                'column' => $c['column'],
                'normalized_value' => $c['normalized_value'],
            ], [
                'raw_value' => $c['raw_value'] ?? null,
                'action' => $c['action'] ?? 'match',
                'entity_model' => $c['entity_model'] ?? null,
                'entity_id' => $c['entity_id'] ?? null,
                'metadata_json' => $c['metadata_json'] ?? null,
                'is_active' => (bool) ($c['is_active'] ?? true),
                'updated_by' => auth()->id(),
            ]);
        }
        return back()->with('success', __('app.import_completed_successfully'));
    }
}
