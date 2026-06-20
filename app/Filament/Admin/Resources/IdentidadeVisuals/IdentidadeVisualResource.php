<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\IdentidadeVisuals;

use App\Filament\Admin\Resources\IdentidadeVisuals\Pages\EditIdentidadeVisual;
use App\Filament\Admin\Resources\IdentidadeVisuals\Schemas\IdentidadeVisualForm;
use App\Models\IdentidadeVisual;
use App\Models\User;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

use function Filament\Support\original_request;

class IdentidadeVisualResource extends Resource
{
    protected static ?string $model = IdentidadeVisual::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static ?string $modelLabel = 'identidade visual';

    protected static ?string $pluralModelLabel = 'identidade visual';

    protected static ?string $navigationLabel = 'Identidade Visual';

    protected static \UnitEnum|string|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'configuracoes/identidade-visual';

    protected static ?string $recordTitleAttribute = 'nome_sistema';

    public static function form(Schema $schema): Schema
    {
        return IdentidadeVisualForm::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditIdentidadeVisual::route('/'),
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
        $registro = IdentidadeVisual::obterOuCriar();

        return static::getUrl('edit', ['record' => $registro], $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        $registro = IdentidadeVisual::obterOuCriar();

        return static::getUrl('edit', ['record' => $registro, ...$parameters], $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->is_platform_admin;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
