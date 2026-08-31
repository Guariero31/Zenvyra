<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Products\Actions;

use App\Models\Product;
use App\Services\StockManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class StockEntryAction
{
    public static function make(): Action
    {
        return Action::make('stockEntry')
            ->label('Entrada de estoque')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('success')
            ->modalHeading('Registrar entrada de estoque')
            ->modalDescription('Some unidades ao estoque (ex.: compra de fornecedor). O movimento fica registrado no histórico.')
            ->modalSubmitActionLabel('Registrar entrada')
            ->schema(fn (Product $record): array => [
                Select::make('product_variation_id')
                    ->label('Variação')
                    ->options(fn (): array => $record->variations()->pluck('name', 'id')->all())
                    ->required()
                    ->visible($record->hasVariations()),
                TextInput::make('quantity')
                    ->label('Quantidade')
                    ->integer()
                    ->type('text')
                    ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                    ->minValue(1)
                    ->default(1)
                    ->required(),
                Textarea::make('reason')
                    ->label('Observação')
                    ->rows(2)
                    ->placeholder('Ex.: Compra de fornecedor, nota fiscal 123'),
            ])
            ->action(function (array $data, Product $record, StockManager $stock): void {
                $variation = empty($data['product_variation_id'])
                    ? null
                    : $record->variations()->find($data['product_variation_id']);

                $stock->entry(
                    product: $record,
                    variation: $variation,
                    quantity: (int) $data['quantity'],
                    reason: $data['reason'] ?? null,
                    userId: auth()->id(),
                );

                Notification::make()
                    ->title('Entrada de estoque registrada')
                    ->success()
                    ->send();
            });
    }
}
