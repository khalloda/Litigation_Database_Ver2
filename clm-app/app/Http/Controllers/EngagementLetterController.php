<?php

namespace App\Http\Controllers;

use App\Http\Requests\EngagementLetterRequest;
use App\Models\EngagementLetter;
use App\Models\Client;
use Illuminate\Http\Request;

class EngagementLetterController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', EngagementLetter::class);
        
        $engagementLetters = EngagementLetter::with('client:id,client_name_ar,client_name_en')
            ->select('id', 'client_id', 'contract_number', 'issue_date', 'expiry_date', 'is_active', 'created_at', 'updated_at')
            ->orderBy('issue_date', 'desc')
            ->paginate(25);

        return view('engagement-letters.index', compact('engagementLetters'));
    }

    public function create()
    {
        $this->authorize('create', EngagementLetter::class);
        
        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();

        return view('engagement-letters.create', compact('clients'));
    }

    public function store(EngagementLetterRequest $request)
    {
        $this->authorize('create', EngagementLetter::class);
        
        $engagementLetter = EngagementLetter::create($request->validated() + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('engagement-letters.show', $engagementLetter)
            ->with('success', __('app.engagement_letter_created_success'));
    }

    public function show(EngagementLetter $engagementLetter)
    {
        $this->authorize('view', $engagementLetter);
        
        $engagementLetter->load('client');
        
        return view('engagement-letters.show', compact('engagementLetter'));
    }

    public function edit(EngagementLetter $engagementLetter)
    {
        $this->authorize('update', $engagementLetter);
        
        $clients = Client::select('id', 'client_name_ar', 'client_name_en')
            ->orderBy('client_name_ar')
            ->get();

        return view('engagement-letters.edit', compact('engagementLetter', 'clients'));
    }

    public function update(EngagementLetterRequest $request, EngagementLetter $engagementLetter)
    {
        $this->authorize('update', $engagementLetter);
        
        $engagementLetter->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('engagement-letters.show', $engagementLetter)
            ->with('success', __('app.engagement_letter_updated_success'));
    }

    public function destroy(EngagementLetter $engagementLetter)
    {
        $this->authorize('delete', $engagementLetter);
        
        $engagementLetter->delete();

        return redirect()->route('engagement-letters.index')
            ->with('success', __('app.engagement_letter_deleted_success'));
    }
}
