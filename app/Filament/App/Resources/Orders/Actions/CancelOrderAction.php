<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Orders\Actions;

use App\Models\Order;
use App\Services\OrderLifecycleManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CancelOrderAction
{
    public static function make(): Action
    {
        return Action::make('cancel')
            ->label('Cancelar pedido')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('gray')
            ->authorize('cancel')
            ->visible(fn (Order $record): bool => $record->status->isCancellable())
            ->requiresConfirmation()
            ->modalHeading('Cancelar pedido')
            ->modalDescription('O pedido será marcado como cancelado. Esta ação só é permitida enquanto o pedido está pendente.')
            ->modalSubmitActionLabel('Confirmar cancelamento')
            ->action(function (Order $record, OrderLifecycleManager $orders): void {
                $orders->cancel($record);

                Notification::make()
                    ->title('Pedido cancelado')
                    ->success()
                    ->send();
            });
    }
}
