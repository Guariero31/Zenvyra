@php
    use Filament\Support\Enums\Width;

    $livewire ??= null;
    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= (filament()->getSimplePageMaxContentWidth() ?? Width::Large);

    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after'      => null,
        'heading'    => null,
        'subheading' => null,
    ])

    <script>
        // Filament toggles `dark` on <html> based on localStorage/system
        // preference — and re-applies it via its own Alpine-driven theme
        // watcher after this script runs once, so a single classList.remove()
        // here isn't enough. A MutationObserver keeps stripping it for as
        // long as this page is open, so the login screen never goes dark.
        (function () {
            var root = document.documentElement;

            function forceLight() {
                root.classList.remove('dark');
                root.style.colorScheme = 'light';
            }

            forceLight();
            new MutationObserver(forceLight).observe(root, {
                attributes: true,
                attributeFilter: ['class'],
            });
        })();
    </script>

    <style>
        /* ── SPLIT LOGIN ───────────────────────────────────────────── */
        html, body,
        html.dark, html.dark body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #0a0f1e;
            color-scheme: light;
        }

        /* Outer shell = full screen */
        .zv-login-shell {
            min-height: 100dvh;
            display: flex;
        }

        /* Container ocupa a tela toda */
        .zv-login-wrap {
            display: flex;
            flex: 1;
            border-left: 2px solid rgba(124,58,237,.6);
            border-right: 2px solid rgba(124,58,237,.6);
            box-shadow: inset 0 0 0 1px rgba(124,58,237,.15);
        }

        /* ── LEFT PANEL ── */
        .zv-login-left {
            flex: 0 0 58%;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 2.5rem;
            overflow: hidden;
            background: linear-gradient(160deg, #0d1b35 0%, #0f2040 40%, #102545 100%);
        }

        /* Subtle dot grid */
        .zv-login-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        /* Green glow top-right */
        .zv-login-left::after {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(34,197,94,.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .zv-login-brand {
            position: relative;
            z-index: 1;
        }

        .zv-brand-logo {
            font-size: 2rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -.04em;
            margin-bottom: .5rem;
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1;
        }

        .zv-brand-logo span { color: #22c55e; }

        .zv-brand-tagline {
            font-size: .9rem;
            color: rgba(255,255,255,.5);
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 34ch;
            line-height: 1.6;
            margin: 0;
        }

        .zv-brand-badges {
            display: flex;
            gap: .5rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .zv-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: rgba(255,255,255,.7);
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            padding: .35rem .9rem;
            border-radius: 99px;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .zv-badge-icon {
            font-size: .85rem;
        }

        /* ── RIGHT PANEL ── */
        .zv-login-right,
        html.dark .zv-login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            background: #ffffff !important;
            overflow-y: auto;
            color-scheme: light;
            color: #000000;
        }

        .zv-login-right-inner {
            width: 100%;
            max-width: 400px;
        }

        /* Reset Filament centering wrappers */
        .zv-login-right .fi-simple-main-ctn {
            display: block !important;
            min-height: unset !important;
            padding: 0 !important;
        }

        .zv-login-right .fi-simple-main {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Force black text throughout right panel, in light AND dark mode */
        .zv-login-right,
        .zv-login-right *:not(.fi-fo-field-wrp-error-message):not([class*="fi-color-danger"]):not([data-validation-error]):not(button[type="submit"]):not(button[type="submit"] *),
        .zv-login-right label,
        .zv-login-right input,
        .zv-login-right a,
        .zv-login-right p:not(.fi-fo-field-wrp-error-message),
        .zv-login-right span:not([class*="fi-color-danger"]):not(button[type="submit"] *),
        .zv-login-right h1,
        .zv-login-right h2,
        .zv-login-right h3,
        .zv-login-right [class*="fi-"]:not(.fi-fo-field-wrp-error-message):not([class*="fi-color-danger"]):not(button[type="submit"]):not(button[type="submit"] *),
        html.dark .zv-login-right,
        html.dark .zv-login-right *:not(.fi-fo-field-wrp-error-message):not([class*="fi-color-danger"]):not([data-validation-error]):not(button[type="submit"]):not(button[type="submit"] *),
        html.dark .zv-login-right label,
        html.dark .zv-login-right input,
        html.dark .zv-login-right a,
        html.dark .zv-login-right p:not(.fi-fo-field-wrp-error-message),
        html.dark .zv-login-right span:not([class*="fi-color-danger"]):not(button[type="submit"] *),
        html.dark .zv-login-right h1,
        html.dark .zv-login-right h2,
        html.dark .zv-login-right h3,
        html.dark .zv-login-right [class*="fi-"]:not(.fi-fo-field-wrp-error-message):not([class*="fi-color-danger"]):not(button[type="submit"]):not(button[type="submit"] *) {
            color: #000000 !important;
        }

        .zv-login-right input::placeholder { color: #9ca3af !important; }
        .zv-login-right a:hover { color: #4f46e5 !important; }

        /* Error messages */
        .zv-login-right .fi-fo-field-wrp-error-message,
        .zv-login-right [data-validation-error],
        .zv-login-right p.fi-fo-field-wrp-error-message {
            color: #dc2626 !important;
            font-weight: 500 !important;
        }

        .zv-login-right .fi-input-wrp[data-invalid] input,
        .zv-login-right input[aria-invalid="true"] {
            border-color: #dc2626 !important;
        }

        /* Login button — bold white text, fixed regardless of dark mode */
        .zv-login-right button[type="submit"],
        .zv-login-right .fi-btn-label,
        .zv-login-right button[type="submit"] *,
        html.dark .zv-login-right button[type="submit"],
        html.dark .zv-login-right .fi-btn-label,
        html.dark .zv-login-right button[type="submit"] * {
            font-weight: 700 !important;
            color: #ffffff !important;
        }

        /* topbar (logged-in state) */
        .zv-topbar {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
        }

        @media (max-width: 768px) {
            .zv-login-wrap  { border: none; box-shadow: none; }
            .zv-login-left  { display: none; }
            .zv-login-right { min-height: 100dvh; }
        }
    </style>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

    @if (($hasTopbar ?? true) && filament()->auth()->check())
        <a href="#fi-main-content" class="fi-skip-link fi-sr-only">
            {{ __('filament-panels::layout.skip_to_content.label') }}
        </a>
        <div class="zv-topbar">
            @if (filament()->hasDatabaseNotifications())
                @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                    'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                    'position' => \Filament\Enums\DatabaseNotificationsPosition::Topbar,
                ])
            @endif
            @if (filament()->hasUserMenu())
                @livewire(Filament\Livewire\SimpleUserMenu::class)
            @endif
        </div>
    @endif

    <div class="zv-login-shell">
        <div class="zv-login-wrap">

            {{-- LEFT: brand panel --}}
            <div class="zv-login-left">
                <div class="zv-login-brand">
                    <div class="zv-brand-logo">Zen<span>vyra</span></div>
                    <p class="zv-brand-tagline">
                        Gerencie sua loja com mais clareza.<br>
                        Catálogo, estoque e pedidos em um painel simples.
                    </p>
                    <div class="zv-brand-badges">
                        <span class="zv-badge"><span class="zv-badge-icon">🔒</span> Acesso Seguro</span>
                        <span class="zv-badge"><span class="zv-badge-icon">🏪</span> Multi-Loja</span>
                        <span class="zv-badge"><span class="zv-badge-icon">📦</span> Estoque em Tempo Real</span>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Filament form --}}
            <div class="zv-login-right">
                <div class="zv-login-right-inner">
                    <main id="fi-main-content" tabindex="-1">
                        {{ $slot }}
                    </main>
                </div>
            </div>

        </div>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
</x-filament-panels::layout.base>
