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

class StockAdjustmentAction
{
    public static function make(): Action
    {
        return Action::make('stockAdjustment')
            ->label('Ajustar estoque')
            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->color('warning')
            ->modalHeading('Ajustar estoque')
            ->modalDescription('Use valores positivos para adicionar unidades e negativos para remover. O movimento fica registrado no histórico.')
            ->modalSubmitActionLabel('Registrar ajuste')
            ->schema(fn (Product $record): array => [
                Select::make('product_variation_id')
                    ->label('Variação')
                    ->options(fn (): array => $record->variations()->pluck('name', 'id')->all())
                    ->required()
                    ->visible($record->hasVariations()),
                TextInput::make('quantity')
                    ->label('Quantidade (use negativo para baixa)')
                    ->integer()
                    ->type('text')
                    ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                    ->default(0)
                    ->required()
                    ->rules(['not_in:0']),
                Textarea::make('reason')
                    ->label('Observação')
                    ->rows(2)
                    ->placeholder('Ex.: Correção de inventário, avaria'),
            ])
            ->action(function (array $data, Product $record, StockManager $stock): void {
                $variation = empty($data['product_variation_id'])
                    ? null
                    : $record->variations()->find($data['product_variation_id']);

                $stock->adjustment(
                    product: $record,
                    variation: $variation,
                    signedQuantity: (int) $data['quantity'],
                    reason: $data['reason'] ?? null,
                    userId: auth()->id(),
                );

                Notification::make()
                    ->title('Ajuste de estoque registrado')
                    ->success()
                    ->send();
            });
    }
}
