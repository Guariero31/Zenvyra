<?php

namespace App\Filament\App\Resources\TeamMembers\Schemas;

use App\Enums\TenantPortalRole;
use App\Filament\Forms\Components\Percent;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        $tenant = Filament::getTenant();
        $commissionsEnabled = $tenant instanceof Tenant
            && $tenant->gestaoSettings()->moduloVendedoresAtivo();

        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->required(function (string $operation, Get $get): bool {
                                if ($operation !== 'create') {
                                    return false;
                                }

                                $email = mb_strtolower(trim((string) $get('email')));

                                if ($email === '') {
                                    return true;
                                }

                                return ! User::query()->where('email', $email)->exists();
                            })
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Preencha apenas se quiser alterar a senha de acesso.'
                                : 'Obrigatória para usuários novos. Se o e-mail já existir na plataforma, a senha é opcional.')
                            ->maxLength(255),
                        Select::make('tu_papel')
                            ->label('Papel')
                            ->options(TenantPortalRole::class)
                            ->required(),
                        Select::make('tu_rotulo_setor')
                            ->label('Setor')
                            ->options([
                                'Diretoria' => 'Diretoria',
                                'Gerência' => 'Gerência',
                                'Vendas' => 'Vendas',
                                'Operação' => 'Operação',
                                'RH' => 'RH',
                            ])
                            ->searchable(),
                        Percent::make('tu_commission_rate')
                            ->label('Comissão padrão (%)')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->required($commissionsEnabled)
                            ->visible($commissionsEnabled)
                            ->helperText('Usada quando o produto vendido não tiver uma comissão específica.'),
                    ]),
            ]);
    }
}
