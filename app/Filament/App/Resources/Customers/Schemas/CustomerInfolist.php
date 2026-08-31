<?php

namespace App\Filament\App\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nome'),
                        TextEntry::make('phone')
                            ->label('Telefone')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->label('E-mail')
                            ->placeholder('-'),
                        TextEntry::make('orders_count')
                            ->label('Total de pedidos')
                            ->state(fn ($record): int => $record->orders()->count()),
                        TextEntry::make('created_at')
                            ->label('Cliente desde')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
