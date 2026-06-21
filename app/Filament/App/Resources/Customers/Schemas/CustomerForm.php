<?php

namespace App\Filament\App\Resources\Customers\Schemas;

use App\Support\TenantGestaoSettings;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;

class CustomerForm
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
                        PhoneNumber::make('phone')
                            ->label('Telefone')
                            ->required(fn (): bool => TenantGestaoSettings::forCurrentTenant()->exigeTelefoneCliente()),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required(fn (): bool => TenantGestaoSettings::forCurrentTenant()->exigeEmailCliente())
                            ->maxLength(255),
                    ]),
            ]);
    }
}
