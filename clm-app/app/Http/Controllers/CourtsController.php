<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourtRequest;
use App\Models\Court;
use App\Models\CaseModel;
use App\Models\OptionValue;
use Illuminate\Http\Request;

class CourtsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Court::class);
        
        $query = Court::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('court_name_ar', 'like', "%{$search}%")
                  ->orWhere('court_name_en', 'like', "%{$search}%");
            });
        }
        
        // Filter by active status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $courts = $query->orderBy('court_name_ar')->paginate(25);
        
        return view('courts.index', compact('courts'));
    }

    public function create()
    {
        $this->authorize('create', Court::class);
        
        // Load option values for dropdowns
        $circuitOptions = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'court.circuit');
        })->where('is_active', true)->orderBy('id')->get();
        
        $secretaryOptions = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'court.circuit_secretary');
        })->where('is_active', true)->orderBy('id')->get();
        
        $floorOptions = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'court.floor');
        })->where('is_active', true)->orderBy('id')->get();
        
        $hallOptions = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'court.hall');
        })->where('is_active', true)->orderBy('id')->get();
        
        return view('courts.create', compact('circuitOptions', 'secretaryOptions', 'floorOptions', 'hallOptions'));
    }

    public function store(CourtRequest $request)
    {
        $this->authorize('create', Court::class);
        
        $court = Court::create([
            'court_name_ar' => $request->court_name_ar,
            'court_name_en' => $request->court_name_en,
            'is_active' => $request->is_active ?? true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        
        // Sync many-to-many relationships
        if ($request->filled('court_circuits')) {
            $court->circuits()->sync($request->court_circuits);
        }
        if ($request->filled('court_secretaries')) {
            $court->secretaries()->sync($request->court_secretaries);
        }
        if ($request->filled('court_floors')) {
            $court->floors()->sync($request->court_floors);
        }
        if ($request->filled('court_halls')) {
            $court->halls()->sync($request->court_halls);
        }
        
        return redirect()->route('courts.show', $court)
            ->with('success', __('app.court_created_success'));
    }

    public function show(Court $court)
    {
        $this->authorize('view', $court);
        
        // Load many-to-many relationships
        $court->load(['circuits', 'secretaries', 'floors', 'halls']);
        
        // Load related cases with pagination
        $cases = CaseModel::where('court_id', $court->id)
            ->with(['client:id,client_name_ar,client_name_en'])
            ->select('id', 'client_id', 'court_id', 'matter_name_ar', 'matter_name_en', 'matter_status', 'matter_start_date')
            ->orderBy('matter_start_date', 'desc')
            ->paginate(15);
        
        // Placeholder for hearings and tasks
        $hearings = collect([]);
        $tasks = collect([]);
        
        return view('courts.show', compact('court', 'cases', 'hearings', 'tasks'));
    }

    public function edit(Court $court)
    {
        $this->authorize('update', $court);
        
        // Load current many-to-many relationships
        $court->load(['circuits', 'secretaries', 'floors', 'halls']);
        
        // Load option values for dropdowns
        $circuitOptions = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'court.circuit');
        })->where('is_active', true)->orderBy('id')->get();
        
        $secretaryOptions = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'court.circuit_secretary');
        })->where('is_active', true)->orderBy('id')->get();
        
        $floorOptions = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'court.floor');
        })->where('is_active', true)->orderBy('id')->get();
        
        $hallOptions = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'court.hall');
        })->where('is_active', true)->orderBy('id')->get();
        
        return view('courts.edit', compact('court', 'circuitOptions', 'secretaryOptions', 'floorOptions', 'hallOptions'));
    }

    public function update(CourtRequest $request, Court $court)
    {
        $this->authorize('update', $court);
        
        $court->update([
            'court_name_ar' => $request->court_name_ar,
            'court_name_en' => $request->court_name_en,
            'is_active' => $request->is_active ?? $court->is_active,
            'updated_by' => auth()->id(),
        ]);
        
        // Sync many-to-many relationships
        $court->circuits()->sync($request->court_circuits ?? []);
        $court->secretaries()->sync($request->court_secretaries ?? []);
        $court->floors()->sync($request->court_floors ?? []);
        $court->halls()->sync($request->court_halls ?? []);
        
        return redirect()->route('courts.show', $court)
            ->with('success', __('app.court_updated_success'));
    }

    public function destroy(Court $court)
    {
        $this->authorize('delete', $court);
        
        // Check if court has related cases
        if ($court->cases()->count() > 0) {
            return back()->with('error', __('app.court_has_cases'));
        }
        
        $court->delete();
        
        return redirect()->route('courts.index')
            ->with('success', __('app.court_deleted_success'));
    }
}
