@extends('store.layout', ['navClass' => 'nav--solid'])

@section('title', 'Catálogo')
@section('og_title', 'Catálogo — ' . ($theme->nome_loja ?? $tenant->te_name))
@section('og_description', 'Explore o catálogo completo de ' . ($theme->nome_loja ?? $tenant->te_name) . ' e encontre os melhores produtos.')
@section('meta_description', 'Explore o catálogo completo de ' . ($theme->nome_loja ?? $tenant->te_name) . ' e encontre os melhores produtos.')

@push('styles')
<style>
    /* CATALOG HEADER */
    .cat-header {
        padding: 2rem 2rem 0;
    }
    .cat-header__title {
        font-size: 28px;
        font-style: italic;
        font-weight: 500;
        letter-spacing: -1px;
        margin-bottom: .5rem;
    }
    .cat-header__sub {
        font-size: 12px;
        color: #9ca3af;
    }

    /* FILTER PILLS */
    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .filter-pill {
        font-size: 11px;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 6px 16px;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        cursor: pointer;
        text-decoration: none;
        transition: all .15s;
        line-height: 1;
    }
    .filter-pill:hover { border-color: #111; color: #111; }
    .filter-pill.is-active { background: #111; color: #fff; border-color: #111; }

    /* 3-COL GRID */
    .cat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        padding: 2rem;
    }
    @media (max-width: 800px) {
        .cat-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .cat-grid { grid-template-columns: 1fr; padding: 1rem; }
        .filters { padding: 1rem; }
    }

    /* CATALOG CARD (same .pcard but with fixed img height) */
    .cat-grid .pcard__img { aspect-ratio: 4 / 3; }

    /* EMPTY */
    .empty {
        padding: 4rem 2rem;
        text-align: center;
        color: #9ca3af;
        font-size: 13px;
    }

    /* PAGINATION */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 4px;
        padding: 2rem;
    }
    .pagination a,
    .pagination span {
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 12px;
        border: 1px solid #e5e7eb;
        color: #6b7280;
        text-decoration: none;
        transition: all .15s;
    }
    .pagination a:hover { border-color: #111; color: #111; }
    .pagination span.active { background: #111; color: #fff; border-color: #111; }
    .pagination span.disabled { opacity: .4; cursor: default; }
</style>
@endpush

@section('content')

<div class="cat-header">
    <h1 class="cat-header__title">
        @if($active){{ $active->name }}@else Todos os produtos @endif
    </h1>
    <p class="cat-header__sub">{{ $products->total() }} produto{{ $products->total() !== 1 ? 's' : '' }}</p>
</div>

<div class="filters">
    <a href="{{ route('store.catalog', $tenant->te_slug) }}"
       class="filter-pill {{ !$active ? 'is-active' : '' }}">Todos</a>
    @foreach($categories as $cat)
    <a href="{{ route('store.catalog', $tenant->te_slug) }}?categoria={{ $cat->slug }}"
       class="filter-pill {{ $active?->is($cat) ? 'is-active' : '' }}">{{ $cat->name }}</a>
    @endforeach
</div>

@if($products->isEmpty())
<div class="empty">Nenhum produto encontrado.</div>
@else
<div class="cat-grid">
    @foreach($products as $product)
    <a href="{{ route('store.product', [$tenant->te_slug, $product->id]) }}" class="pcard">
        <div class="pcard__img">
            @if($product->primaryImage)
                <img src="{{ Storage::url($product->primaryImage->image_path) }}" alt="{{ $product->title }}">
            @else
                <svg class="pcard__img-placeholder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            @endif
        </div>
        <div class="pcard__cat">{{ $product->category?->name ?? '—' }}</div>
        <div class="pcard__name">{{ $product->title }}</div>
        @if($product->price)
        <div class="pcard__price">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
        @endif
    </a>
    @endforeach
</div>

@if($products->hasPages())
<nav class="pagination" aria-label="Paginação">
    @if($products->onFirstPage())
        <span class="disabled">‹</span>
    @else
        <a href="{{ $products->previousPageUrl() }}" aria-label="Anterior">‹</a>
    @endif

    @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
        @if($page === $products->currentPage())
            <span class="active">{{ $page }}</span>
        @else
            <a href="{{ $url }}">{{ $page }}</a>
        @endif
    @endforeach

    @if($products->hasMorePages())
        <a href="{{ $products->nextPageUrl() }}" aria-label="Próximo">›</a>
    @else
        <span class="disabled">›</span>
    @endif
</nav>
@endif
@endif

<footer class="footer">
    <span class="footer__brand">© {{ date('Y') }} {{ $theme->nome_loja ?? $tenant->te_name }}</span>
    <div class="footer__links">
        <a href="{{ route('store.home', $tenant->te_slug) }}" class="footer__link">Início</a>
        <a href="{{ route('store.contact', $tenant->te_slug) }}" class="footer__link">Contato</a>
    </div>
</footer>
@endsection
