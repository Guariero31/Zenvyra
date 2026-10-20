@extends('store.layout')

@section('title', 'Catálogo')
@section('og_title', 'Catálogo — ' . ($theme->nome_loja ?? $tenant->te_name))
@section('og_description', 'Explore o catálogo completo de ' . ($theme->nome_loja ?? $tenant->te_name) . ' e encontre os melhores produtos.')
@section('meta_description', 'Explore o catálogo completo de ' . ($theme->nome_loja ?? $tenant->te_name) . ' e encontre os melhores produtos.')

@section('content')

<div class="catalog-intro">
    <p class="eyebrow">{{ $theme->nome_loja ?? $tenant->te_name }}</p>
    <h1>{{ $active?->name ?? 'Explore nosso catálogo' }}</h1>
    <p class="muted">Encontre seus favoritos e converse com a loja para saber mais.</p>
</div>
<div class="cat-header">
    <nav class="filters" aria-label="Filtrar por categoria">
        <a href="{{ route('store.catalog', ['slug' => $tenant->te_slug, 'ordenar' => $sort, 'busca' => $search]) }}" class="filter-pill {{ !$active ? 'is-active' : '' }}" @if(!$active) aria-current="page" @endif>Todos</a>
        @foreach($categories as $category)
            <a href="{{ route('store.catalog', ['slug' => $tenant->te_slug, 'categoria' => $category->slug, 'ordenar' => $sort, 'busca' => $search]) }}" class="filter-pill {{ $active?->is($category) ? 'is-active' : '' }}" @if($active?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
        @endforeach
    </nav>
    <form class="cat-sort" method="GET" action="{{ route('store.catalog', $tenant->te_slug) }}">
        @if($active)<input type="hidden" name="categoria" value="{{ $active->slug }}">@endif
        @if($search !== '')<input type="hidden" name="busca" value="{{ $search }}">@endif
        <label for="ordenar">Ordenar por</label>
        <select name="ordenar" id="ordenar">
            <option value="recentes" @selected($sort === 'recentes')>Mais recentes</option>
            <option value="menor_preco" @selected($sort === 'menor_preco')>Menor preço</option>
            <option value="maior_preco" @selected($sort === 'maior_preco')>Maior preço</option>
            <option value="nome" @selected($sort === 'nome')>Nome (A–Z)</option>
        </select>
        <button type="submit">Aplicar</button>
    </form>
</div>
<p class="catalog-results" role="status">{{ $products->total() }} {{ $products->total() === 1 ? 'produto encontrado' : 'produtos encontrados' }}@if($search !== '') para “{{ $search }}”@endif</p>

@if($products->isEmpty())
<div class="empty">
    <h2>Nenhum produto encontrado.</h2>
    <p>Tente outro nome ou remova os filtros para explorar o catálogo.</p>
    <a class="button" href="{{ route('store.catalog', $tenant->te_slug) }}">Ver todos os produtos</a>
</div>
@else
<div class="cat-grid">
    @foreach($products as $product)
    @include('store.product-card', ['product' => $product])
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
            <span class="active" aria-current="page">{{ $page }}</span>
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
