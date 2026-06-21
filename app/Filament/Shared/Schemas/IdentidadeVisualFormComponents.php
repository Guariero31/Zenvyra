<?php

declare(strict_types=1);

namespace App\Filament\Shared\Schemas;

use Filament\Forms\Components\ColorPicker;

final class IdentidadeVisualFormComponents
{
    /**
     * @return array<string, string>
     */
    public static function previewFieldsMaster(): array
    {
        return [
            'nome' => 'nome_sistema',
            'primaryLight' => 'cor_primaria',
            'successLight' => 'cor_sucesso',
            'warningLight' => 'cor_alerta',
            'dangerLight' => 'cor_erro',
            'primaryDark' => 'cor_primaria_dark',
            'bgPageLight' => 'bg_page_light',
            'bgSidebarLight' => 'bg_sidebar_light',
            'bgTopbarLight' => 'bg_topbar_light',
            'bgContentLight' => 'bg_content_light',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function previewFieldsTenant(): array
    {
        return [
            'nome' => 'nome_loja',
            'primaryLight' => 'cor_primaria_light',
            'successLight' => 'cor_sucesso_light',
            'warningLight' => 'cor_alerta_light',
            'dangerLight' => 'cor_erro_light',
            'primaryDark' => 'cor_primaria_dark',
            'bgPageLight' => 'bg_page_light',
            'bgSidebarLight' => 'bg_sidebar_light',
            'bgTopbarLight' => 'bg_topbar_light',
            'bgContentLight' => 'bg_content_light',
        ];
    }

    /**
     * @return array<int, ColorPicker>
     */
    public static function colorFields(string $primary, string $success, string $warning, string $danger): array
    {
        return [
            ColorPicker::make($primary)
                ->label('Cor primária')
                ->helperText('Botões principais, links e destaques.')
                ->required()
                ->rgba(false),

            ColorPicker::make($success)
                ->label('Cor de sucesso')
                ->helperText('Confirmações e status positivos.')
                ->required()
                ->rgba(false),

            ColorPicker::make($warning)
                ->label('Cor de alerta')
                ->helperText('Avisos e pendências.')
                ->required()
                ->rgba(false),

            ColorPicker::make($danger)
                ->label('Cor de erro')
                ->helperText('Erros, cancelamentos e ações destrutivas.')
                ->required()
                ->rgba(false),
        ];
    }

    /**
     * @return array<int, ColorPicker>
     */
    public static function backgroundFields(string $page, string $content, string $sidebar, string $topbar): array
    {
        return [
            ColorPicker::make($page)
                ->label('Fundo da página')
                ->rgba(false),

            ColorPicker::make($content)
                ->label('Fundo do conteúdo')
                ->rgba(false),

            ColorPicker::make($sidebar)
                ->label('Fundo da barra lateral')
                ->rgba(false),

            ColorPicker::make($topbar)
                ->label('Fundo do topo')
                ->rgba(false),
        ];
    }
}
