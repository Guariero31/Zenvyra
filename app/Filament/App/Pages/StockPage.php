<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\StockMovementsWidget;
use App\Filament\App\Widgets\StockProductsWidget;
use App\Models\Product;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class StockPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Estoque';

    protected static ?string $title = 'Estoque';

    protected static \UnitEnum|string|null $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 22;

    protected static ?string $slug = 'estoque';

    protected string $view = 'filament-panels::pages.page';

    public static function getNavigationBadge(): ?string
    {
        $count = Product::query()
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('stock', '>', 0)
            ->whereDoesntHave('variations')
            ->count();

        return $count > 0 ? (string)$count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->contained(false)
                ->persistTabInQueryString('aba')
                ->tabs([
                    Tab::make('Produtos')
                        ->icon(Heroicon::OutlinedShoppingBag)
                        ->schema([
                            Livewire::make(StockProductsWidget::class),
                        ]),
                    Tab::make('Movimentações')
                        ->icon(Heroicon::OutlinedArrowsRightLeft)
                        ->schema([
                            Livewire::make(StockMovementsWidget::class),
                        ]),
                ]),
        ]);
    }
}
