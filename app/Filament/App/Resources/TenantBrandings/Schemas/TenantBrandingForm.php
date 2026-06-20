<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\TenantBrandings\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class TenantBrandingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Geral')
                            ->schema([
                                TextInput::make('nome_loja')
                                    ->label('Nome da loja no painel')
                                    ->maxLength(80)
                                    ->placeholder('Ex: Minha Loja'),

                                FileUpload::make('logo_path')
                                    ->label('Logotipo')
                                    ->helperText('SVG ou PNG com fundo transparente. Recomendado: 200×48 px.')
                                    ->image()
                                    ->disk('public')
                                    ->directory(fn (): string => 'branding/tenants/'.(Filament::getTenant()?->id ?? 'tmp'))
                                    ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp'])
                                    ->maxSize(512),

                                FileUpload::make('favicon_path')
                                    ->label('Favicon')
                                    ->helperText('PNG ou ICO. Recomendado: 32×32 px.')
                                    ->image()
                                    ->disk('public')
                                    ->directory(fn (): string => 'branding/tenants/'.(Filament::getTenant()?->id ?? 'tmp'))
                                    ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/webp'])
                                    ->maxSize(256),
                            ]),

                        Tab::make('Modo Claro')
                            ->schema([
                                Section::make('Cores')
                                    ->description('Cores dos elementos interativos no modo claro.')
                                    ->columns(2)
                                    ->schema([
                                        ColorPicker::make('cor_primaria_light')
                                            ->label('Cor primária')
                                            ->required()
                                            ->rgba(false),

                                        ColorPicker::make('cor_sucesso_light')
                                            ->label('Cor de sucesso')
                                            ->required()
                                            ->rgba(false),

                                        ColorPicker::make('cor_alerta_light')
                                            ->label('Cor de alerta')
                                            ->required()
                                            ->rgba(false),

                                        ColorPicker::make('cor_erro_light')
                                            ->label('Cor de erro')
                                            ->required()
                                            ->rgba(false),
                                    ]),

                                Section::make('Fundos (modo claro)')
                                    ->description('Deixe em branco para usar o padrão Filament.')
                                    ->columns(4)
                                    ->schema([
                                        ColorPicker::make('bg_page_light')
                                            ->label('Fundo da página')
                                            ->rgba(false),

                                        ColorPicker::make('bg_content_light')
                                            ->label('Fundo do conteúdo')
                                            ->rgba(false),

                                        ColorPicker::make('bg_sidebar_light')
                                            ->label('Fundo da sidebar')
                                            ->rgba(false),

                                        ColorPicker::make('bg_topbar_light')
                                            ->label('Fundo da topbar')
                                            ->rgba(false),
                                    ]),
                            ]),

                        Tab::make('Modo Escuro')
                            ->schema([
                                Section::make('Cores')
                                    ->description('Cores dos elementos interativos no modo escuro.')
                                    ->columns(2)
                                    ->schema([
                                        ColorPicker::make('cor_primaria_dark')
                                            ->label('Cor primária')
                                            ->required()
                                            ->rgba(false),

                                        ColorPicker::make('cor_sucesso_dark')
                                            ->label('Cor de sucesso')
                                            ->required()
                                            ->rgba(false),

                                        ColorPicker::make('cor_alerta_dark')
                                            ->label('Cor de alerta')
                                            ->required()
                                            ->rgba(false),

                                        ColorPicker::make('cor_erro_dark')
                                            ->label('Cor de erro')
                                            ->required()
                                            ->rgba(false),
                                    ]),

                                Section::make('Fundos (modo escuro)')
                                    ->description('Deixe em branco para usar o padrão Filament.')
                                    ->columns(4)
                                    ->schema([
                                        ColorPicker::make('bg_page_dark')
                                            ->label('Fundo da página')
                                            ->rgba(false),

                                        ColorPicker::make('bg_content_dark')
                                            ->label('Fundo do conteúdo')
                                            ->rgba(false),

                                        ColorPicker::make('bg_sidebar_dark')
                                            ->label('Fundo da sidebar')
                                            ->rgba(false),

                                        ColorPicker::make('bg_topbar_dark')
                                            ->label('Fundo da topbar')
                                            ->rgba(false),
                                    ]),
                            ]),
                    ]),

                View::make('filament.identidade-visual.painel-inferior')
                    ->viewData(['painel' => 'tenant', 'tenantId' => Filament::getTenant()?->getKey()])
                    ->columnSpanFull(),
            ]);
    }
}
