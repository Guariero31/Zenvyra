<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Products\Pages;

use App\Filament\App\Resources\Products\ProductResource;
use App\Filament\Exports\ProductExporter;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ListProducts extends Page
{
    use WithPagination;

    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.app.resources.products.pages.list-products';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'cat')]
    public ?int $categoryId = null;

    #[Url(as: 'stock')]
    public string $stockFilter = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedStockFilter(): void
    {
        $this->resetPage();
    }

    public function getProducts(): LengthAwarePaginator
    {
        return Product::query()
            ->with(['primaryImage', 'variations', 'category'])
            ->when(
                filled($this->search),
                fn ($q) => $q->where(function ($q): void {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%");
                })
            )
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->stockFilter === 'low', fn ($q) => $q->where(function ($q): void {
                $q->where(fn ($q) => $q
                    ->where('has_variations', false)
                    ->whereColumn('stock', '<=', 'low_stock_threshold'))
                    ->orWhere(fn ($q) => $q
                        ->where('has_variations', true)
                        ->whereHas('variations', fn ($q) => $q->whereColumn('stock', '<=', 'low_stock_threshold')));
            }))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where(function ($q): void {
                $q->where(fn ($q) => $q
                    ->where('has_variations', false)
                    ->where('stock', '<=', 0))
                    ->orWhere(fn ($q) => $q
                        ->where('has_variations', true)
                        ->whereRaw('(select coalesce(sum(stock), 0) from product_variations where product_variations.product_id = products.id) <= 0'));
            }))
            ->orderBy('title')
            ->paginate(12);
    }

    public function getCategories(): Collection
    {
        return Category::orderBy('name')->pluck('name', 'id');
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Catálogo & Galeria';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Gerencie seus produtos e imagens';
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make('export')
                ->exporter(ProductExporter::class)
                ->label('Exportar')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->outlined(),

            Action::make('create')
                ->label('Novo Produto')
                ->icon(Heroicon::OutlinedPlus)
                ->url(ProductResource::getUrl('create'))
                ->button(),
        ];
    }
}
