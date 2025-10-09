<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

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
}
