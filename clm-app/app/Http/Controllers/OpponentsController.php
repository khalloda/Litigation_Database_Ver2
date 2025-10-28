<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpponentRequest;
use App\Models\Opponent;
use Illuminate\Http\Request;

class OpponentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Opponent::class);
        $query = Opponent::query();
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('opponent_name_ar', 'like', "%$search%")
                    ->orWhere('opponent_name_en', 'like', "%$search%")
                    ->orWhere('notes', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }
        $opponents = $query->orderBy('opponent_name_ar')->paginate(25);
        return view('opponents.index', compact('opponents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Opponent::class);
        return view('opponents.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OpponentRequest $request)
    {
        $this->authorize('create', Opponent::class);
        $opponent = Opponent::create($request->validated() + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        return redirect()->route('opponents.show', $opponent)->with('success', __('app.opponent_created_success'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Opponent $opponent)
    {
        $this->authorize('view', $opponent);
        return view('opponents.show', compact('opponent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Opponent $opponent)
    {
        $this->authorize('update', $opponent);
        return view('opponents.edit', compact('opponent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OpponentRequest $request, Opponent $opponent)
    {
        $this->authorize('update', $opponent);
        $opponent->update($request->validated() + ['updated_by' => auth()->id()]);
        return redirect()->route('opponents.show', $opponent)->with('success', __('app.opponent_updated_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Opponent $opponent)
    {
        $this->authorize('delete', $opponent);
        $opponent->delete();
        return redirect()->route('opponents.index')->with('success', __('app.opponent_deleted_success'));
    }

    /**
     * Search opponents for AJAX requests (used in add opponent modal).
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $opponents = Opponent::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('opponent_name_en', 'like', "%{$query}%")
                    ->orWhere('opponent_name_ar', 'like', "%{$query}%");
            })
            ->orderBy('opponent_name_en')
            ->limit(10)
            ->get(['id', 'opponent_name_en', 'opponent_name_ar']);

        return response()->json($opponents);
    }
}
