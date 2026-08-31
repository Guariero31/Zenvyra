<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\IdentidadeVisuals\Schemas;

use App\Filament\Shared\Schemas\IdentidadeVisualFormComponents;
use App\Models\IdentidadeVisual;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class IdentidadeVisualForm
{
    public static function configure(Schema $schema): Schema
    {
        $defaultNome = IdentidadeVisual::defaults()['nome_sistema'];

        return $schema
            ->components([
                View::make('filament.identidade-visual.aviso')
                    ->viewData(['painel' => 'master'])
                    ->columnSpanFull(),

                View::make('filament.identidade-visual.preview')
                    ->viewData([
                        'fields' => IdentidadeVisualFormComponents::previewFieldsMaster(),
                        'fallbackNome' => $defaultNome,
                        'previewDesc' => 'Veja como nome e cores aparecem no painel master. Alterne o modo claro/escuro do Filament para conferir os dois temas.',
                        'navPrimary' => 'Tenants',
                        'navSecondary' => 'Planos',
                    ])
                    ->columnSpanFull(),

                Tabs::make('IdentidadeVisualTabs')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Geral')
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema([
                                Section::make('Marca do painel master')
                                    ->description('Como administradores da plataforma verão o painel: nome, logo e favicon.')
                                    ->schema([
                                        TextInput::make('nome_sistema')
                                            ->label('Nome exibido no painel')
                                            ->helperText('Aparece ao lado do logo na barra lateral do admin.')
                                            ->required()
                                            ->maxLength(120)
                                            ->placeholder('Ex: Zenvyra · Master'),

                                        FileUpload::make('logo_path')
                                            ->label('Logotipo')
                                            ->helperText('SVG ou PNG com fundo transparente. Recomendado: 200×48 px.')
                                            ->image()
                                            ->imagePreviewHeight('3rem')
                                            ->disk('public')
                                            ->directory('branding/master')
                                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp'])
                                            ->maxSize(1024),

                                        FileUpload::make('favicon_path')
                                            ->label('Favicon')
                                            ->helperText('Ícone da aba do navegador. PNG ou ICO, 32×32 px.')
                                            ->image()
                                            ->imagePreviewHeight('2rem')
                                            ->disk('public')
                                            ->directory('branding/master')
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
                                        primary: 'cor_primaria',
                                        success: 'cor_sucesso',
                                        warning: 'cor_alerta',
                                        danger: 'cor_erro',
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
                    ->viewData(['painel' => 'master', 'tenantId' => null])
                    ->columnSpanFull(),
            ]);
    }
}
