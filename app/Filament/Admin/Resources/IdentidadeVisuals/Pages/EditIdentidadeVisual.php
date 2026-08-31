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

        abort_unless($registro->exists && $registro->getKey() !== null, 503);

        parent::mount($registro->getKey());
    }

    public function getSubheading(): ?string
    {
        return 'Personalize nome, logo e cores do painel administrativo da plataforma.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('restaurar_padroes')
                ->label('Restaurar cores padrão')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Restaurar cores padrão?')
                ->modalDescription('Nome, logo e favicon serão mantidos. Somente as cores voltam ao padrão do sistema.')
                ->modalSubmitActionLabel('Sim, restaurar')
                ->action(function (): void {
                    $defaults = IdentidadeVisual::defaults();
                    unset($defaults['logo_path'], $defaults['favicon_path'], $defaults['nome_sistema']);

                    $this->form->fill(array_merge($this->form->getState(), $defaults));

                    Notification::make()
                        ->title('Cores restauradas')
                        ->body('Clique em Salvar para aplicar as cores padrão.')
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
        return 'As alterações já estão valendo para quem recarregar o painel.';
    }
}
