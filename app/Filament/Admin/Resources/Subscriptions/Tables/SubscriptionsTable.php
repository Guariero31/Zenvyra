<?php

namespace App\Filament\Admin\Resources\Subscriptions\Tables;

use App\Enums\SubscriptionIntent;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionManager;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.te_name')
                    ->label('Loja')
                    ->searchable(),
                TextColumn::make('plan.name')
                    ->label('Plano')
                    ->searchable(),
                TextColumn::make('intent')
                    ->label('Intenção')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                IconColumn::make('access')
                    ->label('Acesso liberado')
                    ->state(fn (Subscription $record): bool => $record->grantsAccess())
                    ->boolean(),
                TextColumn::make('trial_ends_at')
                    ->label('Fim do teste')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('grace_ends_at')
                    ->label('Fim da tolerância')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('next_billing_display')
                    ->label('Próxima cobrança')
                    ->state(fn (Subscription $record): ?string => $record->resolveNextBillingAt()?->format('d/m/Y'))
                    ->placeholder('—')
                    ->sortable(
                        true,
                        fn ($query, string $direction) => $query->orderByRaw(
                            'COALESCE(next_billing_at, trial_ends_at, ends_at, starts_at) '.$direction
                        ),
                    ),
                TextColumn::make('ends_at')
                    ->label('Fim do ciclo')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(SubscriptionStatus::options()),
                SelectFilter::make('intent')
                    ->label('Intenção')
                    ->options(SubscriptionIntent::options()),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label(fn (Subscription $record): string => $record->isEvaluation()
                        ? 'Converter em cliente'
                        : 'Ativar')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Subscription $record): string => $record->isEvaluation()
                        ? 'Converter avaliação em cliente'
                        : 'Ativar assinatura')
                    ->modalDescription(fn (Subscription $record): string => $record->isEvaluation()
                        ? 'Selecione o plano comercial. A loja passa a ter assinatura ativa e mantém os dados cadastrados.'
                        : 'Confirma a ativação? A loja recebe acesso imediato e o próximo vencimento é recalculado.')
                    ->schema(fn (Subscription $record): array => $record->isEvaluation()
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
                    ->visible(fn (Subscription $record): bool => $record->status !== SubscriptionStatus::Active)
                    ->action(function (Subscription $record, array $data): void {
                        $manager = app(SubscriptionManager::class);

                        if ($record->isEvaluation()) {
                            $plan = Plan::findOrFail($data['plan_id']);
                            $manager->convertEvaluationToCommercial($record, $plan);

                            return;
                        }

                        $manager->activate($record);
                    })
                    ->successNotificationTitle('Operação concluída com sucesso.'),
                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar assinatura')
                    ->modalDescription('A loja perde o acesso ao painel. Esta ação pode ser revertida ativando a assinatura novamente.')
                    ->visible(fn (Subscription $record): bool => $record->status !== SubscriptionStatus::Canceled)
                    ->action(fn (Subscription $record) => app(SubscriptionManager::class)->cancel($record))
                    ->successNotificationTitle('Assinatura cancelada.'),
                EditAction::make()
                    ->label(false),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
