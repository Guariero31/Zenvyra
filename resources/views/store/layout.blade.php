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

    <style>
        :root {
            --primary: {{ $theme->cor_primaria_light ?? '#4648d4' }};
            --font: system-ui, -apple-system, 'Segoe UI', sans-serif;
            --bg-page: #f9fafb;
            --bg-surface: #ffffff;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font);
            color: #111827;
            background: var(--bg-page);
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }

        img { display: block; max-width: 100%; }

        /* NAV */
        .nav {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 0 2rem;
            height: 60px;
            background: var(--bg-surface);
            border-bottom: 1px solid #e5e7eb;
        }
        .nav__logo {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -.3px;
            color: #111827;
            flex-shrink: 0;
        }
        .nav__links { display: flex; gap: 1.75rem; flex: 1; }
        .nav__link {
            font-size: 13px;
            font-weight: 500;
            height: 60px;
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #6b7280;
            transition: color .15s;
        }
        .nav__link:hover,
        .nav__link--active { color: var(--primary); }
        .nav__actions { display: flex; align-items: center; gap: 1.1rem; flex-shrink: 0; }
        .nav__action-icon { width: 19px; height: 19px; color: #374151; cursor: pointer; }

        /* FOOTER */
        .footer {
            padding: 2.5rem 2rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            background: var(--bg-surface);
        }
        .footer__top {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 2rem;
            padding-bottom: 2rem;
        }
        .footer__brand-name { font-size: 15px; font-weight: 700; margin-bottom: .5rem; }
        .footer__brand-desc { font-size: 12px; color: #6b7280; line-height: 1.6; max-width: 320px; }
        .footer__col-title { font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #9ca3af; margin-bottom: .85rem; }
        .footer__col-links { display: flex; flex-direction: column; gap: .6rem; }
        .footer__col-links a,
        .footer__col-links span { font-size: 12px; color: #6b7280; }
        .footer__col-links a:hover { color: #111827; }
        .footer__bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.25rem;
            border-top: 1px solid #f3f4f6;
        }
        .footer__brand { font-size: 11px; color: #9ca3af; }
        .footer__links { display: flex; gap: 1.25rem; }
        .footer__link { font-size: 11px; color: #9ca3af; }
        .footer__link:hover { color: #111827; }

        @media (max-width: 700px) {
            .footer__top { grid-template-columns: 1fr; gap: 1.5rem; }
        }

        /* TOP ANNOUNCEMENT BAR (whatsapp + ticker items) */
        .topbar {
            background: var(--primary);
            color: #fff;
            padding: 7px 0;
            overflow: hidden;
            white-space: nowrap;
        }
        .topbar__inner {
            display: inline-flex;
            animation: ticker 22s linear infinite;
        }
        .topbar__item {
            font-size: 12px;
            font-weight: 500;
            padding: 0 2rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .topbar__item svg { width: 13px; height: 13px; flex-shrink: 0; }
        .topbar__sep { opacity: .5; }
        @keyframes ticker {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }

        /* WPP BANNER */
        .wpp-banner {
            margin: 0 2rem 2.5rem;
            border-radius: 12px;
            background: #f9fafb;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .wpp-banner__title { font-size: 14px; font-weight: 500; margin-bottom: 3px; }
        .wpp-banner__sub { font-size: 12px; color: #6b7280; }
        .wpp-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 10px 20px;
            background: #25d366;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            white-space: nowrap;
            font-family: var(--font);
        }
        .wpp-btn svg { width: 18px; height: 18px; flex-shrink: 0; }

        /* PRODUCT CARD */
        .pcard {
            cursor: pointer;
            position: relative;
            background: var(--bg-surface);
            border: 1px solid #f0f1f3;
            border-radius: 12px;
            padding: .75rem;
            transition: box-shadow .2s, border-color .2s;
        }
        .pcard:hover { border-color: #e5e7eb; box-shadow: 0 4px 14px rgba(17,24,39,.06); }
        .pcard__img {
            position: relative;
            border-radius: 8px;
            background: #f9fafb;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            margin-bottom: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pcard__img img { width: 100%; height: 100%; object-fit: cover; }
        .pcard__img-placeholder {
            width: 48px;
            height: 48px;
            opacity: .25;
        }
        .pcard__cat {
            font-size: 10px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 3px;
        }
        .pcard__name { font-size: 13px; font-weight: 500; margin-bottom: 6px; line-height: 1.35; }
        .pcard__price { font-size: 14px; font-weight: 700; color: #111827; }
        .pcard__badge-new {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 3px 8px;
            background: var(--primary);
            color: #fff;
            border-radius: 4px;
            z-index: 2;
        }

        /* SECTION */
        .section { padding: 2.5rem 2rem; }
        .section__head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 1.75rem;
        }
        .section__title {
            font-size: 20px;
            font-weight: 700;
        }
        .section__link { font-size: 12px; color: #9ca3af; }
        .section__link:hover { color: #111827; }

        .pgrid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
        }

        @media (max-width: 900px) {
            .pgrid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 500px) {
            .pgrid { grid-template-columns: 1fr; }
            .nav { padding: 0 1rem; }
            .nav__links { display: none; }
            .section { padding: 1.5rem 1rem; }
            .wpp-banner { margin: 0 1rem 1.5rem; flex-direction: column; align-items: flex-start; }
        }
    </style>

    @stack('styles')
</head>
<body>

@php $wpp = $theme->storeConfig('whatsapp'); @endphp
@if($wpp || $tickerTexts->isNotEmpty())
<div class="topbar">
    <div class="topbar__inner">
        @foreach(range(0, 1) as $_)
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
        @endforeach
    </div>
</div>
@endif

<nav class="nav">
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
    <div class="nav__actions">
        <svg class="nav__action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    </div>
</nav>

@yield('content')

<footer class="footer">
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
</footer>

<script>
@stack('scripts')
</script>

</body>
</html>
