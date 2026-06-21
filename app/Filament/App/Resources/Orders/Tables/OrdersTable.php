<?php

namespace App\Filament\App\Resources\Orders\Tables;

use App\Filament\App\Resources\Orders\Actions\CancelOrderAction;
use App\Filament\App\Resources\Orders\Actions\RefundOrderAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->placeholder('Sem cliente'),
                TextColumn::make('operator.name')
                    ->label('Vendedor')
                    ->searchable()
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
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commission_amount')
                    ->label('Comissão')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('origin')
                    ->label('Origem')
                    ->options([
                        'BALCAO' => 'Balcão',
                        'CATALOGO_WHATSAPP' => 'Catálogo WhatsApp',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Vendedor')
                    ->relationship('operator', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(false),
                RefundOrderAction::make(),
                CancelOrderAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
