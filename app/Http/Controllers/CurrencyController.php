<?php

namespace App\Http\Controllers;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CurrencyController extends Controller
{
    public function __invoke(Request $request, string $code)
    {
        $code = strtoupper($code);

        abort_unless(array_key_exists($code, Money::all()), 404);

        Cookie::queue(Cookie::make('currency', $code, 60 * 24 * 365, '/', null, false, false));

        return back();
    }
}
