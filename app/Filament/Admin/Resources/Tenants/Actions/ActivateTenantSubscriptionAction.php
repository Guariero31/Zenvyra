<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tenants\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * Reactivates the store's latest subscription straight from the tenants list,
 * so a canceled/expired store can be released without leaving this screen.
 */
class ActivateTenantSubscriptionAction
{
    public static function make(): Action
    {
        return Action::make('activateSubscription')
            ->label('Ativar assinatura')
            ->icon(Heroicon::OutlinedCreditCard)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Ativar assinatura da loja')
            ->modalDescription(fn (Tenant $record): string => self::latestSubscription($record)?->isEvaluation()
                ? 'Esta loja está em avaliação. Selecione o plano comercial para convertê-la em cliente.'
                : 'A loja recebe acesso imediato, fica ativa e o próximo vencimento é recalculado.')
            ->schema(fn (Tenant $record): array => self::latestSubscription($record)?->isEvaluation()
                ? [
                    Select::make('plan_id')
                        ->label('Plano comercial')
                        ->options(fn (): array => Plan::query()
                            ->where('slug', '!=', config('subscriptions.evaluation_plan_slug'))
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->required(),
                ]
                : [])
            ->visible(function (Tenant $record): bool {
                $subscription = self::latestSubscription($record);

                return $subscription !== null
                    && $subscription->status !== SubscriptionStatus::Active;
            })
            ->action(function (Tenant $record, array $data): void {
                $subscription = self::latestSubscription($record);

                if ($subscription === null) {
                    return;
                }

                $manager = app(SubscriptionManager::class);

                if ($subscription->isEvaluation()) {
                    $manager->convertEvaluationToCommercial($subscription, Plan::findOrFail($data['plan_id']));
                } else {
                    $manager->activate($subscription);
                    $record->update(['te_ativo' => true]);
                }

                Notification::make()
                    ->title('Assinatura ativada')
                    ->success()
                    ->send();
            });
    }

    private static function latestSubscription(Tenant $tenant): ?Subscription
    {
        return $tenant->subscriptions()->latest('id')->first();
    }
}
