<?php

namespace App\Filament\App\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('#'),
                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('customer.name')
                            ->label('Cliente')
                            ->placeholder('Sem cliente'),
                        TextEntry::make('operator.name')
                            ->label('Vendedor')
                            ->placeholder('Sem vendedor'),
                        TextEntry::make('origin')
                            ->label('Origem')
                            ->badge(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('payment_method')
                            ->label('Pagamento')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'dinheiro' => 'Dinheiro',
                                'pix' => 'Pix',
                                'cartao_credito' => 'Crédito',
                                'cartao_debito' => 'Débito',
                                default => $state ?? '-',
                            }),
                        TextEntry::make('total_amount')
                            ->label('Total')
                            ->money('BRL'),
                        TextEntry::make('commission_amount')
                            ->label('Comissão')
                            ->money('BRL'),
                        TextEntry::make('refunded_amount')
                            ->label('Estornado')
                            ->money('BRL')
                            ->placeholder('-'),
                        TextEntry::make('refunded_at')
                            ->label('Estornado em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
