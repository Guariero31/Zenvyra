<x-filament-panels::page>

    {{-- Barra de Filtros --}}
    <div class="flex flex-col sm:flex-row gap-3">

        {{-- Search --}}
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                @svg('heroicon-o-magnifying-glass', 'h-4 w-4 text-gray-400 dark:text-gray-500')
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por nome ou SKU..."
                class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-gray-500 dark:focus:border-primary-400"
            />
        </div>

        {{-- Filtro Estoque --}}
        <select
            wire:model.live="stockFilter"
            class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-white/10 dark:bg-white/5 dark:text-gray-300"
        >
            <option value="all">Todo Estoque</option>
            <option value="low">Estoque Baixo</option>
            <option value="out">Sem Estoque</option>
        </select>

        {{-- Filtro Categoria --}}
        <select
            wire:model.live="categoryId"
            class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-white/10 dark:bg-white/5 dark:text-gray-300"
        >
            <option value="">Todas Categorias</option>
            @foreach($this->getCategories() as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>

    </div>

    {{-- Grid de Produtos --}}
    @php
        $products = $this->getProducts();
    @endphp

    @if($products->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            @svg('heroicon-o-shopping-bag', 'mx-auto mb-4 h-14 w-14 text-gray-300 dark:text-gray-600')
            <p class="text-base font-medium text-gray-500 dark:text-gray-400">Nenhum produto encontrado</p>
            <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Tente ajustar os filtros ou cadastre um novo produto.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($products as $product)
                @php
                    $stock      = $product->effectiveStock();
                    $isLow      = $product->isLowStock() && $stock > 0;
                    $isOut      = $stock <= 0;
                    $stockBg    = $isOut ? 'bg-red-500 text-white' : ($isLow ? 'bg-amber-400 text-amber-900' : 'bg-emerald-500 text-white');
                    $editUrl    = \App\Filament\App\Resources\Products\ProductResource::getUrl('edit', ['record' => $product]);
                    $varCount   = $product->relationLoaded('variations') ? $product->variations->count() : 0;
                @endphp

                <a
                    href="{{ $editUrl }}"
                    wire:navigate
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-200 hover:border-primary-300 hover:shadow-lg dark:border-white/8 dark:bg-white/4 dark:hover:border-primary-500/50 dark:hover:shadow-primary-900/20"
                >
                    {{-- Imagem --}}
                    <div class="relative aspect-[4/3] overflow-hidden bg-gray-100 dark:bg-gray-800">
                        @if($product->primaryImage)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->primaryImage->image_path) }}"
                                alt="{{ $product->title }}"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                loading="lazy"
                            />
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                @svg('heroicon-o-photo', 'h-16 w-16 text-gray-300 dark:text-gray-600')
                            </div>
                        @endif

                        {{-- Ícone de imagem principal --}}
                        @if($product->primaryImage)
                            <div class="absolute right-2.5 top-2.5 flex h-7 w-7 items-center justify-center rounded-full bg-amber-400 shadow-md">
                                @svg('heroicon-s-star', 'h-3.5 w-3.5 text-amber-800')
                            </div>
                        @endif

                        {{-- Badge de estoque --}}
                        <div class="absolute bottom-2.5 right-2.5">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold shadow {{ $stockBg }}">
                                {{ $stock }} un
                            </span>
                        </div>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $product->title }}
                        </h3>

                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ $product->category?->name ?? '—' }}
                        </p>

                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-primary-600 dark:text-primary-400">
                                R$&nbsp;{{ number_format((float) $product->price, 2, ',', '.') }}
                            </span>

                            @if($varCount > 0)
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $varCount }} {{ Str::plural('variação', $varCount) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Paginação --}}
        @if($products->hasPages())
            <div class="mt-2">
                {{ $products->links() }}
            </div>
        @endif
    @endif

</x-filament-panels::page>
