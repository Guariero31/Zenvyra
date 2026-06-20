<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\TenantBrandings\Pages;

use App\Filament\App\Resources\TenantBrandings\TenantBrandingResource;
use App\Models\IdentidadeVisualHistorico;
use App\Models\Tenant;
use App\Models\TenantTheme;
use App\Services\BrandingService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

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

        parent::mount($theme->getKey());
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
        return 'As alterações serão aplicadas na próxima requisição.';
    }
}
