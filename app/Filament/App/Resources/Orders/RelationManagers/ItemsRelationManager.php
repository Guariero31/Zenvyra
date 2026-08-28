<?php

namespace App\Filament\App\Resources\Orders\RelationManagers;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\ProductVariation;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Leandrocfe\FilamentPtbrFormFields\Money;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected function isLocked(): bool
    {
        $status = $this->getOwnerRecord()->status;

        return $status instanceof OrderStatus
            ? $status->locksItems()
            : false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Produto')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('product_variation_id')
                    ->label('Variação')
                    ->relationship('variation', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('quantity')
                    ->label('Quantidade')
                    ->integer()
                    ->type('text')
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->rule(static function (callable $get): Closure {
                        return static function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            $variationId = $get('product_variation_id');
                            $productId = $get('product_id');

                            $available = $variationId
                                ? ProductVariation::find($variationId)?->stock
                                : ($productId ? Product::find($productId)?->stock : null);

                            if ($available !== null && (int) $value > (int) $available) {
                                $fail("Estoque insuficiente. Disponível: {$available}.");
                            }
                        };
                    }),
                Money::make('price_at_sale')
                    ->label('Preço na venda')
                    ->required(),
                Money::make('cost_at_sale')
                    ->label('Custo na venda')
                    ->required()
                    ->helperText('Usado para calcular margem e BI.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.title')
            ->columns([
                TextColumn::make('product.title')
                    ->label('Produto')
                    ->searchable(),
                TextColumn::make('variation.name')
                    ->label('Variação')
                    ->placeholder('-'),
                TextColumn::make('quantity')
                    ->label('Qtd.')
                    ->sortable(),
                TextColumn::make('price_at_sale')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('cost_at_sale')
                    ->label('Custo')
                    ->money('BRL')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commission_rate')
                    ->label('Comissão')
                    ->suffix('%')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commission_amount')
                    ->label('Valor comissão')
                    ->money('BRL')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => ! $this->isLocked()),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(false)
                    ->visible(fn (): bool => ! $this->isLocked()),
                DeleteAction::make()
                    ->visible(fn (): bool => ! $this->isLocked()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => ! $this->isLocked()),
                ]),
            ]);
    }
}
