<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\PlatformOverviewWidget;
use App\Filament\Admin\Widgets\QuickActionsWidget;
use App\Filament\Admin\Widgets\StoresByPlanChart;
use App\Filament\Admin\Widgets\SubscriptionStatusChart;
use App\Filament\Admin\Widgets\TenantsGrowthChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard Administrativo';

    public function getSubheading(): ?string
    {
        return 'Visão geral do ecossistema Zenvyra';
    }

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getWidgets(): array
    {
        return [
            PlatformOverviewWidget::class,
            QuickActionsWidget::class,
            TenantsGrowthChart::class,
            SubscriptionStatusChart::class,
            StoresByPlanChart::class,
        ];
    }
}
