@extends('store.layout')

@php $wpp = $theme->storeConfig('whatsapp'); @endphp

@section('topbar')
@if($wpp)
<div class="topbar">
    <div class="topbar__inner">
        @foreach(range(0, 1) as $_)
            <span class="topbar__item">✦ Primeira compra? Fale com a gente</span>
            <span class="topbar__item topbar__sep">·</span>
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $wpp) }}" target="_blank" rel="noopener" class="topbar__item">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Chame no WhatsApp
            </a>
            <span class="topbar__item topbar__sep">·</span>
        @endforeach
    </div>
</div>
@endif
@endsection

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
