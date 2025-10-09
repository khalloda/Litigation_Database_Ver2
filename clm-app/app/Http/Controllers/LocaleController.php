<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $locale = in_array($locale, ['en', 'ar']) ? $locale : config('app.locale');
        session(['locale' => $locale]);
        return back();
    }
}


