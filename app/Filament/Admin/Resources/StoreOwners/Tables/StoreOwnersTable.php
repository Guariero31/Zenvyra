<?php

namespace App\Filament\Admin\Resources\StoreOwners\Tables;

use App\Filament\Admin\Actions\ToggleAdminStatusAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StoreOwnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('tenants.te_name')
                    ->label('Lojas')
                    ->badge()
                    ->placeholder('Sem loja vinculada'),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ToggleAdminStatusAction::make(),
                EditAction::make()
                    ->label(false),
            ]);
    }
}
