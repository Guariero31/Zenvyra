<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Orders\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderRefundManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class RefundOrderAction
{
    public static function make(): Action
    {
        return Action::make('refund')
            ->label('Estornar')
            ->icon(Heroicon::OutlinedReceiptRefund)
            ->color('danger')
            ->authorize('refund')
            ->visible(fn (Order $record): bool => $record->status->isRefundable())
            ->schema(fn (Order $record): array => [
                ToggleButtons::make('type')
                    ->label('Tipo de estorno')
                    ->options([
                        'full' => 'Total',
                        'partial' => 'Parcial',
                    ])
                    ->default('full')
                    ->inline()
                    ->required()
                    ->live(),
                Repeater::make('items')
                    ->label('Itens a estornar')
                    ->visible(fn (callable $get): bool => $get('type') === 'partial')
                    ->default(fn (): array => self::refundableRows($record))
                    ->table([
                        TableColumn::make('Item'),
                        TableColumn::make('Disponível')->alignCenter(),
                        TableColumn::make('Quantidade')->alignCenter(),
                    ])
                    ->schema([
                        TextInput::make('product_label')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('refundable')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('quantity')
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->maxValue(fn (callable $get): int => max(0, (int) $get('refundable'))),
                        Hidden::make('order_item_id'),
                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->columnSpanFull(),
                Textarea::make('reason')
                    ->label('Motivo')
                    ->required()
                    ->rows(3),
                Toggle::make('restocked')
                    ->label('Devolver ao estoque')
                    ->default(true)
                    ->helperText('Desmarque se o produto não puder retornar ao estoque (ex.: danificado).'),
            ])
            ->action(function (array $data, Order $record, OrderRefundManager $refunds): void {
                $items = match ($data['type']) {
                    'full' => $refunds->refundableItemsMap($record),
                    'partial' => collect($data['items'] ?? [])
                        ->filter(fn (array $line): bool => (int) ($line['quantity'] ?? 0) > 0)
                        ->mapWithKeys(fn (array $line): array => [(int) $line['order_item_id'] => (int) $line['quantity']])
                        ->all(),
                    default => [],
                };

                $refunds->refund(
                    order: $record,
                    items: $items,
                    reason: $data['reason'],
                    restock: (bool) ($data['restocked'] ?? false),
                    userId: auth()->id(),
                );

                Notification::make()
                    ->title('Estorno registrado com sucesso')
                    ->success()
                    ->send();
            })
            ->modalHeading('Estornar pedido')
            ->modalSubmitActionLabel('Confirmar estorno');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function refundableRows(Order $order): array
    {
        return $order->items()
            ->with('product')
            ->get()
            ->filter(fn (OrderItem $item): bool => $item->refundableQuantity() > 0)
            ->map(fn (OrderItem $item): array => [
                'order_item_id' => $item->id,
                'product_label' => $item->product->title,
                'refundable' => $item->refundableQuantity(),
                'quantity' => 0,
            ])
            ->values()
            ->all();
    }
}
