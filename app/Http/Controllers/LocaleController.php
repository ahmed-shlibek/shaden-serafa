<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __invoke(string $locale): RedirectResponse
    {
        if (in_array($locale, SetLocale::SUPPORTED, true)) {
            session()->put('locale', $locale);
        }

        return redirect()->back();
    }
}
