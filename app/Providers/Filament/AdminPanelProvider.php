<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Dashboard;
use App\Services\BrandingService;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Painel Master do dono do SaaS. Roda na central (public), sem escopo de tenant.
 * Aqui são gerenciados os tenants globais, os usuários da plataforma e os planos.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $branding = app(BrandingService::class);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->font('Inter', provider: GoogleFontProvider::class)
            ->brandName(fn (): string => $branding->nomeMaster())
            ->brandLogo(fn (): ?HtmlString => $branding->logoHtmlMaster())
            ->login()
            ->colors(fn (): array => $branding->coresFilamentMaster())
            ->navigationGroups([
                NavigationGroup::make('SaaS'),
                NavigationGroup::make('Configurações'),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): View => view('filament.hooks.branding-head'),
            )
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
