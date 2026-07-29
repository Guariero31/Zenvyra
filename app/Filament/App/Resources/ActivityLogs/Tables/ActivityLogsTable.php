<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\ActivityLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label('Módulo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'produto'    => 'info',
                        'pedido'     => 'success',
                        'cliente'    => 'warning',
                        'assinatura' => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => 'Criado',
                        'updated' => 'Atualizado',
                        'deleted' => 'Excluído',
                        default   => $state ?? '—',
                    }),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->wrap()
                    ->limit(80),

                TextColumn::make('causer.name')
                    ->label('Usuário')
                    ->placeholder('Sistema')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Módulo')
                    ->options([
                        'produto'    => 'Produto',
                        'pedido'     => 'Pedido',
                        'cliente'    => 'Cliente',
                        'assinatura' => 'Assinatura',
                        'default'    => 'Geral',
                    ]),

                SelectFilter::make('event')
                    ->label('Evento')
                    ->options([
                        'created' => 'Criado',
                        'updated' => 'Atualizado',
                        'deleted' => 'Excluído',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
