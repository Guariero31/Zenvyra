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
            --primary: {{ $theme->cor_primaria_light ?? '#1a56db' }};
            --font: system-ui, -apple-system, 'Segoe UI', sans-serif;
            --bg-page: #f2f4f7;
            --bg-surface: #ffffff;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font);
            color: #111;
            background: var(--bg-page);
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }

        img { display: block; max-width: 100%; }

        /* NAV */
        .nav {
            position: absolute;
            top: 0; left: 0; right: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            height: 56px;
        }
        .nav--solid {
            position: sticky;
            top: 0;
            background: var(--bg-surface);
            border-bottom: 1px solid #e5e7eb;
        }
        .nav__logo {
            font-size: 17px;
            font-weight: 500;
            letter-spacing: -.4px;
            color: #fff;
        }
        .nav--solid .nav__logo { color: #111; }
        .nav__links { display: flex; }
        .nav__link {
            font-size: 11px;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: 0 18px;
            height: 56px;
            display: flex;
            align-items: center;
            cursor: pointer;
            color: rgba(255,255,255,.65);
            transition: color .15s;
        }
        .nav__link:hover,
        .nav__link--active { color: #fff; font-weight: 500; }
        .nav--solid .nav__link { color: #6b7280; }
        .nav--solid .nav__link:hover,
        .nav--solid .nav__link--active { color: #111; }

        /* FOOTER */
        .footer {
            padding: 1.25rem 2rem;
            border-top: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .footer__brand { font-size: 11px; color: #9ca3af; }
        .footer__links { display: flex; gap: 1.5rem; }
        .footer__link {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
        }
        .footer__link:hover { color: #111; }

        /* TICKER */
        .ticker {
            background: var(--bg-surface);
            padding: 10px 0;
            overflow: hidden;
            white-space: nowrap;
            border-bottom: 1px solid #f3f4f6;
        }
        .ticker__inner {
            display: inline-flex;
            animation: ticker 22s linear infinite;
        }
        .ticker__item {
            font-size: 11px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #9ca3af;
            padding: 0 2.5rem;
        }
        .ticker__sep { color: #e5e7eb; }
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
        .pcard { cursor: pointer; }
        .pcard__img {
            border-radius: 12px;
            background: #f9fafb;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            margin-bottom: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }
        .pcard:hover .pcard__img { background: #f3f4f6; }
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
        .pcard__name { font-size: 13px; font-weight: 500; margin-bottom: 6px; }
        .pcard__price { font-size: 13px; }
        .pcard__old {
            font-size: 11px;
            color: #9ca3af;
            text-decoration: line-through;
            margin-right: 5px;
        }
        .pcard__badge {
            display: inline-block;
            font-size: 9px;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 2px 6px;
            background: #111;
            color: #fff;
            margin-left: 5px;
            vertical-align: middle;
            border-radius: 2px;
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
            font-size: 12px;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .section__link { font-size: 12px; color: #9ca3af; }
        .section__link:hover { color: #111; }

        .pgrid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 900px) {
            .pgrid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 500px) {
            .pgrid { grid-template-columns: 1fr; }
            .nav { padding: 0 1rem; }
            .section { padding: 1.5rem 1rem; }
            .wpp-banner { margin: 0 1rem 1.5rem; flex-direction: column; align-items: flex-start; }
        }
    </style>

    @stack('styles')
</head>
<body>

<nav class="nav {{ $navClass ?? '' }}">
    <a href="{{ route('store.home', $tenant->te_slug) }}" class="nav__logo">
        @if ($theme->logo_path)
            <img src="{{ Storage::url($theme->logo_path) }}" alt="{{ $theme->nome_loja ?? $tenant->te_name }}" style="height:32px;width:auto;">
        @else
            {{ $theme->nome_loja ?? $tenant->te_name }}
        @endif
    </a>
    <div class="nav__links">
        <a href="{{ route('store.home', $tenant->te_slug) }}"
           class="nav__link {{ request()->routeIs('store.home') ? 'nav__link--active' : '' }}">Home</a>
        <a href="{{ route('store.catalog', $tenant->te_slug) }}"
           class="nav__link {{ request()->routeIs('store.catalog') ? 'nav__link--active' : '' }}">Catálogo</a>
        <a href="{{ route('store.contact', $tenant->te_slug) }}"
           class="nav__link {{ request()->routeIs('store.contact') ? 'nav__link--active' : '' }}">Contato</a>
    </div>
</nav>

@yield('content')

<script>
@stack('scripts')
</script>

</body>
</html>
