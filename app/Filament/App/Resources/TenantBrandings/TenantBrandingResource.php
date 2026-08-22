<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\TenantBrandings;

use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\TenantBrandings\Pages\EditTenantBranding;
use App\Filament\App\Resources\TenantBrandings\Schemas\TenantBrandingForm;
use App\Models\Tenant;
use App\Models\TenantTheme;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

use function Filament\Support\original_request;

class TenantBrandingResource extends Resource
{
    protected static ?string $model = TenantTheme::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static ?string $modelLabel = 'identidade visual';

    protected static ?string $pluralModelLabel = 'identidade visual';

    protected static ?string $navigationLabel = 'Identidade Visual';

    protected static \UnitEnum|string|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 82;

    protected static ?string $slug = 'configuracoes/identidade-visual';

    protected static ?string $recordTitleAttribute = 'nome_loja';

    public static function form(Schema $schema): Schema
    {
        return TenantBrandingForm::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditTenantBranding::route('/'),
        ];
    }

    public static function getNavigationItems(): array
    {
        if (! static::canAccess()) {
            return [];
        }

        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => original_request()->routeIs(static::getRouteBaseName().'.*'))
                ->sort(static::getNavigationSort())
                ->url(static::getNavigationUrl()),
        ];
    }

    public static function getNavigationUrl(?string $name = null, bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        $tenantAtual = Filament::getTenant();

        if (! $tenantAtual instanceof Tenant) {
            return '#';
        }

        // Find existing theme without creating — creation happens in mount() on page load
        $theme = TenantTheme::where('tenant_id', $tenantAtual->getKey())->first()
            ?? TenantTheme::obterOuCriarParaTenant((int) $tenantAtual->getKey());

        if (! $theme->exists || $theme->getKey() === null) {
            return '#';
        }

        return static::getUrl('edit', ['record' => $theme->getKey()], $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        $tenantAtual = Filament::getTenant();

        if (! $tenantAtual instanceof Tenant) {
            return '#';
        }

        $theme = TenantTheme::where('tenant_id', $tenantAtual->getKey())->first()
            ?? TenantTheme::obterOuCriarParaTenant((int) $tenantAtual->getKey());

        if (! $theme->exists || $theme->getKey() === null) {
            return '#';
        }

        return static::getUrl('edit', ['record' => $theme->getKey(), ...$parameters], $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }

    public static function canAccess(): bool
    {
        return static::usuarioPodeGerenciarBranding(auth()->user());
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        if (! $user instanceof User || ! $tenant instanceof Tenant || ! $record instanceof TenantTheme) {
            return false;
        }

        return static::usuarioPodeGerenciarBranding($user)
            && (int) $record->tenant_id === (int) $tenant->getKey();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    private static function usuarioPodeGerenciarBranding(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        $tenant = Filament::getTenant();

        if (! $tenant instanceof Tenant) {
            return false;
        }

        $papel = $user->roleInTenant($tenant);

        return in_array($papel, [TenantPortalRole::AdministradorCliente, TenantPortalRole::Leader], true);
    }
}
