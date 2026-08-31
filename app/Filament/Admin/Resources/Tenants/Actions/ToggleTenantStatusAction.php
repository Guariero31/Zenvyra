<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tenants\Actions;

use App\Models\Tenant;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ToggleTenantStatusAction
{
    public static function make(): Action
    {
        return Action::make('toggleStatus')
            ->label(fn (Tenant $record): string => $record->te_ativo ? 'Desativar' : 'Ativar')
            ->icon(fn (Tenant $record): Heroicon => $record->te_ativo
                ? Heroicon::OutlinedXCircle
                : Heroicon::OutlinedCheckCircle)
            ->color(fn (Tenant $record): string => $record->te_ativo ? 'gray' : 'success')
            ->requiresConfirmation()
            ->action(function (Tenant $record): void {
                $record->update(['te_ativo' => ! $record->te_ativo]);

                Notification::make()
                    ->title($record->te_ativo ? 'Loja ativada' : 'Loja desativada')
                    ->success()
                    ->send();
            });
    }
}
