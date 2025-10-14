<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaseRequest;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\Court;
use App\Models\OptionValue;
use Illuminate\Http\Request;

class CasesController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', CaseModel::class);
        $cases = CaseModel::with('client:id,client_name_ar,client_name_en')
            ->select('id', 'client_id', 'matter_name_ar', 'matter_name_en', 'matter_status', 'created_at', 'updated_at')
            ->orderBy('matter_name_ar')
            ->paginate(25);
        return view('cases.index', compact('cases'));
    }

    public function create()
    {
        $this->authorize('create', CaseModel::class);
        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();
        
        $courts = Court::where('is_active', true)
            ->orderBy('court_name_ar')
            ->get();
        
        // Load circuit option values
        $circuitNames = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'circuit.name');
        })->where('is_active', true)->orderBy('id')->get();
        
        $circuitSerials = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'circuit.serial');
        })->where('is_active', true)->orderBy('id')->get();
        
        $circuitShifts = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'circuit.shift');
        })->where('is_active', true)->orderBy('id')->get();
        
        return view('cases.create', compact('clients', 'courts', 'circuitNames', 'circuitSerials', 'circuitShifts'));
    }

    public function store(CaseRequest $request)
    {
        $this->authorize('create', CaseModel::class);
        $case = CaseModel::create($request->validated() + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('cases.show', $case)
            ->with('success', __('app.case_created_success'));
    }

    public function show(CaseModel $case)
    {
        $this->authorize('view', $case);
        $case->load('client', 'court', 'circuitName', 'circuitSerial', 'circuitShift', 'circuitSecretaryRef', 'courtFloorRef', 'courtHallRef', 'hearings', 'adminTasks', 'documents');
        return view('cases.show', compact('case'));
    }

    public function edit(CaseModel $case)
    {
        $this->authorize('update', $case);
        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();
        
        $courts = Court::where('is_active', true)
            ->orderBy('court_name_ar')
            ->get();
        
        // Load circuit option values
        $circuitNames = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'circuit.name');
        })->where('is_active', true)->orderBy('id')->get();
        
        $circuitSerials = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'circuit.serial');
        })->where('is_active', true)->orderBy('id')->get();
        
        $circuitShifts = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'circuit.shift');
        })->where('is_active', true)->orderBy('id')->get();
        
        return view('cases.edit', compact('case', 'clients', 'courts', 'circuitNames', 'circuitSerials', 'circuitShifts'));
    }

    public function update(CaseRequest $request, CaseModel $case)
    {
        $this->authorize('update', $case);
        $case->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('cases.show', $case)
            ->with('success', __('app.case_updated_success'));
    }

    public function destroy(CaseModel $case)
    {
        $this->authorize('delete', $case);
        $case->delete();
        return redirect()->route('cases.index')->with('success', __('app.case_deleted_success'));
    }

    /**
     * AJAX endpoint to get court details for cascading dropdowns
     */
    public function getCourtDetails(Court $court)
    {
        $court->load(['circuits.circuitName', 'circuits.circuitSerial', 'circuits.circuitShift', 'secretaries', 'floors', 'halls']);
        
        return response()->json([
            'circuits' => $court->circuits->map(function($circuit) {
                return [
                    'id' => $circuit->id,
                    'label' => $circuit->full_name, // Uses the accessor from CourtCircuit model
                ];
            }),
            'secretaries' => $court->secretaries->map(function($secretary) {
                return [
                    'id' => $secretary->id,
                    'label' => app()->getLocale() === 'ar' ? $secretary->label_ar : $secretary->label_en,
                ];
            }),
            'floors' => $court->floors->map(function($floor) {
                return [
                    'id' => $floor->id,
                    'label' => app()->getLocale() === 'ar' ? $floor->label_ar : $floor->label_en,
                ];
            }),
            'halls' => $court->halls->map(function($hall) {
                return [
                    'id' => $hall->id,
                    'label' => app()->getLocale() === 'ar' ? $hall->label_ar : $hall->label_en,
                ];
            }),
        ]);
    }
}
