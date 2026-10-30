<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['fr', 'en'], true), 404);

        // One year, so the preference survives a return visit.
        Cookie::queue(Cookie::make('locale', $locale, 60 * 24 * 365, '/', null, false, false));

        if ($request->user()) {
            $request->user()->forceFill(['locale' => $locale])->save();
        }

        return back();
    }
}
