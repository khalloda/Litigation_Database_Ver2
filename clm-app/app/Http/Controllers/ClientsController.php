<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Requests\ClientRequest;

class ClientsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);
        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->paginate(25);
        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);
        $cases = \App\Models\CaseModel::where('client_id', $client->id)
            ->select('id', 'matter_name_ar', 'matter_name_en')
            ->orderBy('matter_name_ar')
            ->paginate(15);
        return view('clients.show', compact('client', 'cases'));
    }

    public function create()
    {
        $this->authorize('create', Client::class);

        // Load option values for dropdowns
        $cashOrProbonoOptions = \App\Models\OptionValue::whereHas('optionSet', fn($q) => $q->where('key', 'client.cash_or_probono'))
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $statusOptions = \App\Models\OptionValue::whereHas('optionSet', fn($q) => $q->where('key', 'client.status'))
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $powerOfAttorneyLocationOptions = \App\Models\OptionValue::whereHas('optionSet', fn($q) => $q->where('key', 'client.power_of_attorney_location'))
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $documentsLocationOptions = \App\Models\OptionValue::whereHas('optionSet', fn($q) => $q->where('key', 'client.documents_location'))
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        return view('clients.create', compact(
            'cashOrProbonoOptions',
            'statusOptions',
            'powerOfAttorneyLocationOptions',
            'documentsLocationOptions'
        ));
    }

    public function store(ClientRequest $request)
    {
        $this->authorize('create', Client::class);

        // Prepare data
        $data = [
            'client_name_ar' => $request->client_name_ar,
            'client_name_en' => $request->client_name_en,
            'client_print_name' => $request->client_print_name ?: $request->client_name_en ?: $request->client_name_ar,
            'cash_or_probono_id' => $request->cash_or_probono_id,
            'status_id' => $request->status_id,
            'client_start' => $request->client_start,
            'client_end' => $request->client_end,
            'contact_lawyer' => $request->contact_lawyer,
            'power_of_attorney_location_id' => $request->power_of_attorney_location_id,
            'documents_location_id' => $request->documents_location_id,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['logo'] = $file->storeAs('logos', $filename, 'public');
        }

        $client = Client::create($data);

        return redirect()->route('clients.show', $client)
            ->with('success', __('app.client_created_successfully'));
    }

    public function edit(Client $client)
    {
        $this->authorize('edit', $client);
        return view('clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, Client $client)
    {
        $this->authorize('edit', $client);
        $client->update([
            'client_name_ar' => $request->client_name_ar,
            'client_name_en' => $request->client_name_en,
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('clients.show', $client)->with('success', 'Client updated');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        $client->delete();
        return redirect()->route('clients.index')->with('success', __('app.client_deleted_success'));
    }
}
