<?php

namespace App\Filament\Admin\Resources\Tenants\Schemas;

use App\Enums\SubscriptionIntent;
use App\Models\Plan;
use App\Models\Tenant;
use App\Support\TenantGestaoSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Provisionamento')
                    ->description('Escolha se a loja nasce como avaliação comercial ou como cliente com assinatura ativa.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->hiddenOn('edit')
                    ->schema([
                        Select::make('provisioning_type')
                            ->label('Tipo de loja')
                            ->options([
                                SubscriptionIntent::Evaluation->value => 'Avaliação (ambiente reduzido)',
                                SubscriptionIntent::Commercial->value => 'Cliente com assinatura ativa',
                            ])
                            ->default(SubscriptionIntent::Evaluation->value)
                            ->required()
                            ->live(),
                        TextInput::make('evaluation_days')
                            ->label('Dias de avaliação')
                            ->numeric()
                            ->type('text')
                            ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                            ->minValue(1)
                            ->maxValue(365)
                            ->default(fn (): int => (int) config('subscriptions.evaluation_days'))
                            ->required(fn (Get $get): bool => $get('provisioning_type') === SubscriptionIntent::Evaluation->value)
                            ->visible(fn (Get $get): bool => $get('provisioning_type') === SubscriptionIntent::Evaluation->value)
                            ->helperText('Padrão configurável em subscriptions.evaluation_days.'),
                        Select::make('commercial_plan_id')
                            ->label('Plano comercial')
                            ->options(fn (): array => Plan::query()
                                ->where('slug', '!=', config('subscriptions.evaluation_plan_slug'))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('provisioning_type') === SubscriptionIntent::Commercial->value)
                            ->visible(fn (Get $get): bool => $get('provisioning_type') === SubscriptionIntent::Commercial->value),
                    ]),
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
                    ]),
                Section::make('Configurações de Gestão')
                    ->description('Defina como o painel desta loja se comporta para clientes, vendedores e estoque.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('te_gestao_settings.exige_telefone_cliente')
                            ->label('Exigir telefone no cadastro de clientes')
                            ->helperText('Quando ativo, o lojista não consegue salvar cliente sem telefone.')
                            ->default(fn (): bool => TenantGestaoSettings::defaults()->exigeTelefoneCliente()),
                        Toggle::make('te_gestao_settings.exige_email_cliente')
                            ->label('Exigir e-mail no cadastro de clientes')
                            ->helperText('Quando ativo, o lojista não consegue salvar cliente sem e-mail.')
                            ->default(fn (): bool => TenantGestaoSettings::defaults()->exigeEmailCliente()),
                        Toggle::make('te_gestao_settings.modulo_vendedores_ativo')
                            ->label('Habilitar comissões por usuário')
                            ->helperText('Desative para ocultar comissões nos pedidos e no ranking. A gestão da equipe continua disponível.')
                            ->default(fn (): bool => TenantGestaoSettings::defaults()->moduloVendedoresAtivo()),
                        Toggle::make('te_gestao_settings.permitir_estoque_negativo')
                            ->label('Permitir estoque negativo')
                            ->helperText('Quando desativado, vendas e ajustes não podem deixar o saldo abaixo de zero.')
                            ->default(fn (): bool => TenantGestaoSettings::defaults()->permitirEstoqueNegativo()),
                    ]),
            ]);
    }
}
