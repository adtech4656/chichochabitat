<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\RolePermissions;
use App\Filament\Pages\SiteConfiguration;
use App\Filament\Resources\BranchResource;
use App\Filament\Resources\CurrencyResource;
use App\Filament\Resources\DepartmentResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PropertyResource;
use App\Filament\Resources\StaffProfileResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\VerificationStats;
use App\Http\Middleware\ScopeAdminSession;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile()
            ->darkMode(condition: true, isForced: true)
            ->brandName('ChiChoc-Habitat')
            ->colors([
                // Neon trust green, expanded into a full Tailwind shade ramp.
                'primary' => Color::hex('#66BB6A'),
                'success' => Color::hex('#66BB6A'),
                'warning' => Color::hex('#FFA726'),
                'danger' => Color::hex('#E53935'),
                'gray' => Color::Stone,
            ])
            ->resources([
                BranchResource::class,
                DepartmentResource::class,
                StaffProfileResource::class,
                PropertyResource::class,
                UserResource::class,
                PageResource::class,
                CurrencyResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
                RolePermissions::class,
                SiteConfiguration::class,
            ])
            ->widgets([
                VerificationStats::class,
                RecentActivity::class,
            ])
            ->middleware([
                // Must precede StartSession so the cookie swap takes effect.
                ScopeAdminSession::class,
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
        // Filament v3 ships Tailwind-3 CSS, so the design system is applied as a
        // plain override sheet rather than a Vite theme (the app runs Tailwind 4).
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => Blade::render(
                '<link rel="stylesheet" href="{{ asset(\'css/chichochabitat-admin.css\') }}?v=1">'
            ),
        );
    }
}
