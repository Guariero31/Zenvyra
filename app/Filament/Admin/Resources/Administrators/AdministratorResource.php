<?php

namespace App\Filament\Admin\Resources\Administrators;

use App\Filament\Admin\Resources\Administrators\Pages\CreateAdministrator;
use App\Filament\Admin\Resources\Administrators\Pages\EditAdministrator;
use App\Filament\Admin\Resources\Administrators\Pages\ListAdministrators;
use App\Filament\Admin\Resources\Administrators\Schemas\AdministratorForm;
use App\Filament\Admin\Resources\Administrators\Tables\AdministratorsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdministratorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'administrador';

    protected static ?string $pluralModelLabel = 'administradores';

    protected static ?string $navigationLabel = 'Administradores';

    protected static \UnitEnum|string|null $navigationGroup = 'SaaS';

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_platform_admin', true);
    }

    public static function form(Schema $schema): Schema
    {
        return AdministratorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdministratorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdministrators::route('/'),
            'create' => CreateAdministrator::route('/create'),
            'edit' => EditAdministrator::route('/{record}/edit'),
        ];
    }
}
