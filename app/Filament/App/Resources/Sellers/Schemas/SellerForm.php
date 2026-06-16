<?php

namespace App\Filament\App\Resources\Sellers\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SellerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        Select::make('user_id')
                            ->label('Conta vinculada')
                            ->options(fn (): array => Filament::getTenant()?->users()
                                ->orderBy('name')
                                ->pluck('name', 'users.id')
                                ->all() ?? [])
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Opcional. Use para vendedores que terão login; deixe vazio para vendedor de balcão.'),
                        TextInput::make('commission_rate')
                            ->label('Comissão padrão (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->required()
                            ->helperText('Usada quando o produto vendido não tiver uma comissão específica.'),
                        Select::make('active')
                            ->label('Status')
                            ->options([
                                true => 'Ativo',
                                false => 'Inativo',
                            ])
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
