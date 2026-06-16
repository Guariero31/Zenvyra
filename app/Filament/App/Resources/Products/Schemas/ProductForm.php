<?php

namespace App\Filament\App\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\Money;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Categoria')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255),
                        Money::make('price')
                            ->label('Preço de Venda')
                            ->required(),
                        Money::make('cost_price')
                            ->label('Custo')
                            ->required(),
                        TextInput::make('stock')
                            ->label('Estoque')
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->disabled(fn (?Product $record): bool => (bool) $record?->hasVariations())
                            ->dehydrated(fn (?Product $record): bool => ! (bool) $record?->hasVariations())
                            ->helperText(fn (?Product $record): ?string => $record?->hasVariations()
                                ? 'Este produto possui variações. O estoque é controlado em cada variação.'
                                : null),
                        TextInput::make('low_stock_threshold')
                            ->label('Alerta de estoque baixo')
                            ->integer()
                            ->minValue(0)
                            ->default(5)
                            ->helperText('Destaca o produto quando o estoque ficar igual ou abaixo deste valor.'),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
