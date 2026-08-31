<?php

namespace App\Filament\App\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $title = 'Movimentações';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('quantity')
                    ->label('Qtd.')
                    ->sortable(),
                TextColumn::make('variation.name')
                    ->label('Variação')
                    ->placeholder('-'),
                TextColumn::make('order_id')
                    ->label('Pedido')
                    ->placeholder('-'),
                TextColumn::make('reason')
                    ->label('Observação')
                    ->wrap()
                    ->limit(60)
                    ->placeholder('-'),
                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
