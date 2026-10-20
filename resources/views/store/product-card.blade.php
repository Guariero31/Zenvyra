@php
    $cardPriceRange = $product->priceRange();
    $cardStock = $product->effectiveStock();
@endphp
<a href="{{ route('store.product', [$tenant->te_slug, $product->id]) }}" class="pcard">
    @if($product->created_at && $product->created_at->greaterThanOrEqualTo(now()->subDays(14)))
        <span class="pcard__badge-new">Novo</span>
    @endif
    <div class="pcard__img">
        @if($product->primaryImage)
            <img src="{{ Storage::url($product->primaryImage->image_path) }}" alt="{{ $product->title }}" loading="lazy" decoding="async" width="400" height="400">
        @else
            <svg class="pcard__img-placeholder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-label="Produto sem foto"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        @endif
    </div>
    @if($product->category)<p class="pcard__cat">{{ $product->category->name }}</p>@endif
    <h3 class="pcard__name">{{ $product->title }}</h3>
    <p class="pcard__price">
        @if($cardPriceRange['max'] > 0)
            @if($cardPriceRange['min'] < $cardPriceRange['max']) A partir de @endif
            R$ {{ number_format($cardPriceRange['min'], 2, ',', '.') }}
        @else
            Consulte o preço
        @endif
    </p>
    <p class="pcard__status">{{ $cardStock > 0 ? 'Disponível' : 'Fora de estoque' }}</p>
</a>
