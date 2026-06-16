<?php

namespace App\Filament\App\Resources\Sellers;

use App\Filament\App\Resources\Sellers\Pages\CreateSeller;
use App\Filament\App\Resources\Sellers\Pages\EditSeller;
use App\Filament\App\Resources\Sellers\Pages\ListSellers;
use App\Filament\App\Resources\Sellers\Schemas\SellerForm;
use App\Filament\App\Resources\Sellers\Tables\SellersTable;
use App\Models\Seller;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SellerResource extends Resource
{
    protected static ?string $model = Seller::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'vendedor';

    protected static ?string $pluralModelLabel = 'vendedores';

    protected static ?string $navigationLabel = 'Vendedores';

    protected static \UnitEnum|string|null $navigationGroup = 'Vendas';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SellerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SellersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSellers::route('/'),
            'create' => CreateSeller::route('/create'),
            'edit' => EditSeller::route('/{record}/edit'),
        ];
    }
}
