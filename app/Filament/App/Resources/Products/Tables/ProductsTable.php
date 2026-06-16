<?php

namespace App\Filament\App\Resources\Products\Tables;

use App\Filament\App\Resources\Products\Actions\StockEntryAction;
use App\Filament\App\Resources\Products\Actions\ToggleProductStatusAction;
use App\Models\Product;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primaryImage.image_path')
                    ->label('Capa')
                    ->disk('public')
                    ->height(48)
                    ->extraImgAttributes(['class' => 'object-cover rounded-md'])
                    ->defaultImageUrl(asset('images/placeholder-product.svg')),
                TextColumn::make('title')
                    ->label('Produto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('price')
                    ->label('Venda')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('cost_price')
                    ->label('Custo')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commission_rate')
                    ->label('Comissão')
                    ->suffix('%')
                    ->placeholder('Padrão do vendedor')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock')
                    ->label('Estoque')
                    ->state(fn (Product $record): int => $record->effectiveStock())
                    ->badge()
                    ->color(fn (Product $record): string => match (true) {
                        $record->effectiveStock() <= 0 => 'danger',
                        $record->isLowStock() => 'warning',
                        default => 'success',
                    })
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderBy('stock', $direction);
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'draft' => 'Rascunho',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'draft' => 'Rascunho',
                    ]),
                Filter::make('low_stock')
                    ->label('Estoque baixo')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query
                            ->where(fn (Builder $query) => $query
                                ->whereDoesntHave('variations')
                                ->whereColumn('stock', '<=', 'low_stock_threshold'))
                            ->orWhere(fn (Builder $query) => $query
                                ->whereHas('variations')
                                ->whereRaw('(select coalesce(sum(stock), 0) from product_variations where product_variations.product_id = products.id) <= products.low_stock_threshold'));
                    })),
            ])
            ->recordActions([
                StockEntryAction::make(),
                ToggleProductStatusAction::make(),
                EditAction::make(),
            ]);
    }
}
