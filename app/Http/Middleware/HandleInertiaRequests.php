<?php

namespace App\Http\Middleware;

use App\Models\Page;
use App\Models\Property;
use App\Models\User;
use App\Support\Money;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root Blade template rendered on the first page load.
     */
    protected $rootView = 'app';

    /**
     * Asset version — changing the build invalidates the client cache.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Data shared with every Inertia response.
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'initials' => $user->initials(),
                    'permissions' => $user->effectivePermissions(),
                    'is_admin' => $user->isAdmin(),
                    'member_since' => $user->created_at?->format('F Y'),
                    'backend_url' => $user->hasPanelRole(User::AGENT_PANEL_ROLES)
                        && ! $user->hasPanelRole(User::ADMIN_PANEL_ROLES)
                            ? '/agent'
                            : '/admin',
                ] : null,
            ],

            'locale' => app()->getLocale(),

            // Live currency table + the visitor's saved preference.
            'currencies' => fn (): array => Money::all(),
            'currency' => function () use ($request): string {
                $code = strtoupper((string) $request->cookie('currency', Money::BASE));

                return array_key_exists($code, Money::all())
                    ? $code
                    : Money::BASE;
            },

            // Admin-editable identity and contact details.
            'site' => fn (): array => [
                'name' => Settings::get('site.name', 'ChiChoc-Habitat'),
                'tagline' => Settings::get('footer.tagline'),
                'email' => Settings::get('contact.email'),
                'phone' => Settings::get('contact.phone'),
                'address' => Settings::get('contact.address'),
            ],

            'translations' => fn (): array => $this->translations(),

            // Footer navigation is driven by published pages, so adding a page
            // in the back office immediately appears in the footer. Slugs are
            // unique per language; missing translations fall back to English.
            'footerPages' => fn (): array => Page::query()
                ->published()
                ->orderBy('sort')
                ->get(['slug', 'title', 'group', 'locale'])
                ->sortBy(fn (Page $page): int => $page->locale === app()->getLocale() ? 0 : 1)
                ->unique('slug')
                ->sortBy('sort')
                ->groupBy('group')
                ->map(fn ($rows) => $rows->map(fn ($p) => ['slug' => $p->slug, 'title' => $p->title])->values())
                ->all(),

            // Live counts, so the public site never shows invented numbers.
            'siteStats' => fn (): array => [
                'approved' => Property::query()->where('status', Property::STATUS_APPROVED)->count(),
                'agents' => User::query()
                    ->whereHas('roles', fn ($query) => $query->where('name', 'agent')->where('guard_name', 'web'))
                    ->count(),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }

    /**
     * Flat key → string map for the active language.
     *
     * @return array<string, string>
     */
    protected function translations(): array
    {
        $locale = app()->getLocale();
        $path = lang_path("{$locale}.json");

        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
