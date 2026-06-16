<?php

namespace App\Filament\App\Resources\Orders;

use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Orders\Pages\CreateOrder;
use App\Filament\App\Resources\Orders\Pages\EditOrder;
use App\Filament\App\Resources\Orders\Pages\ListOrders;
use App\Filament\App\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Filament\App\Resources\Orders\RelationManagers\RefundsRelationManager;
use App\Filament\App\Resources\Orders\Schemas\OrderForm;
use App\Filament\App\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Tenant;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $modelLabel = 'pedido';

    protected static ?string $pluralModelLabel = 'pedidos';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static \UnitEnum|string|null $navigationGroup = 'Vendas';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('seller');

        if (self::currentUserManagesOrders()) {
            return $query;
        }

        $sellerId = Seller::linkedToUser(auth()->id())->value('id');

        return $query->where(function (Builder $query) use ($sellerId): void {
            if ($sellerId) {
                $query->where('seller_id', $sellerId);
            }

            $query->orWhere('user_id', auth()->id());
        });
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            RefundsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    private static function currentUserManagesOrders(): bool
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Tenant || ! auth()->check()) {
            return false;
        }

        return in_array(auth()->user()->roleInTenant($tenant), [
            TenantPortalRole::AdministradorCliente,
            TenantPortalRole::Leader,
        ], true);
    }
}
