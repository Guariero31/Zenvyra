<?php

namespace App\Filament\Admin\Resources\Subscriptions\Tables;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\SubscriptionManager;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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
                TextColumn::make('next_billing_at')
                    ->label('Próxima cobrança')
                    ->date()
                    ->sortable(),
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
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Ativar')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Ativar assinatura')
                    ->modalDescription('Confirma a ativação? A loja recebe acesso imediato e o próximo vencimento é recalculado.')
                    ->visible(fn (Subscription $record): bool => $record->status !== SubscriptionStatus::Active)
                    ->action(fn (Subscription $record) => app(SubscriptionManager::class)->activate($record))
                    ->successNotificationTitle('Assinatura ativada.'),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
