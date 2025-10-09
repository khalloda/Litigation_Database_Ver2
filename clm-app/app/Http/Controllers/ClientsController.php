<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Requests\ClientRequest;

class ClientsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class, auth()->user());
        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->paginate(25);
        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client, auth()->user());
        $cases = \App\Models\CaseModel::where('client_id', $client->id)
            ->select('id', 'matter_name_ar', 'matter_name_en')
            ->orderBy('matter_name_ar')
            ->paginate(15);
        return view('clients.show', compact('client', 'cases'));
    }

    public function create()
    {
        $this->authorize('create', Client::class, auth()->user());
        return view('clients.create');
    }

    public function store(ClientRequest $request)
    {
        $this->authorize('create', Client::class, auth()->user());
        $client = Client::create([
            'client_name_ar' => $request->client_name_ar,
            'client_name_en' => $request->client_name_en,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('clients.show', $client)->with('success', 'Client created');
    }

    public function edit(Client $client)
    {
        $this->authorize('edit', $client, auth()->user());
        return view('clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, Client $client)
    {
        $this->authorize('edit', $client, auth()->user());
        $client->update([
            'client_name_ar' => $request->client_name_ar,
            'client_name_en' => $request->client_name_en,
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('clients.show', $client)->with('success', 'Client updated');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client, auth()->user());
        $client->delete();
        return redirect()->route('clients.index')->with('success', __('app.client_deleted_success'));
    }
}
