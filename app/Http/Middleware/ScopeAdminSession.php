<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session isolation for the administrative control zone.
 *
 * The public site and the back office share an origin. With a single session
 * cookie, a single XSS anywhere on the public site is running in the exact
 * context that already holds the administrator's authenticated session.
 *
 * This middleware runs *before* StartSession on admin routes and swaps the
 * session cookie to a distinct name scoped to /admin:
 *
 *   public  -> chichochabitat-session  (path /)
 *   admin   -> cch_admin_session       (path /admin)
 *
 * The two therefore never share a session identifier. The admin cookie is not
 * sent to public routes at all, and logout on one side cannot be replayed on
 * the other. Combined with HttpOnly + SameSite=Strict this closes the
 * cross-surface escalation path rather than merely obscuring it.
 */
class ScopeAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionManager = app('session');
        $sessionConfig = config('session');

        $sessionManager->forgetDrivers();
        config([
            'session.cookie' => 'cch_admin_session',
            'session.path' => '/admin',
            'session.http_only' => true,
            'session.same_site' => 'strict',
            // Encrypt session payloads: a file/database read no longer exposes
            // serialised application state.
            'session.encrypt' => true,
        ]);

        try {
            return $next($request);
        } finally {
            $sessionManager->forgetDrivers();
            config(['session' => $sessionConfig]);
        }
    }
}
