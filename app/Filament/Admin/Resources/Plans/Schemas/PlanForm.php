<?php

namespace App\Filament\Admin\Resources\Plans\Schemas;

use App\Enums\BillingPeriod;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\Money;

class PlanForm
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
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->columnSpanFull(),
                        Money::make('price')
                            ->label('Preço')
                            ->required(),
                        Select::make('billing_period')
                            ->label('Período')
                            ->options(BillingPeriod::options())
                            ->required()
                            ->default(BillingPeriod::Monthly->value),
                        TextInput::make('trial_days')
                            ->label('Dias de teste grátis')
                            ->helperText('Use 0 para não oferecer período de teste.')
                            ->numeric()
                            ->type('text')
                            ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('max_products')
                            ->label('Limite de produtos')
                            ->helperText('Deixe vazio para ilimitado.')
                            ->numeric()
                            ->type('text')
                            ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                            ->minValue(0),
                        TextInput::make('max_users')
                            ->label('Limite de usuários')
                            ->helperText('Deixe vazio para ilimitado.')
                            ->numeric()
                            ->type('text')
                            ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                            ->minValue(0),
                        KeyValue::make('features')
                            ->label('Recursos')
                            ->keyLabel('Recurso')
                            ->valueLabel('Valor')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
