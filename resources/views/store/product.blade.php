@extends('store.layout', ['navClass' => 'nav--solid'])

@section('title', $product->title)
@section('og_type', 'product')
@section('og_title', $product->title . ' — ' . ($theme->nome_loja ?? $tenant->te_name))
@section('og_description', $product->description ? str(strip_tags((string) $product->description))->limit(160) : 'Confira ' . $product->title . ' na loja ' . ($theme->nome_loja ?? $tenant->te_name))
@section('meta_description', $product->description ? str(strip_tags((string) $product->description))->limit(160) : 'Confira ' . $product->title . ' na loja ' . ($theme->nome_loja ?? $tenant->te_name))
@if ($product->primaryImage?->image_path)
    @section('og_image', Storage::url($product->primaryImage->image_path))
@endif

@push('styles')
<style>
    .product-wrap {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        padding: 2.5rem 2rem;
        max-width: 1100px;
        margin: 0 auto;
    }

    /* GALLERY */
    .gallery { display: flex; flex-direction: column; gap: .75rem; }
    .gallery__main {
        border-radius: 12px;
        background: #f9fafb;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .gallery__main img { width: 100%; height: 100%; object-fit: cover; }
    .gallery__main-placeholder svg { width: 64px; height: 64px; opacity: .2; }
    .gallery__thumbs { display: flex; gap: .5rem; flex-wrap: wrap; }
    .gallery__thumb {
        width: 72px;
        height: 72px;
        border-radius: 8px;
        background: #f9fafb;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color .15s;
        flex-shrink: 0;
    }
    .gallery__thumb.is-active { border-color: #111; }
    .gallery__thumb img { width: 100%; height: 100%; object-fit: cover; }

    /* PRODUCT INFO */
    .pinfo { display: flex; flex-direction: column; gap: 1.25rem; }
    .pinfo__cat {
        font-size: 10px;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #9ca3af;
    }
    .pinfo__title {
        font-size: clamp(22px, 3vw, 30px);
        font-style: italic;
        font-weight: 500;
        letter-spacing: -1px;
        line-height: 1.15;
    }
    .pinfo__price {
        font-size: 26px;
        font-weight: 500;
        letter-spacing: -.5px;
    }
    .pinfo__price-sub { font-size: 12px; color: #9ca3af; margin-top: 2px; }
    .pinfo__desc {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.7;
        border-top: 1px solid #f3f4f6;
        padding-top: 1.25rem;
    }

    /* VARIATIONS */
    .variations { border-top: 1px solid #f3f4f6; padding-top: 1.25rem; }
    .variations__label {
        font-size: 11px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #9ca3af;
        margin-bottom: .6rem;
    }
    .variations__list { display: flex; flex-wrap: wrap; gap: .5rem; }
    .variation-pill {
        font-size: 12px;
        padding: 6px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        cursor: pointer;
        transition: all .15s;
        background: #fff;
    }
    .variation-pill:hover,
    .variation-pill.is-active { background: #111; color: #fff; border-color: #111; }
    .variation-pill.is-out { opacity: .4; cursor: not-allowed; text-decoration: line-through; }

    /* CTA */
    .pinfo__cta { display: flex; gap: .75rem; flex-wrap: wrap; }
    .btn-wpp {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 24px;
        background: #25d366;
        color: #fff;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        font-family: var(--font);
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s;
    }
    .btn-wpp:hover { background: #1ebe5d; }
    .btn-wpp svg { width: 18px; height: 18px; flex-shrink: 0; }
    .btn-catalog {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 13px 20px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 13px;
        color: #6b7280;
        text-decoration: none;
        transition: border-color .15s, color .15s;
    }
    .btn-catalog:hover { border-color: #111; color: #111; }

    /* STOCK BADGE */
    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: 500;
    }
    .stock-badge--ok { background: #f0fdf4; color: #166534; }
    .stock-badge--low { background: #fefce8; color: #854d0e; }
    .stock-badge--out { background: #fef2f2; color: #991b1b; }

    /* RELATED */
    .related { padding: 2.5rem 2rem; border-top: 1px solid #f3f4f6; }
    .related__title {
        font-size: 12px;
        font-weight: 500;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }

    /* BREADCRUMB */
    .breadcrumb {
        padding: .75rem 2rem;
        border-bottom: 1px solid #f9fafb;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #9ca3af;
    }
    .breadcrumb a { color: #9ca3af; }
    .breadcrumb a:hover { color: #111; }
    .breadcrumb span { color: #111; }

    @media (max-width: 750px) {
        .product-wrap { grid-template-columns: 1fr; gap: 1.5rem; padding: 1.5rem 1rem; }
        .related { padding: 1.5rem 1rem; }
        .breadcrumb { padding: .75rem 1rem; }
    }
</style>
@endpush

@section('content')

<div class="breadcrumb">
    <a href="{{ route('store.home', $tenant->te_slug) }}">Home</a>
    <span>›</span>
    <a href="{{ route('store.catalog', $tenant->te_slug) }}">Catálogo</a>
    @if($product->category)
    <span>›</span>
    <a href="{{ route('store.catalog', $tenant->te_slug) }}?categoria={{ $product->category->slug }}">{{ $product->category->name }}</a>
    @endif
    <span>›</span>
    <span>{{ $product->title }}</span>
</div>

<div class="product-wrap">

    {{-- GALLERY --}}
    <div class="gallery">
        <div class="gallery__main" id="galleryMain">
            @php $allImages = $product->images->sortByDesc('is_primary'); @endphp
            @if($allImages->isNotEmpty())
                <img src="{{ Storage::url($allImages->first()->image_path) }}"
                     alt="{{ $product->title }}" id="galleryMainImg">
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
            <div class="gallery__thumb {{ $i === 0 ? 'is-active' : '' }}"
                 data-src="{{ Storage::url($img->image_path) }}"
                 onclick="gallerySelect(this)">
                <img src="{{ Storage::url($img->image_path) }}" alt="{{ $product->title }}">
            </div>
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

        @if($product->price)
        <div>
            <div class="pinfo__price">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
            <div class="pinfo__price-sub">Consulte condições de pagamento</div>
        </div>
        @endif

        {{-- STOCK --}}
        @php $stock = $product->effectiveStock(); @endphp
        @if($stock > 5)
            <span class="stock-badge stock-badge--ok">● Em estoque</span>
        @elseif($stock > 0)
            <span class="stock-badge stock-badge--low">● Últimas {{ $stock }} unidades</span>
        @else
            <span class="stock-badge stock-badge--out">● Fora de estoque</span>
        @endif

        {{-- VARIATIONS --}}
        @if($product->variations->isNotEmpty())
        <div class="variations">
            <div class="variations__label">Variações</div>
            <div class="variations__list">
                @foreach($product->variations as $variation)
                <button class="variation-pill {{ $variation->stock <= 0 ? 'is-out' : '' }}"
                        type="button"
                        {{ $variation->stock <= 0 ? 'disabled' : '' }}>
                    {{ $variation->name }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- DESCRIPTION --}}
        @if($product->description)
        <div class="pinfo__desc">{{ $product->description }}</div>
        @endif

        {{-- CTA --}}
        <div class="pinfo__cta">
            @php $wpp = $theme->storeConfig('whatsapp'); @endphp
            @if($wpp)
            @php
                $wppText = urlencode("Olá! Tenho interesse no produto: {$product->title}");
                $wppUrl  = 'https://wa.me/' . preg_replace('/\D/', '', $wpp) . '?text=' . $wppText;
            @endphp
            <a href="{{ $wppUrl }}" target="_blank" rel="noopener" class="btn-wpp">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Pedir pelo WhatsApp
            </a>
            @endif
            <a href="{{ route('store.catalog', $tenant->te_slug) }}" class="btn-catalog">
                ← Ver catálogo
            </a>
        </div>

        @if($product->sku)
        <div style="font-size:11px;color:#d1d5db;margin-top:.5rem;">SKU: {{ $product->sku }}</div>
        @endif
    </div>

</div>

{{-- RELATED PRODUCTS --}}
@if($related->isNotEmpty())
<div class="related">
    <div class="related__title">Outros da mesma categoria</div>
    <div class="pgrid">
        @foreach($related as $rel)
        <a href="{{ route('store.product', [$tenant->te_slug, $rel->id]) }}" class="pcard">
            <div class="pcard__img">
                @if($rel->primaryImage)
                    <img src="{{ Storage::url($rel->primaryImage->image_path) }}" alt="{{ $rel->title }}">
                @else
                    <svg class="pcard__img-placeholder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                @endif
            </div>
            <div class="pcard__name">{{ $rel->title }}</div>
            @if($rel->price)
            <div class="pcard__price">R$ {{ number_format($rel->price, 2, ',', '.') }}</div>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif

<footer class="footer">
    <span class="footer__brand">© {{ date('Y') }} {{ $theme->nome_loja ?? $tenant->te_name }}</span>
    <div class="footer__links">
        <a href="{{ route('store.home', $tenant->te_slug) }}" class="footer__link">Home</a>
        <a href="{{ route('store.catalog', $tenant->te_slug) }}" class="footer__link">Catálogo</a>
        <a href="{{ route('store.contact', $tenant->te_slug) }}" class="footer__link">Contato</a>
    </div>
</footer>

@push('scripts')
function gallerySelect(el) {
    document.getElementById('galleryMainImg').src = el.dataset.src;
    document.querySelectorAll('.gallery__thumb').forEach(function(t) {
        t.classList.remove('is-active');
    });
    el.classList.add('is-active');
}
@endpush

@endsection
