<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\Client;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Contact::class);

        $contacts = Contact::with('client:id,client_name_ar,client_name_en')
            ->select('id', 'client_id', 'contact_name', 'full_name', 'job_title', 'email', 'business_phone', 'created_at', 'updated_at')
            ->orderBy('contact_name')
            ->paginate(25);

        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        $this->authorize('create', Contact::class);

        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();

        return view('contacts.create', compact('clients'));
    }

    public function store(ContactRequest $request)
    {
        $this->authorize('create', Contact::class);

        $contact = Contact::create($request->validated() + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('contacts.show', $contact)
            ->with('success', __('app.contact_created_success'));
    }

    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);

        $contact->load('client');

        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        $this->authorize('update', $contact);

        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();

        return view('contacts.edit', compact('contact', 'clients'));
    }

    public function update(ContactRequest $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        $contact->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('contacts.show', $contact)
            ->with('success', __('app.contact_updated_success'));
    }

    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', __('app.contact_deleted_success'));
    }
}
