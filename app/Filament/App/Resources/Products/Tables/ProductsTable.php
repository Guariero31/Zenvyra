<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Products\Tables;

use App\Filament\App\Resources\Products\Actions\StockEntryAction;
use App\Filament\App\Resources\Products\Actions\ToggleProductStatusAction;
use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primaryImage.image_path')
                    ->label('')
                    ->disk('public')
                    ->width(40)
                    ->height(40)
                    ->square()
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->defaultImageUrl(fn (): string => asset('images/placeholder-product.svg')),

                TextColumn::make('title')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->description(fn (Product $record): string => $record->sku ?? '—'),

                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->placeholder('—'),

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
                    ->placeholder('Padrão')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('stock_display')
                    ->label('Estoque')
                    ->state(fn (Product $record): int => $record->effectiveStock())
                    ->badge()
                    ->color(fn (Product $record): string => match (true) {
                        $record->effectiveStock() <= 0 => 'danger',
                        $record->isLowStock() => 'warning',
                        default => 'success',
                    })
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('stock', $direction)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'draft' => 'Rascunho',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'draft' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date('d/m/Y')
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
                            ->where(fn (Builder $q) => $q
                                ->whereDoesntHave('variations')
                                ->whereColumn('stock', '<=', 'low_stock_threshold')
                                ->where('stock', '>', 0))
                            ->orWhere(fn (Builder $q) => $q
                                ->whereHas('variations')
                                ->whereRaw('(select coalesce(sum(stock), 0) from product_variations where product_variations.product_id = products.id) <= products.low_stock_threshold')
                                ->whereRaw('(select coalesce(sum(stock), 0) from product_variations where product_variations.product_id = products.id) > 0'));
                    })),

                Filter::make('out_of_stock')
                    ->label('Sem estoque')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query
                            ->where(fn (Builder $q) => $q
                                ->whereDoesntHave('variations')
                                ->where('stock', '<=', 0))
                            ->orWhere(fn (Builder $q) => $q
                                ->whereHas('variations')
                                ->whereRaw('(select coalesce(sum(stock), 0) from product_variations where product_variations.product_id = products.id) <= 0'));
                    })),
            ])
            ->recordActions([
                StockEntryAction::make(),
                ToggleProductStatusAction::make(),
                EditAction::make()
                    ->hiddenLabel(),
                DeleteAction::make()
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Ativar selecionados')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'active']))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label('Pausar selecionados')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'inactive']))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('title');
    }
}
