<?php

namespace App\Filament\Admin\Resources\Tenants\Tables;

use App\Filament\Admin\Resources\Tenants\Actions\PurgeEvaluationDataAction;
use App\Filament\Admin\Resources\Tenants\Actions\ToggleTenantStatusAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('te_name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('te_slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('plan_name')
                    ->label('Plano')
                    ->state(fn ($record) => $record->subscriptions()->latest('id')->first()?->plan?->name)
                    ->placeholder('-'),
                TextColumn::make('subscription_intent')
                    ->label('Intenção')
                    ->badge()
                    ->state(fn ($record) => $record->subscriptions()->latest('id')->first()?->intent)
                    ->placeholder('-'),
                TextColumn::make('subscription_status')
                    ->label('Assinatura')
                    ->badge()
                    ->state(fn ($record) => $record->subscriptions()->latest('id')->first()?->status)
                    ->placeholder('-'),
                TextColumn::make('te_data_retention_ends_at')
                    ->label('Retenção até')
                    ->date()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('te_schema')
                    ->label('Schema')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('te_ativo')
                    ->label('Ativa')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('te_ativo')
                    ->label('Ativa'),
            ])
            ->recordActions([
                ToggleTenantStatusAction::make(),
                PurgeEvaluationDataAction::make(),
                EditAction::make()
                    ->label(false),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
