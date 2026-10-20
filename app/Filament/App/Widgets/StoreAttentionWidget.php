<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\PanelDashboardService;
use Filament\Widgets\Widget;

class StoreAttentionWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.app.widgets.store-attention';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return ['pending' => Order::where('status', OrderStatus::Pending)->count(), 'restock' => app(PanelDashboardService::class)->restockQuery()->count()];
    }
}
