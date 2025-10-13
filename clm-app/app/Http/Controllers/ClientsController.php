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

        // Get filter parameters
        $search = $request->get('search');
        $status_id = $request->get('status_id');
        $cash_or_probono_id = $request->get('cash_or_probono_id');
        $contact_lawyer_id = $request->get('contact_lawyer_id');

        // Build query
        $query = Client::select('id', 'client_name_ar', 'client_name_en', 'contact_lawyer_id', 'status_id', 'cash_or_probono_id')
            ->with([
                'contactLawyer:id,lawyer_name_ar,lawyer_name_en',
                'statusRef:id,label_ar,label_en',
                'cashOrProbono:id,label_ar,label_en',
                'cases:id,client_id' // For case count
            ])
            ->withCount('cases');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('client_name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('client_name_en', 'LIKE', "%{$search}%")
                    ->orWhere('client_print_name', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status_id) {
            $query->where('status_id', $status_id);
        }

        // Apply cash or probono filter
        if ($cash_or_probono_id) {
            $query->where('cash_or_probono_id', $cash_or_probono_id);
        }

        // Apply contact lawyer filter
        if ($contact_lawyer_id) {
            $query->where('contact_lawyer_id', $contact_lawyer_id);
        }

        // Order and paginate
        $clients = $query->orderBy(app()->getLocale() == 'ar' ? 'client_name_ar' : 'client_name_en')
            ->paginate(25)
            ->appends($request->query());

        // Get filter options
        $statuses = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'client.status');
        })->orderBy('id')->get();

        $cashOrProbonoOptions = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'client.cash_or_probono');
        })->orderBy('id')->get();

        $lawyers = \App\Models\Lawyer::select('id', 'lawyer_name_ar', 'lawyer_name_en')
            ->orderBy(app()->getLocale() == 'ar' ? 'lawyer_name_ar' : 'lawyer_name_en')
            ->get();

        return view('clients.index', compact('clients', 'statuses', 'cashOrProbonoOptions', 'lawyers', 'search', 'status_id', 'cash_or_probono_id', 'contact_lawyer_id'));
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        // Load relationships for the client
        $client->load([
            'cashOrProbono',
            'statusRef',
            'powerOfAttorneyLocation',
            'documentsLocation',
            'contactLawyer',
            'createdBy',
            'updatedBy',
            'activities' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

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

        // Load lawyers for contact lawyer dropdown
        $lawyers = \App\Models\Lawyer::orderBy(app()->getLocale() === 'ar' ? 'lawyer_name_ar' : 'lawyer_name_en')->get();

        return view('clients.create', compact(
            'cashOrProbonoOptions',
            'statusOptions',
            'powerOfAttorneyLocationOptions',
            'documentsLocationOptions',
            'lawyers'
        ));
    }

    public function store(ClientRequest $request)
    {
        $this->authorize('create', Client::class);

        // Get option values for legacy text fields
        $cashOrProbonoValue = $request->cash_or_probono_id ? \App\Models\OptionValue::find($request->cash_or_probono_id)?->label_en : null;
        $statusValue = $request->status_id ? \App\Models\OptionValue::find($request->status_id)?->label_en : null;
        $powerOfAttorneyLocationValue = $request->power_of_attorney_location_id ? \App\Models\OptionValue::find($request->power_of_attorney_location_id)?->label_en : null;
        $documentsLocationValue = $request->documents_location_id ? \App\Models\OptionValue::find($request->documents_location_id)?->label_en : null;
        $contactLawyerValue = $request->contact_lawyer_id ? \App\Models\Lawyer::find($request->contact_lawyer_id)?->lawyer_name_en : null;

        // Prepare data
        $data = [
            // New fields (MFiles ID and Client Code)
            'mfiles_id' => $request->mfiles_id,
            'client_code' => $request->client_code,
            'client_name_ar' => $request->client_name_ar,
            'client_name_en' => $request->client_name_en,
            'client_print_name' => $request->client_print_name ?: $request->client_name_en ?: $request->client_name_ar,
            // FK fields (new)
            'cash_or_probono_id' => $request->cash_or_probono_id,
            'status_id' => $request->status_id,
            'power_of_attorney_location_id' => $request->power_of_attorney_location_id,
            'documents_location_id' => $request->documents_location_id,
            'contact_lawyer_id' => $request->contact_lawyer_id,
            // Legacy text fields (for backward compatibility)
            'cash_or_probono' => $cashOrProbonoValue,
            'status' => $statusValue,
            'power_of_attorney_location' => $powerOfAttorneyLocationValue,
            'documents_location' => $documentsLocationValue,
            'contact_lawyer' => $contactLawyerValue,
            // Other fields
            'client_start' => $request->client_start,
            'client_end' => $request->client_end,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        // Handle logo upload - store directly in public/uploads for production compatibility
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/logos'), $filename);
            $data['logo'] = 'uploads/logos/' . $filename;
        }

        $client = Client::create($data);

        return redirect()->route('clients.show', $client)
            ->with('success', __('app.client_created_successfully'));
    }

    public function edit(Client $client)
    {
        $this->authorize('edit', $client);

        // Load dropdown options
        $cashOrProbonoOptions = \App\Models\OptionValue::whereHas('optionSet', function ($query) {
            $query->where('key', 'client.cash_or_probono');
        })->orderBy('id')->get();

        $statusOptions = \App\Models\OptionValue::whereHas('optionSet', function ($query) {
            $query->where('key', 'client.status');
        })->orderBy('id')->get();

        $powerOfAttorneyLocationOptions = \App\Models\OptionValue::whereHas('optionSet', function ($query) {
            $query->where('key', 'client.power_of_attorney_location');
        })->orderBy('id')->get();

        $documentsLocationOptions = \App\Models\OptionValue::whereHas('optionSet', function ($query) {
            $query->where('key', 'client.documents_location');
        })->orderBy('id')->get();

        $lawyers = \App\Models\Lawyer::select('id', 'lawyer_name_ar', 'lawyer_name_en')
            ->orderBy('lawyer_name_ar')
            ->get();

        return view('clients.edit', compact(
            'client',
            'cashOrProbonoOptions',
            'statusOptions',
            'powerOfAttorneyLocationOptions',
            'documentsLocationOptions',
            'lawyers'
        ));
    }

    public function update(ClientRequest $request, Client $client)
    {
        $this->authorize('edit', $client);

        // Get option values for legacy text fields
        $cashOrProbonoValue = $request->cash_or_probono_id ? \App\Models\OptionValue::find($request->cash_or_probono_id)?->label_en : null;
        $statusValue = $request->status_id ? \App\Models\OptionValue::find($request->status_id)?->label_en : null;
        $powerOfAttorneyLocationValue = $request->power_of_attorney_location_id ? \App\Models\OptionValue::find($request->power_of_attorney_location_id)?->label_en : null;
        $documentsLocationValue = $request->documents_location_id ? \App\Models\OptionValue::find($request->documents_location_id)?->label_en : null;
        $contactLawyerValue = $request->contact_lawyer_id ? \App\Models\Lawyer::find($request->contact_lawyer_id)?->lawyer_name_en : null;

        // Prepare update data
        $data = [
            // New fields (MFiles ID and Client Code)
            'mfiles_id' => $request->mfiles_id,
            'client_code' => $request->client_code,
            'client_name_ar' => $request->client_name_ar,
            'client_name_en' => $request->client_name_en,
            'client_print_name' => $request->client_print_name ?: $request->client_name_en ?: $request->client_name_ar,
            // FK fields (new)
            'cash_or_probono_id' => $request->cash_or_probono_id,
            'status_id' => $request->status_id,
            'power_of_attorney_location_id' => $request->power_of_attorney_location_id,
            'documents_location_id' => $request->documents_location_id,
            'contact_lawyer_id' => $request->contact_lawyer_id,
            // Legacy text fields (for backward compatibility)
            'cash_or_probono' => $cashOrProbonoValue,
            'status' => $statusValue,
            'power_of_attorney_location' => $powerOfAttorneyLocationValue,
            'documents_location' => $documentsLocationValue,
            'contact_lawyer' => $contactLawyerValue,
            // Other fields
            'client_start' => $request->client_start,
            'client_end' => $request->client_end,
            'updated_by' => auth()->id(),
        ];

        // Handle logo upload - store directly in public/uploads for production compatibility
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/logos'), $filename);
            $data['logo'] = 'uploads/logos/' . $filename;
        }

        $client->update($data);
        return redirect()->route('clients.show', $client)->with('success', __('app.client_updated_successfully'));
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        $client->delete();
        return redirect()->route('clients.index')->with('success', __('app.client_deleted_success'));
    }
}
