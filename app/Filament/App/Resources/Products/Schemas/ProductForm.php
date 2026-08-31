<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Products\Schemas;

use App\Filament\Forms\Components\Percent;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\Money;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do produto')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('category_id')
                            ->label('Categoria')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255)
                            ->placeholder('Ex: CAMISETA-P-PRE-001')
                            ->helperText('Código único de identificação do produto.'),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Preço')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Money::make('price')
                            ->label('Preço de venda')
                            ->required(),

                        Money::make('cost_price')
                            ->label('Custo')
                            ->helperText('Apenas para controle interno.'),

                        Percent::make('commission_rate')
                            ->label('Comissão (%)')
                            ->nullable()
                            ->placeholder('Padrão do vendedor')
                            ->helperText('Deixe vazio para usar a comissão padrão do vendedor.'),
                    ]),

                Section::make('Variações')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('has_variations')
                            ->label('Este produto tem variações (tamanho, cor etc.)?')
                            ->helperText('Ative para controlar estoque, preço e imagens por variação, na aba "Variações" abaixo. O estoque geral do produto some quando ativado.')
                            ->live()
                            ->default(false),
                    ]),

                Section::make('Estoque')
                    ->columnSpanFull()
                    ->columns(2)
                    ->hidden(fn (Get $get): bool => (bool) $get('has_variations'))
                    ->schema([
                        TextInput::make('stock')
                            ->label('Quantidade em estoque')
                            ->integer()
                            ->type('text')
                            ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                            ->minValue(0)
                            ->default(0)
                            ->dehydrated(fn (Get $get): bool => ! (bool) $get('has_variations')),

                        TextInput::make('low_stock_threshold')
                            ->label('Alerta de estoque baixo')
                            ->integer()
                            ->type('text')
                            ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                            ->minValue(0)
                            ->default(5)
                            ->helperText('Destaca o produto em amarelo quando o saldo atingir este valor.')
                            ->dehydrated(fn (Get $get): bool => ! (bool) $get('has_variations')),
                    ]),

                Section::make('Status')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status do produto')
                            ->options([
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                                'draft' => 'Rascunho',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
            ]);
    }
}
