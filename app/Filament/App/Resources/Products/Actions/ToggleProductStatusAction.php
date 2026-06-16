<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Products\Actions;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ToggleProductStatusAction
{
    public static function make(): Action
    {
        return Action::make('toggleStatus')
            ->label(fn (Product $record): string => $record->status === 'active' ? 'Desativar' : 'Ativar')
            ->icon(fn (Product $record): Heroicon => $record->status === 'active'
                ? Heroicon::OutlinedXCircle
                : Heroicon::OutlinedCheckCircle)
            ->color(fn (Product $record): string => $record->status === 'active' ? 'gray' : 'success')
            ->requiresConfirmation()
            ->action(function (Product $record): void {
                $record->update([
                    'status' => $record->status === 'active' ? 'inactive' : 'active',
                ]);

                Notification::make()
                    ->title($record->status === 'active' ? 'Produto ativado' : 'Produto desativado')
                    ->success()
                    ->send();
            });
    }
}
