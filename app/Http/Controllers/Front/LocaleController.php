<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(string $locale, Request $request)
    {
        if (in_array($locale, ['fr', 'en'])) {
            $request->session()->put('locale', $locale);
            app()->setLocale($locale);
        }

        return redirect()->back();
    }
}
