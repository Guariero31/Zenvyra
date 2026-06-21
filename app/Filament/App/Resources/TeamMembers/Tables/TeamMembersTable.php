<?php

namespace App\Filament\App\Resources\TeamMembers\Tables;

use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('tenant_role')
                    ->label('Papel')
                    ->state(function (User $record): ?string {
                        $tenant = Filament::getTenant();

                        if (! $tenant instanceof Tenant) {
                            return null;
                        }

                        return $record->roleInTenant($tenant)?->getLabel();
                    })
                    ->badge(),
                TextColumn::make('tu_commission_rate')
                    ->label('Comissão padrão')
                    ->state(function (User $record): string {
                        $tenant = Filament::getTenant();

                        if (! $tenant instanceof Tenant) {
                            return '0';
                        }

                        return $record->commissionRateInTenant($tenant);
                    })
                    ->suffix('%')
                    ->sortable(false)
                    ->visible(fn (): bool => Filament::getTenant()?->gestaoSettings()->moduloVendedoresAtivo() ?? false),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(false),
            ]);
    }
}
