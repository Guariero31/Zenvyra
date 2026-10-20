<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Filament\Admin\Widgets\PlatformAgendaWidget;
use App\Filament\Admin\Widgets\PlatformAttentionWidget;
use App\Filament\Admin\Widgets\PlatformOverviewWidget;
use App\Filament\Admin\Widgets\StoresOverviewWidget;
use App\Filament\Admin\Widgets\SubscriptionBreakdownWidget;
use App\Filament\Admin\Widgets\TenantsGrowthChart;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Sua plataforma, em perspectiva.';

    protected static ?string $navigationLabel = 'Visão geral';

    public function getPageClasses(): array
    {
        return ['fi-page-dashboard'];
    }

    public function getSubheading(): ?string
    {
        return 'Visão geral das lojas e assinaturas.';
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'xl' => 3];
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('createStore')->label('Criar loja')->icon('heroicon-o-plus')->url(TenantResource::getUrl('create'))];
    }

    public function getWidgets(): array
    {
        return [PlatformOverviewWidget::class, PlatformAttentionWidget::class, StoresOverviewWidget::class, PlatformAgendaWidget::class, TenantsGrowthChart::class, SubscriptionBreakdownWidget::class];
    }
}
