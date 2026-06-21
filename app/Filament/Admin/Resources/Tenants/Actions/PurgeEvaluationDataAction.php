<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tenants\Actions;

use App\Models\Tenant;
use App\Services\TenantEvaluationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class PurgeEvaluationDataAction
{
    public static function make(): Action
    {
        return Action::make('purgeEvaluationData')
            ->label('Apagar dados')
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Apagar dados de avaliação')
            ->modalDescription('Remove o schema físico da loja e encerra a retenção de dados. Esta ação não pode ser desfeita.')
            ->visible(fn (Tenant $record): bool => $record->hasRetainedEvaluationData())
            ->action(function (Tenant $record): void {
                app(TenantEvaluationService::class)->purge($record);

                Notification::make()
                    ->title('Dados de avaliação apagados')
                    ->success()
                    ->send();
            });
    }
}
