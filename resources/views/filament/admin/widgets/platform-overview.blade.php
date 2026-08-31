<div>
    <style>
        .zv-grid { display: grid; gap: 1rem; grid-template-columns: repeat(1, minmax(0, 1fr)); }
        @media (min-width: 640px) { .zv-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1280px) { .zv-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .zv-card { position: relative; border-radius: 0.75rem; padding: 1.25rem 1.5rem; background: #fff; border: 1px solid rgba(17, 24, 39, 0.06); box-shadow: 0 1px 2px rgba(17, 24, 39, 0.04); }
        .dark .zv-card { background: #1f2937; border-color: rgba(255, 255, 255, 0.06); box-shadow: none; }

        .zv-label { font-size: 0.8rem; font-weight: 500; color: #6b7280; }
        .dark .zv-label { color: #9ca3af; }

        .zv-value { margin-top: 0.5rem; font-size: 1.875rem; font-weight: 700; line-height: 1.15; color: #111827; }
        .dark .zv-value { color: #f9fafb; }

        .zv-sub { margin-top: 0.4rem; font-size: 0.8rem; font-weight: 600; color: #10b981; }

        .zv-icon { position: absolute; top: 1.25rem; right: 1.5rem; display: flex; align-items: center; justify-content: center; height: 2.5rem; width: 2.5rem; border-radius: 0.625rem; }
        .zv-icon svg { height: 1.25rem; width: 1.25rem; }

        .zv-blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
        .zv-green { background: rgba(16, 185, 129, 0.12); color: #10b981; }
        .zv-amber { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
        .zv-red { background: rgba(244, 63, 94, 0.12); color: #f43f5e; }
    </style>

    <div class="zv-grid">
        <div class="zv-card">
            <div class="zv-label">Total de Lojas</div>
            <div class="zv-value">{{ $totalStores }}</div>
            <div class="zv-icon zv-blue">@svg('heroicon-o-building-storefront')</div>
        </div>

        <div class="zv-card">
            <div class="zv-label">Lojas Ativas</div>
            <div class="zv-value">{{ $activeStores }}</div>
            <div class="zv-sub">{{ $activePercentage }}% operacionais</div>
            <div class="zv-icon zv-green">@svg('heroicon-o-building-storefront')</div>
        </div>

        <div class="zv-card">
            <div class="zv-label">Faturamento Mensal</div>
            <div class="zv-value">R$ {{ number_format($monthlyRevenue, 0, ',', '.') }}</div>
            <div class="zv-icon zv-amber">@svg('heroicon-o-currency-dollar')</div>
        </div>

        <div class="zv-card">
            <div class="zv-label">Vencimentos Próximos</div>
            <div class="zv-value">{{ $upcoming }}</div>
            <div class="zv-icon zv-red">@svg('heroicon-o-exclamation-triangle')</div>
        </div>
    </div>
</div>
