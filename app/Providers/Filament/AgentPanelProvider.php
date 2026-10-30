<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\AgentPropertyResource;
use App\Http\Middleware\ScopeAgentSession;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * The agent workspace.
 *
 * Deliberately separate from the admin console: agents manage their own
 * listings and their own password, and never see platform settings, keys,
 * other users, or other agents' inventory.
 */
class AgentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('agent')
            ->path('agent')
            ->login(Login::class)
            ->profile()
            ->darkMode(condition: true, isForced: true)
            ->brandName('ChiChoc-Habitat · Agent')
            ->colors([
                'primary' => Color::hex('#66BB6A'),
                'success' => Color::hex('#66BB6A'),
                'warning' => Color::hex('#FFA726'),
                'danger' => Color::hex('#E53935'),
                'gray' => Color::Stone,
            ])
            ->resources([
                AgentPropertyResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([])
            ->middleware([
                // Separate session cookie for the agent workspace as well, so a
                // compromised customer session is never the agent session.
                ScopeAgentSession::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => Blade::render(
                '<link rel="stylesheet" href="{{ asset(\'css/chichochabitat-admin.css\') }}?v=1">'
            ),
        );
    }
}
