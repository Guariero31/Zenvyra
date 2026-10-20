<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\App\Pages\Dashboard;
use App\Http\Middleware\EnsureSubscriptionIsActive;
use App\Http\Middleware\SetTenantSearchPath;
use App\Models\Tenant;
use App\Services\BrandingService;
use App\Services\SubscriptionRenewalNoticeService;
use Filament\Facades\Filament;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
 * Painel do Lojista. Usa o multi-tenant nativo do Filament resolvendo a loja
 * pelo te_slug na URL. Assim que o tenant é resolvido, o SetTenantSearchPath
 * troca o search_path do PostgreSQL para o schema físico daquela loja.
 */
class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $branding = app(BrandingService::class);

        return $panel
            ->id('app')
            ->path('app')
            ->darkMode(false)
            ->sidebarWidth('15rem')
            ->viteTheme('resources/css/filament/app/theme.css')
            ->font('Inter', provider: GoogleFontProvider::class)
            ->brandName(fn (): string => $this->resolverNomeTenant($branding))
            ->brandLogo(fn (): ?HtmlString => $this->resolverLogoTenant($branding))
            ->maxContentWidth('full')
            ->login()
            ->passwordReset()
            ->tenant(Tenant::class, slugAttribute: 'te_slug')
            ->colors(fn (): array => array_replace($this->resolverCoresTenant($branding), ['primary' => Color::hex('#20211f')]))
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): View => view('filament.hooks.branding-head-tenant'),
            )
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                function (): ?View {
                    $tenant = Filament::getTenant();

                    if (! $tenant instanceof Tenant) {
                        return null;
                    }

                    $notice = app(SubscriptionRenewalNoticeService::class)->forTenant($tenant);

                    if ($notice === null) {
                        return null;
                    }

                    return view('filament.app.components.subscription-renewal-notice', [
                        'notice' => $notice,
                    ]);
                },
            )
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\Filament\App\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\Filament\App\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\Filament\App\Widgets')
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
            ])
            ->tenantMiddleware([
                SetTenantSearchPath::class,
                EnsureSubscriptionIsActive::class,
            ], isPersistent: true);
    }

    private function resolverNomeTenant(BrandingService $branding): string
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant
            ? $branding->nomeTenant($tenant)
            : config('app.name');
    }

    private function resolverLogoTenant(BrandingService $branding): ?HtmlString
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant
            ? $branding->logoHtmlTenant($tenant)
            : null;
    }

    /** @return array<string, mixed> */
    private function resolverCoresTenant(BrandingService $branding): array
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant
            ? $branding->coresFilamentTenant($tenant)
            : ['primary' => Color::Indigo];
    }
}
