<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Services\PanelDashboardService;
use Filament\Widgets\Widget;

class SubscriptionBreakdownWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.admin.widgets.subscription-breakdown';

    protected int|string|array $columnSpan = 1;

    protected function getViewData(): array
    {
        return app(PanelDashboardService::class)->platformSummary();
    }
}
