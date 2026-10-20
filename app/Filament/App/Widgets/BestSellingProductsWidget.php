<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enums\OrderStatus;
use App\Filament\App\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class BestSellingProductsWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 2];

    protected static ?string $heading = 'Produtos mais vendidos';

    public function table(Table $table): Table
    {
        $soldItems = fn (Builder $query): Builder => $query->whereHas('order', fn (Builder $order): Builder => $order->whereIn('status', [OrderStatus::Paid, OrderStatus::PartiallyRefunded])->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]));

        return $table->query(Product::query()->with('primaryImage')->whereHas('orderItems', $soldItems)
            ->withSum(['orderItems as sold_quantity' => $soldItems], 'quantity')->orderByDesc('sold_quantity')->limit(4))
            ->description('Unidades vendidas neste mês, antes dos estornos parciais.')
            ->paginated(false)->emptyStateHeading('As próximas vendas aparecem aqui')->emptyStateDescription('Acompanhe os produtos mais vendidos do mês.')->emptyStateIcon('heroicon-o-shopping-bag')
            ->columns([
                ImageColumn::make('primaryImage.image_path')->label('')->disk('public')->imageSize(38)->defaultImageUrl(asset('images/product-placeholder.svg')),
                TextColumn::make('title')->label('Produto')->wrap(),
                TextColumn::make('sold_quantity')->label('Vendas')->suffix(' un'),
            ])->recordUrl(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]));
    }
}
