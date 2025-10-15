<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaseRequest;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\Court;
use App\Models\Lawyer;
use App\Models\OptionValue;
use App\Models\Opponent;
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
        
        // Partner lawyers (titles limited to partner variants)
        $partnerTitleCodes = ['title_managing_partner','title_senior_partner','title_partner','title_junior_partner'];
        $partnerTitleIds = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','lawyer.title'))
            ->whereIn('code', $partnerTitleCodes)->pluck('id')->all();
        $partnerLawyers = Lawyer::when(!empty($partnerTitleIds), function($q) use ($partnerTitleIds) {
                $q->whereIn('title_id', $partnerTitleIds);
            })
            ->orderBy('lawyer_name_ar')
            ->get(['id','lawyer_name_ar','lawyer_name_en','title_id']);

        // Case option lists
        $caseCategories = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.category'))->where('is_active', true)->orderBy('id')->get();
        $caseDegrees    = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.degree'))->where('is_active', true)->orderBy('id')->get();
        $caseStatuses   = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.status'))->where('is_active', true)->orderBy('id')->get();
        $caseImportance = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.importance'))->where('is_active', true)->orderBy('id')->get();
        $caseBranches   = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.branch'))->where('is_active', true)->orderBy('id')->get();
        $capacityTypes  = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','capacity.type'))->where('is_active', true)->orderBy('id')->get();

        // Opponents list
        $opponents = Opponent::where('is_active', true)->orderBy('opponent_name_ar')->get(['id','opponent_name_ar','opponent_name_en']);

        // Circuit option values
        $circuitNames = OptionValue::whereHas('optionSet', function ($q) { $q->where('key', 'circuit.name'); })->where('is_active', true)->orderBy('id')->get();
        $circuitSerials = OptionValue::whereHas('optionSet', function ($q) { $q->where('key', 'circuit.serial'); })->where('is_active', true)->orderBy('id')->get();
        $circuitShifts = OptionValue::whereHas('optionSet', function ($q) { $q->where('key', 'circuit.shift'); })->where('is_active', true)->orderBy('id')->get();
        
        return view('cases.create', compact(
            'clients','courts','partnerLawyers','caseCategories','caseDegrees','caseStatuses','caseImportance','caseBranches','capacityTypes','opponents',
            'circuitNames','circuitSerials','circuitShifts'
        ));
    }

    public function store(CaseRequest $request)
    {
        $this->authorize('create', CaseModel::class);
        $data = $request->validated();

        // Auto-populate client_type_id from Client's cash_or_probono if missing
        if (empty($data['client_type_id']) && !empty($data['client_id'])) {
            $client = Client::find($data['client_id']);
            if ($client) {
                $candidateId = $client->cash_or_probono_id ?? null;
                if (!$candidateId && !empty($client->cash_or_probono)) {
                    $candidateId = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','client.cash_or_probono'))
                        ->where(fn($q) => $q->where('label_en',$client->cash_or_probono)->orWhere('label_ar',$client->cash_or_probono))
                        ->value('id');
                }
                if ($candidateId) { $data['client_type_id'] = $candidateId; }
            }
        }

        $case = CaseModel::create($data + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('cases.show', $case)->with('success', __('app.case_created_success'));
    }

    public function show(CaseModel $case)
    {
        $this->authorize('view', $case);
        $case->load(
            'client', 'court', 'circuitName', 'circuitSerial', 'circuitShift', 'circuitSecretaryRef', 'courtFloorRef', 'courtHallRef',
            'matterCategory','matterDegree','matterStatus','matterImportance','matterBranch',
            'clientCapacity','clientType','opponent','opponentCapacity','matterDestinationRef','matterPartnerRef',
            'hearings', 'adminTasks', 'documents'
        );
        return view('cases.show', compact('case'));
    }

    public function edit(CaseModel $case)
    {
        $this->authorize('update', $case);
        $clients = Client::select('id', 'client_name_ar', 'client_name_en')->orderBy('client_name_ar')->get();
        $courts = Court::where('is_active', true)->orderBy('court_name_ar')->get();

        $partnerTitleCodes = ['title_managing_partner','title_senior_partner','title_partner','title_junior_partner'];
        $partnerTitleIds = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','lawyer.title'))
            ->whereIn('code', $partnerTitleCodes)->pluck('id')->all();
        $partnerLawyers = Lawyer::when(!empty($partnerTitleIds), function($q) use ($partnerTitleIds) { $q->whereIn('title_id', $partnerTitleIds); })
            ->orderBy('lawyer_name_ar')->get(['id','lawyer_name_ar','lawyer_name_en','title_id']);

        $caseCategories = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.category'))->where('is_active', true)->orderBy('id')->get();
        $caseDegrees    = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.degree'))->where('is_active', true)->orderBy('id')->get();
        $caseStatuses   = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.status'))->where('is_active', true)->orderBy('id')->get();
        $caseImportance = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.importance'))->where('is_active', true)->orderBy('id')->get();
        $caseBranches   = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','case.branch'))->where('is_active', true)->orderBy('id')->get();
        $capacityTypes  = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','capacity.type'))->where('is_active', true)->orderBy('id')->get();
        $opponents      = Opponent::where('is_active', true)->orderBy('opponent_name_ar')->get(['id','opponent_name_ar','opponent_name_en']);

        // Circuit option values
        $circuitNames = OptionValue::whereHas('optionSet', function ($q) { $q->where('key', 'circuit.name'); })->where('is_active', true)->orderBy('id')->get();
        $circuitSerials = OptionValue::whereHas('optionSet', function ($q) { $q->where('key', 'circuit.serial'); })->where('is_active', true)->orderBy('id')->get();
        $circuitShifts = OptionValue::whereHas('optionSet', function ($q) { $q->where('key', 'circuit.shift'); })->where('is_active', true)->orderBy('id')->get();
        
        return view('cases.edit', compact(
            'case','clients','courts','partnerLawyers','caseCategories','caseDegrees','caseStatuses','caseImportance','caseBranches','capacityTypes','opponents',
            'circuitNames','circuitSerials','circuitShifts'
        ));
    }

    public function update(CaseRequest $request, CaseModel $case)
    {
        $this->authorize('update', $case);
        $data = $request->validated();

        if (empty($data['client_type_id']) && !empty($data['client_id'])) {
            $client = Client::find($data['client_id']);
            if ($client) {
                $candidateId = $client->cash_or_probono_id ?? null;
                if (!$candidateId && !empty($client->cash_or_probono)) {
                    $candidateId = OptionValue::whereHas('optionSet', fn($q) => $q->where('key','client.cash_or_probono'))
                        ->where(fn($q) => $q->where('label_en',$client->cash_or_probono)->orWhere('label_ar',$client->cash_or_probono))
                        ->value('id');
                }
                if ($candidateId) { $data['client_type_id'] = $candidateId; }
            }
        }

        $case->update($data + [ 'updated_by' => auth()->id() ]);
        return redirect()->route('cases.show', $case)->with('success', __('app.case_updated_success'));
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
                return [ 'id' => $circuit->id, 'label' => $circuit->full_name ];
            }),
            'secretaries' => $court->secretaries->map(function($secretary) {
                return [ 'id' => $secretary->id, 'label' => app()->getLocale() === 'ar' ? $secretary->label_ar : $secretary->label_en ];
            }),
            'floors' => $court->floors->map(function($floor) {
                return [ 'id' => $floor->id, 'label' => app()->getLocale() === 'ar' ? $floor->label_ar : $floor->label_en ];
            }),
            'halls' => $court->halls->map(function($hall) {
                return [ 'id' => $hall->id, 'label' => app()->getLocale() === 'ar' ? $hall->label_ar : $hall->label_en ];
            }),
        ]);
    }
}
