<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\BestSellingProductsWidget;
use App\Filament\App\Widgets\MonthlyRevenueChart;
use App\Filament\App\Widgets\RecentOrdersWidget;
use App\Filament\App\Widgets\RestockProductsWidget;
use App\Filament\App\Widgets\SalesStatsOverview;
use App\Filament\App\Widgets\StoreAttentionWidget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Início';

    protected static ?string $title = 'Sua loja, sob controle.';

    public function getPageClasses(): array
    {
        return ['fi-page-dashboard'];
    }

    public function getSubheading(): ?string
    {
        return 'Veja o que precisa de você hoje.';
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'xl' => 5];
    }

    public function getWidgets(): array
    {
        return [SalesStatsOverview::class, StoreAttentionWidget::class, RecentOrdersWidget::class, RestockProductsWidget::class, MonthlyRevenueChart::class, BestSellingProductsWidget::class];
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('open_pdv')->label('Abrir PDV')->icon(Heroicon::OutlinedShoppingCart)
            ->visible(fn (): bool => PdvPage::canAccess())->url(fn (): string => PdvPage::getUrl())];
    }
}
