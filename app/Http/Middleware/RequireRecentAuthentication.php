<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Step-up re-authentication for destructive back-office actions.
 *
 * A session that has been open for hours — or that was established on a shared
 * machine — is a weaker claim than a session confirmed minutes ago. Sensitive
 * writes require a recent confirmation, recorded in the session at login and
 * refreshed when an administrator re-enters their password.
 */
class RequireRecentAuthentication
{
    /** How long a confirmation is considered fresh, in seconds. */
    private const FRESH_FOR = 900; // 15 minutes

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $confirmedAt = (int) $request->session()->get('auth_confirmed_at', 0);
        $isFresh = $confirmedAt > 0 && (time() - $confirmedAt) <= self::FRESH_FOR;

        // Only destructive verbs require the step-up; reads stay cheap.
        if (! $isFresh && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if ($request->expectsJson()) {
                abort(423, 'Please confirm your password to continue.');
            }

            return redirect()
                ->to('/admin/profile')
                ->with('error', 'Your session needs re-confirmation before this action.');
        }

        return $next($request);
    }
}
