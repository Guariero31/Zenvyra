<?php

namespace App\Filament\App\Resources\Customers\RelationManagers;

use App\Filament\App\Resources\Orders\OrderResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Histórico de compras';

    protected static ?string $modelLabel = 'compra';

    protected static ?string $pluralModelLabel = 'compras';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('operator.name')
                    ->label('Vendedor')
                    ->placeholder('Sem vendedor'),
                TextColumn::make('origin')
                    ->label('Origem')
                    ->badge(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'Pix',
                        'cartao_credito' => 'Crédito',
                        'cartao_debito' => 'Débito',
                        default => $state ?? '-',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('refunded_amount')
                    ->label('Estornado')
                    ->money('BRL')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Visualizar')
                    ->url(fn ($record): string => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('Nenhuma compra registrada')
            ->emptyStateDescription('Os pedidos vinculados a este cliente aparecerão aqui.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
