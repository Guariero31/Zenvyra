@extends('store.layout')

@push('styles')
<style>
    /* HERO */
    .hero-wrap { padding: 2rem 2rem 0; }
    .hero {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        min-height: 460px;
        display: flex;
        align-items: flex-end;
        background: #111827;
    }
    .hero__bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
    }
    .hero__gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.6) 0%, rgba(0,0,0,.1) 55%, transparent 100%);
    }
    .hero__body { position: relative; z-index: 2; padding: 2.5rem; max-width: 480px; }
    .hero__title {
        font-size: clamp(26px, 3.2vw, 36px);
        font-weight: 700;
        line-height: 1.15;
        color: #fff;
        margin-bottom: .6rem;
    }
    .hero__sub {
        font-size: 14px;
        color: rgba(255,255,255,.85);
        margin-bottom: 1.5rem;
    }
    .hero__cta {
        display: inline-flex;
        align-items: center;
        padding: 12px 28px;
        background: var(--primary);
        color: #fff;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }
    .hero__cta:hover { opacity: .92; }

    @media (max-width: 500px) {
        .hero-wrap { padding: 1rem 1rem 0; }
        .hero { min-height: 360px; }
        .hero__body { padding: 1.5rem; }
    }
</style>
@endpush

@section('content')

{{-- HERO --}}
@php
    $useCustomSlides = count($carouselSlides) > 0;
    $heroSlide = $useCustomSlides ? $carouselSlides[0] : null;
@endphp
<div class="hero-wrap">
    <div class="hero">
        @if($heroSlide && !empty($heroSlide['image_path']))
            <div class="hero__bg" style="background-image: url('{{ Storage::url($heroSlide['image_path']) }}')"></div>
            <div class="hero__gradient"></div>
        @elseif($featured->first()?->primaryImage)
            <div class="hero__bg" style="background-image: url('{{ Storage::url($featured->first()->primaryImage->image_path) }}')"></div>
            <div class="hero__gradient"></div>
        @endif
        <div class="hero__body">
            <h1 class="hero__title">{{ $heroSlide['title'] ?? 'Novidades toda semana' }}</h1>
            <p class="hero__sub">{{ $heroSlide['subtitle'] ?? 'Confira as tendências que acabaram de chegar' }}</p>
            <a href="{{ route('store.catalog', $tenant->te_slug) }}" class="hero__cta">Ver Coleção</a>
        </div>
    </div>
</div>

{{-- FEATURED PRODUCTS --}}
@if($featured->isNotEmpty())
<div class="section">
    <div class="section__head">
        <span class="section__title">Produtos em Destaque</span>
    </div>
    <div class="pgrid">
        @foreach($featured as $product)
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
    <div style="text-align:center;margin-top:1.5rem;">
        <a href="{{ route('store.catalog', $tenant->te_slug) }}" class="hero__cta" style="background:#fff;color:#111827;border:1px solid #e5e7eb;">Ver Todo o Catálogo</a>
    </div>
</div>
@endif

@endsection
