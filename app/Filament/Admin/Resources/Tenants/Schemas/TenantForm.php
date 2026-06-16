<?php

namespace App\Filament\Admin\Resources\Tenants\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('te_name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('te_slug')
                            ->label('Slug')
                            ->helperText('Deixe em branco para gerar automaticamente a partir do nome.')
                            ->maxLength(255),
                        TextInput::make('te_schema')
                            ->label('Schema PostgreSQL')
                            ->helperText('Deixe em branco para gerar automaticamente. Depois de criado, não altere sem migrar o schema físico.')
                            ->readOnly(fn (?Tenant $record): bool => filled($record?->te_schema))
                            ->maxLength(255),
                        KeyValue::make('te_gestao_settings')
                            ->label('Configurações de Gestão')
                            ->keyLabel('Chave')
                            ->valueLabel('Valor')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
