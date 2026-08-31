<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\TenantBrandings\Pages;

use App\Filament\App\Resources\TenantBrandings\TenantBrandingResource;
use App\Models\IdentidadeVisualHistorico;
use App\Models\Tenant;
use App\Models\TenantTheme;
use App\Services\BrandingService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditTenantBranding extends EditRecord
{
    protected static string $resource = TenantBrandingResource::class;

    protected static ?string $title = 'Identidade Visual da Loja';

    /** @var array<string, mixed> */
    private array $estadoAnterior = [];

    public function mount(int|string|null $record = null): void
    {
        $tenant = Filament::getTenant();

        abort_unless($tenant instanceof Tenant, 404);

        $theme = TenantTheme::obterOuCriarParaTenant((int) $tenant->getKey());

        abort_unless($theme->exists && $theme->getKey() !== null, 503);

        parent::mount($theme->getKey());
    }

    public function getSubheading(): ?string
    {
        return 'Personalize nome, logo e cores do painel da sua loja para toda a equipe.';
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
                    $defaults = TenantTheme::defaults();
                    unset(
                        $defaults['tenant_id'],
                        $defaults['nome_loja'],
                        $defaults['logo_path'],
                        $defaults['favicon_path'],
                    );

                    $this->form->fill(array_merge($this->form->getState(), $defaults));

                    Notification::make()
                        ->title('Cores restauradas')
                        ->body('Clique em Salvar para aplicar as cores padrão.')
                        ->info()
                        ->send();
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (blank($data['nome_loja'] ?? null)) {
            $tenant = Filament::getTenant();

            if ($tenant instanceof Tenant) {
                $data['nome_loja'] = $tenant->te_name;
            }
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->estadoAnterior = $this->getRecord()->only([
            'nome_loja', 'logo_path', 'favicon_path',
            'cor_primaria_light', 'cor_sucesso_light', 'cor_alerta_light', 'cor_erro_light',
            'bg_page_light', 'bg_content_light', 'bg_sidebar_light', 'bg_topbar_light',
            'cor_primaria_dark', 'cor_sucesso_dark', 'cor_alerta_dark', 'cor_erro_dark',
            'bg_page_dark', 'bg_content_dark', 'bg_sidebar_dark', 'bg_topbar_dark',
        ]);
    }

    protected function afterSave(): void
    {
        /** @var TenantTheme $theme */
        $theme = $this->getRecord();

        app(BrandingService::class)->invalidarCacheTenant($theme->tenant_id);

        $novo = $theme->only(array_keys($this->estadoAnterior));
        IdentidadeVisualHistorico::registrar('tenant', $this->estadoAnterior, $novo, $theme->tenant_id);
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
