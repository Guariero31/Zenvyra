<?php

declare(strict_types=1);

namespace App\Filament\Admin\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ToggleAdminStatusAction
{
    public static function make(): Action
    {
        return Action::make('toggleAdmin')
            ->label(fn (User $record): string => $record->is_platform_admin
                ? 'Remover acesso master'
                : 'Tornar admin master')
            ->icon(fn (User $record): Heroicon => $record->is_platform_admin
                ? Heroicon::OutlinedShieldExclamation
                : Heroicon::OutlinedShieldCheck)
            ->color(fn (User $record): string => $record->is_platform_admin ? 'gray' : 'success')
            ->requiresConfirmation()
            // Impede que o admin logado remova o próprio acesso e se tranque para fora do painel.
            ->visible(fn (User $record): bool => $record->id !== auth()->id())
            ->action(function (User $record): void {
                $record->update(['is_platform_admin' => ! $record->is_platform_admin]);

                Notification::make()
                    ->title($record->is_platform_admin ? 'Acesso master concedido' : 'Acesso master removido')
                    ->success()
                    ->send();
            });
    }
}
