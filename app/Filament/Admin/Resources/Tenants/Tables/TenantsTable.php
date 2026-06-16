<?php

namespace App\Filament\Admin\Resources\Tenants\Tables;

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
                TextColumn::make('te_schema')
                    ->label('Schema')
                    ->searchable(),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
