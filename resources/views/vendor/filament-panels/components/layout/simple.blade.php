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

    <style>
        /* ── SPLIT LOGIN ───────────────────────────────────────────── */
        .zv-login-wrap {
            display: flex;
            min-height: 100dvh;
        }

        /* LEFT PANEL */
        .zv-login-left {
            flex: 0 0 58%;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 3rem;
            overflow: hidden;
            background: linear-gradient(145deg, #0f1a2e 0%, #1a3a5c 45%, #1e4976 100%);
        }

        .zv-login-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 70% 60% at 30% 40%, rgba(34,197,94,.18) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 80% 80%, rgba(16,185,129,.12) 0%, transparent 60%);
            pointer-events: none;
        }

        /* decorative circles */
        .zv-login-left::after {
            content: '';
            position: absolute;
            top: -120px;
            right: -120px;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,.06);
        }

        .zv-deco-ring {
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,.05);
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
            letter-spacing: -.05em;
            margin-bottom: .5rem;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .zv-brand-logo span { color: #22c55e; }

        .zv-brand-tagline {
            font-size: .95rem;
            color: rgba(255,255,255,.55);
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 36ch;
            line-height: 1.55;
        }

        .zv-brand-badges {
            display: flex;
            gap: .625rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .zv-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: rgba(255,255,255,.65);
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.1);
            padding: .3rem .75rem;
            border-radius: 99px;
            font-family: system-ui, -apple-system, sans-serif;
        }

        /* RIGHT PANEL */
        .zv-login-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            background: #ffffff;
            overflow-y: auto;
        }

        .zv-login-right-inner {
            width: 100%;
            max-width: 420px;
        }

        /* override Filament simple main centering */
        .fi-simple-main-ctn { display: contents !important; }
        .fi-simple-main { max-width: 100% !important; width: 100% !important; }
        .fi-simple-layout { display: contents !important; }

        /* topbar (logged-in state) */
        .fi-simple-layout-header {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
        }

        @media (max-width: 768px) {
            .zv-login-left  { display: none; }
            .zv-login-right { min-height: 100dvh; }
        }
    </style>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

    @if (($hasTopbar ?? true) && filament()->auth()->check())
        <a href="#fi-main-content" class="fi-skip-link fi-sr-only">
            {{ __('filament-panels::layout.skip_to_content.label') }}
        </a>
        <div class="fi-simple-layout-header">
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

    <div class="zv-login-wrap">

        {{-- LEFT: brand panel --}}
        <div class="zv-login-left">
            <span class="zv-deco-ring"></span>
            <div class="zv-login-brand">
                <div class="zv-brand-logo">Zen<span>vyra</span></div>
                <p class="zv-brand-tagline">
                    Gerencie sua loja com mais clareza. Catálogo, estoque e pedidos em um painel simples.
                </p>
                <div class="zv-brand-badges">
                    <span class="zv-badge">🔒 Acesso seguro</span>
                    <span class="zv-badge">🚀 Multi-loja</span>
                    <span class="zv-badge">📦 Estoque em tempo real</span>
                </div>
            </div>
        </div>

        {{-- RIGHT: form --}}
        <div class="zv-login-right">
            <div class="zv-login-right-inner">
                <main id="fi-main-content" tabindex="-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
</x-filament-panels::layout.base>
