@extends('store.layout')

@section('content')

{{-- HERO --}}
@php
    $useCustomSlides = count($carouselSlides) > 0;
    $heroSlide = $useCustomSlides ? $carouselSlides[0] : null;
@endphp
<div class="hero-wrap">
    <div class="hero">
        @if($heroSlide && !empty($heroSlide['image_path']))
            <img class="hero__bg" src="{{ Storage::url($heroSlide['image_path']) }}" alt="" fetchpriority="high">
            <div class="hero__gradient"></div>
        @elseif($featured->first()?->primaryImage)
            <img class="hero__bg" src="{{ Storage::url($featured->first()->primaryImage->image_path) }}" alt="" fetchpriority="high">
            <div class="hero__gradient"></div>
        @endif
        <div class="hero__body">
            <p class="eyebrow">Bem-vindo à {{ $theme->nome_loja ?? $tenant->te_name }}</p>
            <h1 class="hero__title">{{ $heroSlide['title'] ?? 'Encontre seus próximos favoritos.' }}</h1>
            <p class="hero__sub">{{ $heroSlide['subtitle'] ?? 'Explore nossos produtos e encontre o que combina com você.' }}</p>
            <a href="{{ route('store.catalog', $tenant->te_slug) }}" class="hero__cta">Explorar catálogo →</a>
        </div>
    </div>
</div>

{{-- FEATURED PRODUCTS --}}
@if($featured->isNotEmpty())
<div class="section">
    <div class="section__head">
        <h2 class="section__title">Produtos em destaque</h2>
        <a class="section__link" href="{{ route('store.catalog', $tenant->te_slug) }}">Ver todo o catálogo →</a>
    </div>
    <div class="pgrid">
        @foreach($featured as $product)
        @include('store.product-card', ['product' => $product])
        @endforeach
    </div>

</div>
@endif

<section class="store-help">
    <div><h2>Precisa de ajuda para escolher?</h2><p class="muted">Converse com a loja sobre produtos, disponibilidade e entrega.</p></div>
    <a class="button button--secondary" href="{{ route('store.contact', $tenant->te_slug) }}">Fale com a loja →</a>
</section>
@endsection
