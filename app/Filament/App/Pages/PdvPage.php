<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Enums\TenantPortalRole;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Page;
use Illuminate\Contracts\Auth\Authenticatable;

class PdvPage extends Page
{
    /**
     * Reaproveita o layout "simple" do Filament (o mesmo da tela de Login):
     * sem sidebar nem topbar do painel, ideal para uma tela cheia de PDV.
     * Diferente de Filament\Pages\SimplePage, Page traz o trait HasRoutes
     * (getUrl(), getRouteName()), que a Page do Dashboard precisa para
     * montar o link do botão "Abrir PDV".
     */
    protected static string $layout = 'filament-panels::components.layout.simple';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'pdv';

    protected static ?string $title = 'PDV';

    protected string $view = 'filament.app.pages.pdv';

    public static function canAccess(): bool
    {
        /** @var Authenticatable|FilamentUser|null $user */
        $user = auth()->user();
        $tenant = Filament::getTenant();

        if (! $user || ! $tenant instanceof Tenant) {
            return false;
        }

        $role = $user->roleInTenant($tenant);

        return in_array($role, [
            TenantPortalRole::AdministradorCliente,
            TenantPortalRole::Leader,
            TenantPortalRole::Vendedor,
        ], true);
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function getTitle(): string
    {
        return 'PDV';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => false,
        ];
    }
}
