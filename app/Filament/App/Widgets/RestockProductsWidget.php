<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\Products\ProductResource;
use App\Models\Product;
use App\Services\PanelDashboardService;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RestockProductsWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 2];

    protected static ?string $heading = 'Repor estoque';

    public function table(Table $table): Table
    {
        return $table->query(app(PanelDashboardService::class)->restockQuery()->with(['primaryImage', 'variations'])->orderBy('title')->limit(4))
            ->paginated(false)->emptyStateHeading('Estoque em dia')->emptyStateDescription('Nenhum produto precisa de reposição.')->emptyStateIcon('heroicon-o-check-circle')
            ->columns([
                ImageColumn::make('primaryImage.image_path')->label('')->disk('public')->imageSize(38)->defaultImageUrl(asset('images/product-placeholder.svg')),
                TextColumn::make('title')->label('Produto')->wrap()->description(fn (Product $record): string => $record->hasVariations() ? 'Variação com estoque baixo' : ($record->sku ?? '')),
                TextColumn::make('balance')->label('Saldo')->state(fn (Product $record): string => $record->effectiveStock().' un')->badge()->color(fn (Product $record): string => $record->effectiveStock() === 0 ? 'danger' : 'warning'),
            ])->headerActions([Action::make('all')->label('Ver todos')->link()->color('gray')->url(ProductResource::getUrl('index', ['stock' => 'low']))])
            ->recordUrl(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]));
    }
}
