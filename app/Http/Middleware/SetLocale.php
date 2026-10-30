<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's saved language preference to every request.
 *
 * The choice lives in a cookie (not local storage) so the server renders the
 * correct language on the very first paint, including for signed-out visitors.
 */
class SetLocale
{
    public const SUPPORTED = ['fr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('locale');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = in_array(config('app.locale'), self::SUPPORTED, true)
                ? config('app.locale')
                : 'fr';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
