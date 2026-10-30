<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeAgentSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionManager = app('session');
        $sessionConfig = config('session');

        $sessionManager->forgetDrivers();
        config([
            'session.cookie' => 'cch_agent_session',
            'session.path' => '/agent',
            'session.http_only' => true,
            'session.same_site' => 'strict',
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
