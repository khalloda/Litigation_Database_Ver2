<?php

namespace App\Http\Controllers;

use App\Http\Requests\PowerOfAttorneyRequest;
use App\Models\PowerOfAttorney;
use App\Models\Client;
use Illuminate\Http\Request;

class PowerOfAttorneyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PowerOfAttorney::class);

        $powerOfAttorneys = PowerOfAttorney::with('client:id,client_name_ar,client_name_en')
            ->orderBy('issue_date', 'desc')
            ->paginate(25);

        return view('power-of-attorneys.index', compact('powerOfAttorneys'));
    }

    public function create()
    {
        $this->authorize('create', PowerOfAttorney::class);

        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();

        return view('power-of-attorneys.create', compact('clients'));
    }

    public function store(PowerOfAttorneyRequest $request)
    {
        $this->authorize('create', PowerOfAttorney::class);

        $powerOfAttorney = PowerOfAttorney::create($request->validated() + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('power-of-attorneys.show', $powerOfAttorney)
            ->with('success', __('app.power_of_attorney_created_success'));
    }

    public function show(PowerOfAttorney $powerOfAttorney)
    {
        $this->authorize('view', $powerOfAttorney);

        $powerOfAttorney->load('client');

        return view('power-of-attorneys.show', compact('powerOfAttorney'));
    }

    public function edit(PowerOfAttorney $powerOfAttorney)
    {
        $this->authorize('update', $powerOfAttorney);

        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();

        return view('power-of-attorneys.edit', compact('powerOfAttorney', 'clients'));
    }

    public function update(PowerOfAttorneyRequest $request, PowerOfAttorney $powerOfAttorney)
    {
        $this->authorize('update', $powerOfAttorney);

        $powerOfAttorney->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('power-of-attorneys.show', $powerOfAttorney)
            ->with('success', __('app.power_of_attorney_updated_success'));
    }

    public function destroy(PowerOfAttorney $powerOfAttorney)
    {
        $this->authorize('delete', $powerOfAttorney);

        $powerOfAttorney->delete();

        return redirect()->route('power-of-attorneys.index')
            ->with('success', __('app.power_of_attorney_deleted_success'));
    }
}
