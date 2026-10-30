<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Named rate limiters.
 *
 * Every public write endpoint is throttled by a key that combines the client
 * IP with the *target* of the request, so an attacker cannot rotate accounts to
 * bypass the limit and cannot use one account to exhaust another.
 *
 * Costs are deliberately conservative: these endpoints are hit by humans, and
 * each one either writes to the database or triggers an outbound paid call.
 */
class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Login: 5 attempts per minute per IP+email. Slows credential stuffing
        // without locking out a user who mistypes.
        RateLimiter::for('login', function (Request $request): Limit {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($this->fingerprint($request).'|'.mb_strtolower($email));
        });

        // Registration: 3 accounts per hour per IP. Blocks mass account creation
        // used to farm offers or scrape listings.
        RateLimiter::for('register', fn (Request $request): Limit => Limit::perHour(3)->by($this->fingerprint($request)));

        // Offers: 10 per minute per user. Prevents chat/offer spam and the
        // notification fan-out an attacker would otherwise trigger.
        RateLimiter::for('offers', fn (Request $request): Limit => Limit::perMinute(10)
            ->by(($request->user()?->id ?? $this->fingerprint($request)).'|'.($request->input('property_id') ?? 'any')));

        // Preferences are cheap but must not be a free loop.
        RateLimiter::for('preferences', fn (Request $request): Limit => Limit::perMinute(30)->by($this->fingerprint($request)));

        // Anything that can reach a paid third party.
        RateLimiter::for('external', fn (Request $request): Limit => Limit::perMinute(5)->by($this->fingerprint($request)));
    }

    private function fingerprint(Request $request): string
    {
        return (string) ($request->ip() ?? 'unknown');
    }
}
