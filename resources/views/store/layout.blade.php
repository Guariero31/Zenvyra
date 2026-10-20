<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $theme->nome_loja ?? $tenant->te_name }} @hasSection('title')— @yield('title')@endif</title>

    {{-- SEO --}}
    <meta name="description" content="@yield('meta_description', 'Catálogo online de ' . ($theme->nome_loja ?? $tenant->te_name))">
    <link rel="canonical" href="{{ request()->url() }}">

    {{-- Open Graph --}}
    <meta property="og:type"        content="@yield('og_type', 'website')">
    <meta property="og:url"         content="{{ request()->url() }}">
    <meta property="og:title"       content="@yield('og_title', ($theme->nome_loja ?? $tenant->te_name))">
    <meta property="og:description" content="@yield('og_description', 'Catálogo online de ' . ($theme->nome_loja ?? $tenant->te_name))">
    <meta property="og:locale"      content="pt_BR">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
        <meta property="og:image:width"  content="1200">
        <meta property="og:image:height" content="630">
    @elseif ($theme->logo_path)
        <meta property="og:image" content="{{ Storage::url($theme->logo_path) }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="@yield('og_title', ($theme->nome_loja ?? $tenant->te_name))">
    <meta name="twitter:description" content="@yield('og_description', 'Catálogo online de ' . ($theme->nome_loja ?? $tenant->te_name))">
    @hasSection('og_image')
        <meta name="twitter:image" content="@yield('og_image')">
    @elseif ($theme->logo_path)
        <meta name="twitter:image" content="{{ Storage::url($theme->logo_path) }}">
    @endif

    @if ($theme->favicon_path)
        <link rel="icon" href="{{ Storage::url($theme->favicon_path) }}">
    @endif

    @php
        $primaryColor = \App\Support\ColorNormalizer::hexFromAny($theme->cor_primaria_light ?? '#4F46E5');
        $primaryColor = preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor) ? $primaryColor : '#4F46E5';
        $channels = array_map(static function (string $channel): float {
            $value = hexdec($channel) / 255;
            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split(substr($primaryColor, 1), 2));
        $luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
        $onPrimary = $luminance > 0.179 ? '#111111' : '#ffffff';
    @endphp
    <style>
        :root {
            --primary: {{ $primaryColor }};
            --on-primary: {{ $onPrimary }};
            --bg-page: {{ $theme->bg_page_light ?? '#f9fafb' }};
            --bg-surface: {{ $theme->bg_content_light ?? '#ffffff' }};
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}">
    <script src="{{ asset('js/storefront.js') }}?v={{ filemtime(public_path('js/storefront.js')) }}" defer></script>

    @stack('styles')
</head>
<body>
<a href="#conteudo" class="skip-link">Pular para o conteúdo</a>

@php $wpp = $theme->storeConfig('whatsapp'); @endphp
@if($wpp || $tickerTexts->isNotEmpty())
<div class="topbar">
    <div class="topbar__inner">

            @foreach($tickerTexts as $text)
                <span class="topbar__item">{{ $text }}</span>
                <span class="topbar__item topbar__sep">·</span>
            @endforeach

            @if($wpp)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $wpp) }}" target="_blank" rel="noopener" class="topbar__item">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Chame no WhatsApp
                </a>
                <span class="topbar__item topbar__sep">·</span>
            @endif
    </div>
</div>
@endif

<header class="site-header">
<nav class="nav" aria-label="Navegação principal">
    <a href="{{ route('store.home', $tenant->te_slug) }}" class="nav__logo">
        @if ($theme->logo_path)
            <img src="{{ Storage::url($theme->logo_path) }}" alt="{{ $theme->nome_loja ?? $tenant->te_name }}" style="height:28px;width:auto;">
        @else
            {{ $theme->nome_loja ?? $tenant->te_name }}
        @endif
    </a>
    <div class="nav__links">
        <a href="{{ route('store.home', $tenant->te_slug) }}"
           class="nav__link {{ request()->routeIs('store.home') ? 'nav__link--active' : '' }}">Início</a>
        <a href="{{ route('store.catalog', $tenant->te_slug) }}"
           class="nav__link {{ request()->routeIs('store.catalog') ? 'nav__link--active' : '' }}">Catálogo</a>
        <a href="{{ route('store.contact', $tenant->te_slug) }}"
           class="nav__link {{ request()->routeIs('store.contact') ? 'nav__link--active' : '' }}">Contato</a>
    </div>
    <details class="mobile-menu">
        <summary class="icon-button" aria-label="Abrir menu da loja">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </summary>
        <div class="mobile-menu__links">
            <a href="{{ route('store.home', $tenant->te_slug) }}">Início</a>
            <a href="{{ route('store.catalog', $tenant->te_slug) }}">Catálogo</a>
            <a href="{{ route('store.contact', $tenant->te_slug) }}">Contato</a>
        </div>
    </details>
    <form class="store-search" action="{{ route('store.catalog', $tenant->te_slug) }}" method="GET" role="search">
        <label for="store-search" class="sr-only">Buscar produtos</label>
        <input id="store-search" type="search" name="busca" maxlength="120" placeholder="O que você procura?" value="{{ $search ?? '' }}">
        <button class="icon-button" type="submit" aria-label="Buscar produtos">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        </button>
    </form>
</nav>
</header>

<main id="conteudo" class="store-main" tabindex="-1">
@yield('content')
</main>

<footer class="footer">
<div class="footer__inner">
    <div class="footer__top">
        <div>
            <div class="footer__brand-name">{{ $theme->nome_loja ?? $tenant->te_name }}</div>
            <p class="footer__brand-desc">Catálogo online de {{ $theme->nome_loja ?? $tenant->te_name }}.</p>
        </div>
        <div>
            <div class="footer__col-title">Loja</div>
            <div class="footer__col-links">
                <a href="{{ route('store.home', $tenant->te_slug) }}">Início</a>
                <a href="{{ route('store.catalog', $tenant->te_slug) }}">Catálogo</a>
                <a href="{{ route('store.contact', $tenant->te_slug) }}">Contato</a>
            </div>
        </div>
        <div>
            <div class="footer__col-title">Institucional</div>
            <div class="footer__col-links">
                <a href="{{ route('legal.terms') }}">Termos de Uso</a>
                <a href="{{ route('legal.privacy') }}">Privacidade</a>
            </div>
        </div>
        @php
            $footerWhatsapp = $theme->storeConfig('whatsapp');
            $footerEmail = $theme->storeConfig('email');
            $footerEndereco = $theme->storeConfig('endereco');
        @endphp
        @if($footerWhatsapp || $footerEmail || $footerEndereco)
        <div>
            <div class="footer__col-title">Contato</div>
            <div class="footer__col-links">
                @if($footerWhatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $footerWhatsapp) }}" target="_blank" rel="noopener">{{ $footerWhatsapp }}</a>
                @endif
                @if($footerEmail)
                    <a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a>
                @endif
                @if($footerEndereco)
                    <span>{{ $footerEndereco }}</span>
                @endif
            </div>
        </div>
        @endif
    </div>
    <div class="footer__bottom">
        <span class="footer__brand">© {{ date('Y') }} {{ $theme->nome_loja ?? $tenant->te_name }}. Todos os direitos reservados.</span>
    </div>
</div>
</footer>

<script>
@stack('scripts')
</script>

</body>
</html>
