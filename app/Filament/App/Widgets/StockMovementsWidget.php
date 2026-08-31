<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enums\StockMovementType;
use App\Models\StockMovement;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query(
                StockMovement::query()->with(['product', 'variation', 'user'])
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('product.title')
                    ->label('Produto')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('variation.name')
                    ->label('Variação')
                    ->placeholder('-'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('quantity')
                    ->label('Quantidade')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => ($state > 0 ? '+' : '').$state),
                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->placeholder('-'),
                TextColumn::make('reason')
                    ->label('Observação')
                    ->wrap()
                    ->limit(60)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(StockMovementType::class),
                Filter::make('period')
                    ->label('Período')
                    ->form([
                        DatePicker::make('from')
                            ->label('De'),
                        DatePicker::make('until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
