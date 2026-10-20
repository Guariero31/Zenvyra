@extends('store.layout')

@section('title', $product->title)
@section('og_type', 'product')
@section('og_title', $product->title . ' — ' . ($theme->nome_loja ?? $tenant->te_name))
@section('og_description', $product->description ? str(strip_tags((string) $product->description))->limit(160) : 'Confira ' . $product->title . ' na loja ' . ($theme->nome_loja ?? $tenant->te_name))
@section('meta_description', $product->description ? str(strip_tags((string) $product->description))->limit(160) : 'Confira ' . $product->title . ' na loja ' . ($theme->nome_loja ?? $tenant->te_name))
@if ($product->primaryImage?->image_path)
    @section('og_image', Storage::url($product->primaryImage->image_path))
@endif

@section('content')

<nav class="breadcrumb" aria-label="Você está em">
    <a href="{{ route('store.home', $tenant->te_slug) }}">Início</a>
    <span>›</span>
    <a href="{{ route('store.catalog', $tenant->te_slug) }}">Catálogo</a>
    @if($product->category)
    <span>›</span>
    <a href="{{ route('store.catalog', $tenant->te_slug) }}?categoria={{ $product->category->slug }}">{{ $product->category->name }}</a>
    @endif
    <span>›</span>
    <span>{{ $product->title }}</span>
</nav>

<div class="product-wrap" data-product="{{ $product->title }}">

    {{-- GALLERY --}}
    <div class="gallery">
        <div class="gallery__main" id="galleryMain">
            @php $allImages = $product->images->sortByDesc('is_primary')->values(); @endphp
            @if($allImages->isNotEmpty())
                <img src="{{ Storage::url($allImages->first()->image_path) }}"
                     alt="{{ $product->title }}" id="galleryMainImg" width="600" height="600" fetchpriority="high">
            @else
                <div class="gallery__main-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 21V9"/>
                    </svg>
                </div>
            @endif
        </div>

        @if($allImages->count() > 1)
        <div class="gallery__thumbs" id="galleryThumbs">
            @foreach($allImages as $i => $img)
            <button type="button" class="gallery__thumb {{ $i === 0 ? 'is-active' : '' }}"
                 data-gallery-image="{{ Storage::url($img->image_path) }}"
                 aria-label="Ver foto {{ $i + 1 }} de {{ $allImages->count() }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}">
                <img src="{{ Storage::url($img->image_path) }}" alt="" width="72" height="72" loading="lazy">
            </button>
            @endforeach
        </div>
        @endif
    </div>

    {{-- INFO --}}
    <div class="pinfo">
        @if($product->category)
        <div class="pinfo__cat">{{ $product->category->name }}</div>
        @endif

        <h1 class="pinfo__title">{{ $product->title }}</h1>

        @php $priceRange = $product->priceRange(); @endphp
        <div aria-live="polite" aria-atomic="true">
            <div class="pinfo__price" data-product-price>
                @if($priceRange['max'] > 0)
                @if($priceRange['min'] < $priceRange['max'])
                    A partir de R$ {{ number_format($priceRange['min'], 2, ',', '.') }}
                @else
                    R$ {{ number_format($priceRange['min'], 2, ',', '.') }}
                @endif
                @else Consulte o preço @endif
            </div>
            <div class="pinfo__price-sub" data-price-hint>
                @if($priceRange['min'] < $priceRange['max'])
                    Preço varia conforme a variação selecionada
                @else
                    Consulte condições de pagamento
                @endif
            </div>
        </div>

        {{-- STOCK --}}
        @php $stock = $product->effectiveStock(); @endphp
        @if($stock > 5)
            <span data-product-stock role="status" class="stock-badge stock-badge--ok">● Em estoque</span>
        @elseif($stock > 0)
            <span data-product-stock role="status" class="stock-badge stock-badge--low">● Últimas {{ $stock }} unidades</span>
        @else
            <span data-product-stock role="status" class="stock-badge stock-badge--out">● Fora de estoque</span>
        @endif

        {{-- VARIATIONS --}}
        @if($product->hasVariations() && $product->variations->isNotEmpty())
        <fieldset class="variations">
            <legend>Escolha uma opção</legend>
            <div class="variations__list">
                @foreach($product->variations as $variation)
                <button class="variation-pill {{ $variation->stock <= 0 ? 'is-out' : '' }}"
                        type="button" aria-pressed="false" data-variation="{{ $variation->name }}" data-stock="{{ $variation->stock }}"
                        data-price-cents="{{ str_replace('.', '', (string) ($variation->price ?? $product->price ?? '0.00')) }}"
                        {{ $variation->stock <= 0 ? 'disabled' : '' }}>
                    {{ $variation->name }}
                    @if($variation->stock <= 0)<span class="sr-only"> — indisponível</span>@endif
                </button>
                @endforeach
            </div>
            <p class="muted" data-selection aria-live="polite">Selecione a opção de sua preferência.</p>
        </fieldset>
        @endif

        {{-- DESCRIPTION --}}
        @if($product->description)
        <div class="pinfo__desc">{{ $product->description }}</div>
        @endif

        <details class="product-details">
            <summary>Entrega e pagamento</summary>
            <p>Consulte a loja para confirmar as formas de pagamento, o prazo e as opções de entrega ou retirada.</p>
        </details>
        <div class="product-purchase">
        <div class="pinfo__cta">
            @php $wpp = $theme->storeConfig('whatsapp'); @endphp
            @if($wpp)
            @php
                $wppText = rawurlencode(($stock > 0 ? "Olá! Tenho interesse no produto: " : "Olá! Gostaria de consultar a disponibilidade do produto: ").$product->title."\n".route('store.product', [$tenant->te_slug, $product->id]));
                $wppUrl  = 'https://wa.me/' . preg_replace('/\D/', '', $wpp) . '?text=' . $wppText;
            @endphp
            <a href="{{ $wppUrl }}" target="_blank" rel="noopener" class="btn-wpp" data-whatsapp>
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                <span data-whatsapp-label>{{ $stock > 0 ? 'Pedir pelo WhatsApp' : 'Consultar disponibilidade' }}</span>
            </a>
            @else
                <a href="{{ route('store.contact', $tenant->te_slug) }}" class="button">Consultar a loja</a>
            @endif
            <a href="{{ route('store.catalog', $tenant->te_slug) }}" class="btn-catalog">
                ← Ver catálogo
            </a>
        </div>

        <p class="muted">A compra é combinada diretamente com a loja.</p>
        </div>

        @if($product->sku)
        <div class="sku">Referência: {{ $product->sku }}</div>
        @endif
    </div>

</div>

{{-- RELATED PRODUCTS --}}
@if($related->isNotEmpty())
<div class="related">
    <h2 class="related__title">Você também pode gostar</h2>
    <div class="pgrid">
        @foreach($related as $rel)
        @include('store.product-card', ['product' => $rel])
        @endforeach
    </div>
</div>
@endif

@endsection
