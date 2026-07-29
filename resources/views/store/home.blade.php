@extends('store.layout', ['navClass' => ''])

@section('content')

@push('styles')
<style>
    /* CAROUSEL */
    .carousel {
        position: relative;
        height: 100vh;
        min-height: 480px;
        overflow: hidden;
        background: #0d0d0d;
    }
    .carousel__slides { height: 100%; }
    .carousel__slide {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-end;
        padding: 0 2rem 5rem;
        opacity: 0;
        transition: opacity .7s ease;
        pointer-events: none;
    }
    .carousel__slide.is-active {
        opacity: 1;
        pointer-events: auto;
    }
    .carousel__slide-bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        transition: transform 8s ease;
    }
    .carousel__slide.is-active .carousel__slide-bg {
        transform: scale(1.04);
    }
    .carousel__gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.75) 0%, rgba(0,0,0,.2) 50%, transparent 100%);
    }
    .carousel__icon {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .carousel__icon svg { width: 80px; height: 80px; opacity: .08; color: #fff; }
    .carousel__body { position: relative; z-index: 2; max-width: 620px; }
    .carousel__eyebrow {
        font-size: 10px;
        letter-spacing: .15em;
        text-transform: uppercase;
        color: rgba(255,255,255,.5);
        margin-bottom: .75rem;
    }
    .carousel__title {
        font-size: clamp(34px, 5vw, 52px);
        font-style: italic;
        font-weight: 500;
        letter-spacing: -2px;
        line-height: 1.05;
        color: #fff;
        margin-bottom: .75rem;
    }
    .carousel__sub {
        font-size: 13px;
        color: rgba(255,255,255,.55);
        line-height: 1.5;
    }
    /* controls */
    .carousel__arrows {
        position: absolute;
        right: 2rem;
        bottom: 5rem;
        display: flex;
        gap: 6px;
        z-index: 10;
    }
    .carousel__arrow {
        width: 36px;
        height: 36px;
        border: 1px solid rgba(255,255,255,.2);
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.7);
        transition: border-color .15s, color .15s;
        border-radius: 4px;
    }
    .carousel__arrow:hover { border-color: rgba(255,255,255,.6); color: #fff; }
    .carousel__arrow svg { width: 14px; height: 14px; }
    .carousel__dots {
        position: absolute;
        bottom: 1.75rem;
        left: 2rem;
        display: flex;
        gap: 5px;
        z-index: 10;
    }
    .carousel__dot {
        width: 5px; height: 5px;
        border-radius: 50%;
        background: rgba(255,255,255,.3);
        cursor: pointer;
        transition: background .2s, transform .2s;
    }
    .carousel__dot.is-active {
        background: #fff;
        transform: scale(1.4);
    }
    .carousel__counter {
        position: absolute;
        top: 4.5rem;
        right: 2rem;
        font-size: 11px;
        color: rgba(255,255,255,.35);
        z-index: 10;
        letter-spacing: .06em;
    }
</style>
@endpush

{{-- CAROUSEL --}}
@php
    $useCustomSlides = count($carouselSlides) > 0;
    $slideCount      = $useCustomSlides ? count($carouselSlides) : max($featured->count(), 1);
@endphp
<div class="carousel" id="carousel">
    <div class="carousel__slides">

        @if($useCustomSlides)
            {{-- Custom configured slides --}}
            @foreach($carouselSlides as $i => $slide)
            <div class="carousel__slide {{ $i === 0 ? 'is-active' : '' }}">
                @if(!empty($slide['image_path']))
                    <div class="carousel__slide-bg" style="background-image: url('{{ Storage::url($slide['image_path']) }}')"></div>
                    <div class="carousel__gradient"></div>
                @else
                    <div class="carousel__slide-bg" style="background: hsl({{ ($i * 55) }}, 12%, 8%)"></div>
                    <div class="carousel__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    </div>
                @endif
                <div class="carousel__body">
                    @if(!empty($slide['eyebrow']))
                    <div class="carousel__eyebrow">{{ $slide['eyebrow'] }}</div>
                    @endif
                    <h2 class="carousel__title">{{ $slide['title'] ?? '' }}</h2>
                    @if(!empty($slide['subtitle']))
                    <p class="carousel__sub">{{ $slide['subtitle'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        @else
            {{-- Auto: use featured products as slides --}}
            @forelse($featured as $i => $product)
            <div class="carousel__slide {{ $i === 0 ? 'is-active' : '' }}">
                @if ($product->primaryImage)
                    <div class="carousel__slide-bg" style="background-image: url('{{ Storage::url($product->primaryImage->image_path) }}')"></div>
                    <div class="carousel__gradient"></div>
                @else
                    <div class="carousel__slide-bg" style="background: hsl({{ ($i * 60) }}, 12%, 8%)"></div>
                    <div class="carousel__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    </div>
                @endif
                <div class="carousel__body">
                    <div class="carousel__eyebrow">{{ $product->category?->name ?? 'Destaque' }}</div>
                    <h2 class="carousel__title">{{ $product->title }}</h2>
                    @if ($product->description)
                    <p class="carousel__sub">{{ Str::limit($product->description, 90) }}</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="carousel__slide is-active">
                <div class="carousel__slide-bg" style="background: #111"></div>
                <div class="carousel__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                </div>
                <div class="carousel__body">
                    <div class="carousel__eyebrow">Bem-vindo</div>
                    <h2 class="carousel__title">{{ $theme->nome_loja ?? $tenant->te_name }}</h2>
                    <p class="carousel__sub">Explore nosso catálogo e descubra produtos incríveis.</p>
                </div>
            </div>
            @endforelse
        @endif

    </div>

    <div class="carousel__counter" id="carouselCounter">1 / {{ $slideCount }}</div>

    @if($slideCount > 1)
    <div class="carousel__arrows">
        <button class="carousel__arrow" id="carouselPrev" aria-label="Anterior">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button class="carousel__arrow" id="carouselNext" aria-label="Próximo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </button>
    </div>
    <div class="carousel__dots" id="carouselDots">
        @foreach(range(0, $slideCount - 1) as $i)
        <button class="carousel__dot {{ $i === 0 ? 'is-active' : '' }}" data-index="{{ $i }}" aria-label="Slide {{ $i+1 }}"></button>
        @endforeach
    </div>
    @endif
</div>

{{-- TICKER --}}
@php
    $tickerSource = count($tickerItems) > 0
        ? collect($tickerItems)->pluck('text')->filter()->values()
        : $categories->pluck('name');
@endphp
@if($tickerSource->isNotEmpty())
<div class="ticker">
    <div class="ticker__inner">
        @foreach(range(0,1) as $_)
            @foreach($tickerSource as $item)
                <span class="ticker__item">{{ $item }}</span>
                <span class="ticker__item ticker__sep">·</span>
            @endforeach
        @endforeach
    </div>
</div>
@endif

{{-- FEATURED PRODUCTS --}}
@if($featured->isNotEmpty())
<div class="section">
    <div class="section__head">
        <span class="section__title">Em destaque</span>
        <a href="{{ route('store.catalog', $tenant->te_slug) }}" class="section__link">Ver tudo →</a>
    </div>
    <div class="pgrid">
        @foreach($featured as $product)
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
</div>
@endif

{{-- WHATSAPP BANNER --}}
@php $wpp = $theme->storeConfig('whatsapp'); @endphp
@if($wpp)
<div class="wpp-banner">
    <div>
        <div class="wpp-banner__title">Tire suas dúvidas pelo WhatsApp</div>
        <div class="wpp-banner__sub">Atendimento rápido, direto com nossa equipe.</div>
    </div>
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $wpp) }}" target="_blank" rel="noopener" class="wpp-btn">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Falar agora
    </a>
</div>
@endif

{{-- FOOTER --}}
<footer class="footer">
    <span class="footer__brand">© {{ date('Y') }} {{ $theme->nome_loja ?? $tenant->te_name }}</span>
    <div class="footer__links">
        <a href="{{ route('store.catalog', $tenant->te_slug) }}" class="footer__link">Catálogo</a>
        <a href="{{ route('store.contact', $tenant->te_slug) }}" class="footer__link">Contato</a>
    </div>
</footer>

@push('scripts')
(function () {
    var slides = document.querySelectorAll('.carousel__slide');
    var dots   = document.querySelectorAll('.carousel__dot');
    var counter = document.getElementById('carouselCounter');
    var total  = slides.length;
    var current = 0;
    var timer;

    function goTo(n) {
        slides[current].classList.remove('is-active');
        if (dots[current]) dots[current].classList.remove('is-active');
        current = (n + total) % total;
        slides[current].classList.add('is-active');
        if (dots[current]) dots[current].classList.add('is-active');
        if (counter) counter.textContent = (current + 1) + ' / ' + total;
    }

    function startTimer() {
        clearInterval(timer);
        timer = setInterval(function () { goTo(current + 1); }, 5000);
    }

    var prev = document.getElementById('carouselPrev');
    var next = document.getElementById('carouselNext');
    if (prev) prev.addEventListener('click', function () { goTo(current - 1); startTimer(); });
    if (next) next.addEventListener('click', function () { goTo(current + 1); startTimer(); });

    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () { goTo(i); startTimer(); });
    });

    if (total > 1) startTimer();
})();
@endpush

@endsection
