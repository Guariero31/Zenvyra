<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Models\Tenant;
use App\Services\SalesReportService;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class TopSellersWidget extends Widget
{
    protected static ?int $sort = 5;

    protected string $view = 'filament.app.widgets.top-sellers';

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant
            && $tenant->gestaoSettings()->moduloVendedoresAtivo();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $data = app(SalesReportService::class)->sellersPerformance();

        $maxGross = ! empty($data['gross']) ? max($data['gross']) : 0.0;

        $sellers = [];

        foreach ($data['labels'] as $index => $name) {
            $gross = $data['gross'][$index] ?? 0.0;

            $sellers[] = [
                'name' => $name,
                'gross' => $gross,
                'salesCount' => $data['salesCount'][$index] ?? 0,
                'percentage' => $maxGross > 0 ? (int) round(($gross / $maxGross) * 100) : 0,
            ];
        }

        return [
            'sellers' => $sellers,
        ];
    }
}
