@extends('store.layout')

@section('title', 'Catálogo')
@section('og_title', 'Catálogo — ' . ($theme->nome_loja ?? $tenant->te_name))
@section('og_description', 'Explore o catálogo completo de ' . ($theme->nome_loja ?? $tenant->te_name) . ' e encontre os melhores produtos.')
@section('meta_description', 'Explore o catálogo completo de ' . ($theme->nome_loja ?? $tenant->te_name) . ' e encontre os melhores produtos.')

@push('styles')
<style>
    /* CATALOG HEADER */
    .cat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 2rem 2rem 1.25rem;
    }
    .cat-header__title {
        font-size: 26px;
        font-weight: 700;
    }
    .cat-sort {
        font-size: 12px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .cat-sort select {
        font-size: 12px;
        font-family: inherit;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 6px 10px;
        color: #111827;
        background: #fff;
    }

    /* FILTER PILLS */
    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 0 2rem 1.5rem;
    }
    .filter-pill {
        font-size: 12px;
        font-weight: 500;
        padding: 7px 16px;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        cursor: pointer;
        text-decoration: none;
        transition: all .15s;
        line-height: 1;
    }
    .filter-pill:hover { border-color: var(--primary); color: var(--primary); }
    .filter-pill.is-active { background: var(--primary); color: #fff; border-color: var(--primary); }

    /* 4-COL GRID */
    .cat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        padding: 0 2rem 2rem;
    }
    @media (max-width: 900px) {
        .cat-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .cat-grid { grid-template-columns: 1fr; padding: 0 1rem 1.5rem; }
        .cat-header { padding: 1.5rem 1rem 1rem; flex-direction: column; align-items: flex-start; }
        .filters { padding: 0 1rem 1.25rem; }
    }

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
        padding: 1rem 2rem 3rem;
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
    .pagination a:hover { border-color: var(--primary); color: var(--primary); }
    .pagination span.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .pagination span.disabled { opacity: .4; cursor: default; }
</style>
@endpush

@section('content')

<div class="cat-header">
    <h1 class="cat-header__title">Catálogo</h1>
    <form class="cat-sort" method="GET">
        @if($active)<input type="hidden" name="categoria" value="{{ $active->slug }}">@endif
        <label for="ordenar">Ordenar por:</label>
        <select name="ordenar" id="ordenar" onchange="this.form.submit()">
            <option value="recentes" {{ $sort === 'recentes' ? 'selected' : '' }}>Mais recentes</option>
            <option value="menor_preco" {{ $sort === 'menor_preco' ? 'selected' : '' }}>Menor preço</option>
            <option value="maior_preco" {{ $sort === 'maior_preco' ? 'selected' : '' }}>Maior preço</option>
            <option value="nome" {{ $sort === 'nome' ? 'selected' : '' }}>Nome (A-Z)</option>
        </select>
    </form>
</div>

<div class="filters">
    <a href="{{ route('store.catalog', $tenant->te_slug) }}?ordenar={{ $sort }}"
       class="filter-pill {{ !$active ? 'is-active' : '' }}">Todos</a>
    @foreach($categories as $cat)
    <a href="{{ route('store.catalog', $tenant->te_slug) }}?categoria={{ $cat->slug }}&ordenar={{ $sort }}"
       class="filter-pill {{ $active?->is($cat) ? 'is-active' : '' }}">{{ $cat->name }}</a>
    @endforeach
</div>

@if($products->isEmpty())
<div class="empty">Nenhum produto encontrado.</div>
@else
<div class="cat-grid">
    @foreach($products as $product)
    <a href="{{ route('store.product', [$tenant->te_slug, $product->id]) }}" class="pcard">
        @if($product->created_at?->diffInDays(now()) <= 14)
            <span class="pcard__badge-new">Novo</span>
        @endif
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

@endsection
