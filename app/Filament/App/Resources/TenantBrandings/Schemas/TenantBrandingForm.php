<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\TenantBrandings\Schemas;

use App\Filament\Shared\Schemas\IdentidadeVisualFormComponents;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TenantBrandingForm
{
    public static function configure(Schema $schema): Schema
    {
        $tenant = Filament::getTenant();
        $tenantId = $tenant instanceof Tenant ? (int) $tenant->getKey() : null;
        $tenantName = $tenant instanceof Tenant ? $tenant->te_name : null;

        return $schema
            ->components([
                View::make('filament.identidade-visual.aviso')
                    ->viewData(['painel' => 'tenant'])
                    ->columnSpanFull(),

                View::make('filament.identidade-visual.preview')
                    ->viewData([
                        'fields' => IdentidadeVisualFormComponents::previewFieldsTenant(),
                        'fallbackNome' => $tenantName ?? 'Sua loja',
                        'previewDesc' => 'Veja como nome e cores aparecem para sua equipe. Alterne o modo claro/escuro do Filament para conferir os dois temas.',
                        'navPrimary' => 'Pedidos',
                        'navSecondary' => 'Produtos',
                    ])
                    ->columnSpanFull(),

                Tabs::make('IdentidadeVisualTabs')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Geral')
                            ->icon(Heroicon::OutlinedBuildingStorefront)
                            ->schema([
                                Section::make('Marca da loja')
                                    ->description('Como sua equipe verá o painel: nome no topo e arquivos de logo.')
                                    ->schema([
                                        TextInput::make('nome_loja')
                                            ->label('Nome exibido no painel')
                                            ->helperText('Aparece ao lado do logo na barra lateral. Se vazio, usamos o nome cadastrado da loja.')
                                            ->maxLength(80)
                                            ->placeholder($tenantName ?? 'Ex: Minha Loja'),

                                        FileUpload::make('logo_path')
                                            ->label('Logotipo')
                                            ->helperText('SVG ou PNG com fundo transparente. Recomendado: 200×48 px.')
                                            ->image()
                                            ->imagePreviewHeight('3rem')
                                            ->disk('public')
                                            ->directory(fn (): string => 'branding/tenants/'.($tenantId ?? 'tmp'))
                                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp'])
                                            ->maxSize(1024),

                                        FileUpload::make('favicon_path')
                                            ->label('Favicon')
                                            ->helperText('Ícone da aba do navegador. PNG ou ICO, 32×32 px.')
                                            ->image()
                                            ->imagePreviewHeight('2rem')
                                            ->disk('public')
                                            ->directory(fn (): string => 'branding/tenants/'.($tenantId ?? 'tmp'))
                                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/webp'])
                                            ->maxSize(256),
                                    ]),
                            ]),

                        Tab::make('Modo Claro')
                            ->icon(Heroicon::OutlinedSun)
                            ->schema([
                                Section::make('Cores dos botões e badges')
                                    ->description('Primária, sucesso, alerta e erro quando o painel está no tema claro.')
                                    ->columns(2)
                                    ->schema(IdentidadeVisualFormComponents::colorFields(
                                        primary: 'cor_primaria_light',
                                        success: 'cor_sucesso_light',
                                        warning: 'cor_alerta_light',
                                        danger: 'cor_erro_light',
                                    )),

                                Section::make('Fundos (opcional)')
                                    ->description('Personalize áreas do layout. Deixe vazio para manter o visual padrão do Filament.')
                                    ->collapsed()
                                    ->columns(2)
                                    ->schema(IdentidadeVisualFormComponents::backgroundFields(
                                        page: 'bg_page_light',
                                        content: 'bg_content_light',
                                        sidebar: 'bg_sidebar_light',
                                        topbar: 'bg_topbar_light',
                                    )),
                            ]),

                        Tab::make('Modo Escuro')
                            ->icon(Heroicon::OutlinedMoon)
                            ->schema([
                                Section::make('Cores dos botões e badges')
                                    ->description('Mesmas funções do modo claro, adaptadas ao tema escuro.')
                                    ->columns(2)
                                    ->schema(IdentidadeVisualFormComponents::colorFields(
                                        primary: 'cor_primaria_dark',
                                        success: 'cor_sucesso_dark',
                                        warning: 'cor_alerta_dark',
                                        danger: 'cor_erro_dark',
                                    )),

                                Section::make('Fundos (opcional)')
                                    ->description('Personalize áreas do layout no tema escuro.')
                                    ->collapsed()
                                    ->columns(2)
                                    ->schema(IdentidadeVisualFormComponents::backgroundFields(
                                        page: 'bg_page_dark',
                                        content: 'bg_content_dark',
                                        sidebar: 'bg_sidebar_dark',
                                        topbar: 'bg_topbar_dark',
                                    )),
                            ]),
                    ]),

                View::make('filament.identidade-visual.historico')
                    ->viewData(['painel' => 'tenant', 'tenantId' => $tenantId])
                    ->columnSpanFull(),
            ]);
    }
}
