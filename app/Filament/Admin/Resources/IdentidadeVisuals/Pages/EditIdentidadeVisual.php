<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\IdentidadeVisuals\Pages;

use App\Filament\Admin\Resources\IdentidadeVisuals\IdentidadeVisualResource;
use App\Models\IdentidadeVisual;
use App\Models\IdentidadeVisualHistorico;
use App\Services\BrandingService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditIdentidadeVisual extends EditRecord
{
    protected static string $resource = IdentidadeVisualResource::class;

    protected static ?string $title = 'Identidade Visual — Painel Master';

    /** @var array<string, mixed> */
    private array $estadoAnterior = [];

    public function mount(int|string|null $record = null): void
    {
        $registro = IdentidadeVisual::obterOuCriar();

        parent::mount($registro->getKey());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('restaurar_padroes')
                ->label('Restaurar padrões')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Restaurar identidade visual padrão?')
                ->modalDescription('Todas as cores serão redefinidas para os valores padrão do sistema.')
                ->modalSubmitActionLabel('Sim, restaurar')
                ->action(function (): void {
                    $defaults = IdentidadeVisual::defaults();
                    unset($defaults['logo_path'], $defaults['favicon_path'], $defaults['nome_sistema']);

                    $atual = $this->form->getState();

                    $this->form->fill(array_merge($atual, $defaults));

                    Notification::make()
                        ->title('Cores restauradas')
                        ->body('Salve para aplicar as cores padrão.')
                        ->info()
                        ->send();
                }),
        ];
    }

    protected function beforeSave(): void
    {
        $this->estadoAnterior = $this->getRecord()->only([
            'nome_sistema',
            'cor_primaria', 'cor_sucesso', 'cor_alerta', 'cor_erro',
            'cor_primaria_dark', 'cor_sucesso_dark', 'cor_alerta_dark', 'cor_erro_dark',
            'bg_page_light', 'bg_content_light', 'bg_sidebar_light', 'bg_topbar_light',
            'bg_page_dark', 'bg_content_dark', 'bg_sidebar_dark', 'bg_topbar_dark',
            'logo_path', 'favicon_path',
        ]);
    }

    protected function afterSave(): void
    {
        app(BrandingService::class)->invalidarCacheMaster();

        $novo = $this->getRecord()->only(array_keys($this->estadoAnterior));
        IdentidadeVisualHistorico::registrar('master', $this->estadoAnterior, $novo);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Identidade visual salva';
    }

    protected function getSavedNotificationBody(): ?string
    {
        return 'As alterações serão aplicadas na próxima requisição.';
    }
}
