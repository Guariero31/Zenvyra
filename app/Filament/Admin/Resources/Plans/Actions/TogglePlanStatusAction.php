<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Plans\Actions;

use App\Models\Plan;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class TogglePlanStatusAction
{
    public static function make(): Action
    {
        return Action::make('toggleStatus')
            ->label(fn (Plan $record): string => $record->is_active ? 'Desativar' : 'Ativar')
            ->icon(fn (Plan $record): Heroicon => $record->is_active
                ? Heroicon::OutlinedXCircle
                : Heroicon::OutlinedCheckCircle)
            ->color(fn (Plan $record): string => $record->is_active ? 'gray' : 'success')
            ->requiresConfirmation()
            ->action(function (Plan $record): void {
                $record->update(['is_active' => ! $record->is_active]);

                Notification::make()
                    ->title($record->is_active ? 'Plano ativado' : 'Plano desativado')
                    ->success()
                    ->send();
            });
    }
}
