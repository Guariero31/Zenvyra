@php
    $tenant = \Filament\Facades\Filament::getTenant();
@endphp

    <style>
        /* Neutraliza o padding padrão do Filament para esta página ocupar a tela toda */
        .fi-simple-main { padding: 0 !important; max-width: 100% !important; }
        .fi-simple-layout { background: #0f1117 !important; }

        :root {
            --pdv-bg:        #0f1117;
            --pdv-surface:   #1a1d27;
            --pdv-surface2:  #22263a;
            --pdv-border:    #2e3347;
            --pdv-accent:    #3b82f6;
            --pdv-green:     #22c55e;
            --pdv-red:       #ef4444;
            --pdv-yellow:    #f59e0b;
            --pdv-text:      #f1f5f9;
            --pdv-text2:     #94a3b8;
            --pdv-text3:     #64748b;
        }

        .pdv-shell * { box-sizing: border-box; }
        .pdv-shell {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--pdv-bg);
            color: var(--pdv-text);
            height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 14px;
        }

        .pdv-topbar {
            height: 52px;
            background: var(--pdv-surface);
            border-bottom: 1px solid var(--pdv-border);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            gap: 1.25rem;
            flex-shrink: 0;
        }
        .pdv-brand { font-size: 15px; font-weight: 700; color: var(--pdv-accent); display: flex; align-items: center; gap: 8px; }
        .pdv-brand span { color: var(--pdv-text2); font-weight: 400; font-size: 12px; }
        .pdv-sep { width: 1px; height: 24px; background: var(--pdv-border); }
        .pdv-info { color: var(--pdv-text2); font-size: 12px; }
        .pdv-info strong { color: var(--pdv-text); }
        .pdv-spacer { flex: 1; }
        .pdv-status {
            display: flex; align-items: center; gap: 6px;
            background: #14532d; border: 1px solid #166534; border-radius: 6px;
            padding: 4px 10px; font-size: 11px; color: var(--pdv-green); font-weight: 600;
        }
        .pdv-exit {
            display: flex; align-items: center; gap: 6px;
            background: var(--pdv-surface2); border: 1px solid var(--pdv-border);
            border-radius: 6px; padding: 6px 12px; font-size: 12px; color: var(--pdv-text2);
            text-decoration: none; transition: all .15s;
        }
        .pdv-exit:hover { border-color: var(--pdv-text2); color: var(--pdv-text); }

        .pdv-wrap { flex: 1; display: flex; overflow: hidden; }

        .pdv-left { flex: 1; display: flex; flex-direction: column; border-right: 1px solid var(--pdv-border); }

        .pdv-search-bar {
            padding: .75rem 1rem; background: var(--pdv-surface); border-bottom: 1px solid var(--pdv-border);
            display: flex; gap: .75rem; align-items: center; flex-shrink: 0;
        }
        .pdv-search-wrap { flex: 1; position: relative; }
        .pdv-search-input {
            width: 100%; height: 44px; background: var(--pdv-bg); border: 2px solid var(--pdv-accent);
            border-radius: 8px; padding: 0 1rem 0 2.75rem; color: var(--pdv-text); font-size: 15px; outline: none;
        }
        .pdv-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--pdv-text3); }

        .pdv-tabs { display: flex; gap: 4px; padding: .6rem 1rem 0; background: var(--pdv-surface); border-bottom: 1px solid var(--pdv-border); }
        .pdv-tab {
            padding: 9px 16px; background: transparent; border: none; border-bottom: 2px solid transparent;
            color: var(--pdv-text3); font-size: 13px; font-weight: 600; border-radius: 6px 6px 0 0;
        }
        .pdv-tab.is-active { color: var(--pdv-accent); border-bottom-color: var(--pdv-accent); background: #1e3a5f; }

        .pdv-empty {
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 1rem; color: var(--pdv-text3);
        }
        .pdv-empty-icon { font-size: 48px; opacity: .3; }
        .pdv-empty p { font-size: 13px; text-align: center; line-height: 1.6; }

        .pdv-bottombar {
            height: 40px; background: var(--pdv-surface); border-top: 1px solid var(--pdv-border);
            padding: 0 1rem; display: flex; align-items: center; gap: 1.5rem; font-size: 11px; color: var(--pdv-text3); flex-shrink: 0;
        }

        .pdv-right { width: 320px; display: flex; flex-direction: column; background: var(--pdv-surface); flex-shrink: 0; }
        .pdv-section-label { font-size: 10px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--pdv-text3); margin-bottom: .5rem; }
        .pdv-customer-section { padding: .75rem 1rem; border-bottom: 1px solid var(--pdv-border); }
        .pdv-customer-field {
            display: flex; align-items: center; gap: 8px; background: var(--pdv-bg); border: 1px solid var(--pdv-border);
            border-radius: 7px; padding: 8px 10px;
        }
        .pdv-avatar { width: 30px; height: 30px; border-radius: 50%; background: #1e293b; display: flex; align-items: center; justify-content: center; font-size: 13px; color: var(--pdv-accent); font-weight: 700; }
        .pdv-customer-name { font-size: 13px; font-weight: 500; }
        .pdv-customer-sub { font-size: 11px; color: var(--pdv-text3); }

        .pdv-summary { flex: 1; padding: 1rem; display: flex; flex-direction: column; gap: .5rem; }
        .pdv-summary-row { display: flex; justify-content: space-between; padding: .35rem 0; }
        .pdv-summary-row span:first-child { font-size: 12px; color: var(--pdv-text2); }
        .pdv-summary-row span:last-child { font-size: 13px; font-weight: 500; }
        .pdv-divider { height: 1px; background: var(--pdv-border); margin: .25rem 0; }

        .pdv-total-block { background: var(--pdv-bg); border: 1px solid var(--pdv-border); border-radius: 10px; padding: 1rem; margin-top: auto; }
        .pdv-total-label { font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: var(--pdv-text3); margin-bottom: 4px; }
        .pdv-total-value { font-size: 38px; font-weight: 800; letter-spacing: -1px; color: var(--pdv-green); line-height: 1; }

        .pdv-actions { padding: .75rem 1rem; border-top: 1px solid var(--pdv-border); display: flex; flex-direction: column; gap: .5rem; }
        .pdv-btn { display: flex; align-items: center; justify-content: center; gap: 8px; height: 44px; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; width: 100%; }
        .pdv-btn-primary { background: var(--pdv-green); color: #fff; font-size: 15px; height: 52px; opacity: .5; }
        .pdv-btn-row { display: flex; gap: .5rem; }
        .pdv-btn-row .pdv-btn { flex: 1; background: var(--pdv-surface2); color: var(--pdv-text2); border: 1px solid var(--pdv-border); }

        .pdv-badge-wip {
            background: #451a03; border: 1px solid #78350f; color: #fbbf24;
            font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px; letter-spacing: .04em;
        }
    </style>

    <div class="pdv-shell">

        <div class="pdv-topbar">
            <div class="pdv-brand">
                Zenvyra <span>PDV</span>
            </div>
            <span class="pdv-badge-wip">EM CONSTRUÇÃO</span>
            <div class="pdv-sep"></div>
            <div class="pdv-info">Operador: <strong>{{ auth()->user()?->name }}</strong></div>
            <div class="pdv-info">Loja: <strong>{{ $tenant?->te_name }}</strong></div>
            <div class="pdv-spacer"></div>
            <div class="pdv-status">CAIXA ABERTO</div>
            <div class="pdv-sep"></div>
            <a href="{{ \App\Filament\App\Pages\Dashboard::getUrl(panel: 'app') }}" class="pdv-exit">
                ← Sair do PDV
            </a>
        </div>

        <div class="pdv-wrap">
            <div class="pdv-left">
                <div class="pdv-search-bar">
                    <div class="pdv-search-wrap">
                        <svg class="pdv-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text" class="pdv-search-input" placeholder="Pesquisar produto por nome, SKU ou código de barras..." disabled>
                    </div>
                </div>

                <div class="pdv-tabs">
                    <button type="button" class="pdv-tab is-active">🛒 Venda</button>
                    <button type="button" class="pdv-tab">📦 Catálogo</button>
                </div>

                <div class="pdv-empty">
                    <div class="pdv-empty-icon">🛠️</div>
                    <p>
                        Esqueleto da tela de PDV.<br>
                        A busca, o catálogo e o carrinho serão implementados na próxima etapa.
                    </p>
                </div>

                <div class="pdv-bottombar">
                    <span>Venda: <strong style="color:var(--pdv-accent)">#001</strong></span>
                </div>
            </div>

            <div class="pdv-right">
                <div class="pdv-customer-section">
                    <div class="pdv-section-label">Cliente</div>
                    <div class="pdv-customer-field">
                        <div class="pdv-avatar">?</div>
                        <div>
                            <div class="pdv-customer-name">Consumidor não identificado</div>
                            <div class="pdv-customer-sub">Em breve: seleção de cliente</div>
                        </div>
                    </div>
                </div>

                <div class="pdv-summary">
                    <div class="pdv-section-label">Resumo da Venda</div>
                    <div class="pdv-summary-row"><span>Itens</span><span>0</span></div>
                    <div class="pdv-summary-row"><span>Subtotal</span><span>R$ 0,00</span></div>
                    <div class="pdv-summary-row"><span>Desconto</span><span>— R$ 0,00</span></div>
                    <div class="pdv-divider"></div>

                    <div class="pdv-total-block">
                        <div class="pdv-total-label">Total da Venda</div>
                        <div class="pdv-total-value">R$ 0,00</div>
                    </div>
                </div>

                <div class="pdv-actions">
                    <button type="button" class="pdv-btn pdv-btn-primary" disabled>✓ Finalizar Venda</button>
                    <div class="pdv-btn-row">
                        <button type="button" class="pdv-btn">⏸ Suspender</button>
                        <button type="button" class="pdv-btn">✕ Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
