<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\Products\Actions\StockAdjustmentAction;
use App\Filament\App\Resources\Products\Actions\StockEntryAction;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class StockProductsWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query(
                Product::query()->with(['category', 'variations'])
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Produto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->placeholder('-'),
                TextColumn::make('effective_stock')
                    ->label('Saldo')
                    ->state(fn (Product $record): int => $record->effectiveStock())
                    ->badge()
                    ->color(fn (Product $record): string => match (true) {
                        $record->effectiveStock() <= 0 => 'danger',
                        $record->isLowStock() => 'warning',
                        default => 'success',
                    })
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('stock', $direction)),
                TextColumn::make('low_stock_threshold')
                    ->label('Limiar baixo')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('low_stock')
                    ->label('Estoque baixo')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query
                            ->where(fn (Builder $q) => $q
                                ->where('has_variations', false)
                                ->whereColumn('stock', '<=', 'low_stock_threshold'))
                            ->orWhere(fn (Builder $q) => $q
                                ->where('has_variations', true)
                                ->whereHas('variations', fn (Builder $q) => $q->whereColumn('stock', '<=', 'low_stock_threshold')));
                    })),
            ])
            ->recordActions([
                StockEntryAction::make(),
                StockAdjustmentAction::make(),
            ])
            ->defaultSort('title');
    }
}
