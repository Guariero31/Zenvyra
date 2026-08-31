<?php

namespace App\Filament\Admin\Resources\StoreOwners;

use App\Filament\Admin\Resources\StoreOwners\Pages\CreateStoreOwner;
use App\Filament\Admin\Resources\StoreOwners\Pages\EditStoreOwner;
use App\Filament\Admin\Resources\StoreOwners\Pages\ListStoreOwners;
use App\Filament\Admin\Resources\StoreOwners\Schemas\StoreOwnerForm;
use App\Filament\Admin\Resources\StoreOwners\Tables\StoreOwnersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StoreOwnerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $modelLabel = 'lojista';

    protected static ?string $pluralModelLabel = 'lojistas';

    protected static ?string $navigationLabel = 'Lojistas';

    protected static \UnitEnum|string|null $navigationGroup = 'SaaS';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_platform_admin', false);
    }

    public static function form(Schema $schema): Schema
    {
        return StoreOwnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoreOwnersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStoreOwners::route('/'),
            'create' => CreateStoreOwner::route('/create'),
            'edit' => EditStoreOwner::route('/{record}/edit'),
        ];
    }
}
