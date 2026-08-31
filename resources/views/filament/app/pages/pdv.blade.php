@php
    $tenant    = \Filament\Facades\Filament::getTenant();
    $operator  = auth()->user();
    $dashUrl   = \App\Filament\App\Pages\Dashboard::getUrl(panel: 'app');
@endphp

{{-- The Livewire component view must have a single root element — the
     <style> block used to be a sibling of the .pdv div below, which made
     Livewire attach its wire:id to the <style> tag instead, leaving all
     of the actual markup (and every wire:click/wire:model in it) outside
     the component's tracked boundary. Nesting <style> as the first child
     fixes that. --}}
<div class="pdv" wire:poll.60s="">

<style>
    /* ── RESET ──────────────────────────────────────────────────────── */
    html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }

    *, *::before, *::after { box-sizing: border-box; }

    :root {
        --bg:       #0f1117;
        --surface:  #1a1d27;
        --surface2: #22263a;
        --border:   #2a2f45;
        --accent:   #3b82f6;
        --green:    #22c55e;
        --red:      #ef4444;
        --yellow:   #f59e0b;
        --text:     #f1f5f9;
        --text2:    #94a3b8;
        --text3:    #64748b;
    }

    /* ── SHELL ──────────────────────────────────────────────────────── */
    .pdv {
        display: flex;
        flex-direction: column;
        height: 100vh;
        background: var(--bg);
        color: var(--text);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        font-size: 14px;
    }

    /* ── TOPBAR ─────────────────────────────────────────────────────── */
    .pdv-top {
        flex-shrink: 0;
        height: 52px;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        padding: 0 1.25rem;
        gap: 1rem;
    }
    .pdv-brand {
        font-size: 16px;
        font-weight: 800;
        color: var(--accent);
        letter-spacing: -.02em;
        white-space: nowrap;
    }
    .pdv-brand small {
        font-weight: 400;
        font-size: 11px;
        color: var(--text3);
        letter-spacing: .05em;
        margin-left: 4px;
    }
    .pdv-vsep { width: 1px; height: 24px; background: var(--border); flex-shrink: 0; }
    .pdv-meta { font-size: 12px; color: var(--text3); white-space: nowrap; }
    .pdv-meta strong { color: var(--text2); font-weight: 500; }
    .pdv-spacer { flex: 1; }
    .pdv-clock { font-size: 14px; font-weight: 700; color: var(--text2); font-variant-numeric: tabular-nums; }
    .pdv-pill-green {
        display: flex; align-items: center; gap: 5px;
        background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.25);
        border-radius: 99px; padding: 3px 10px;
        font-size: 11px; font-weight: 700; letter-spacing: .04em; color: var(--green);
    }
    .pdv-pill-green::before { content:''; width: 6px; height: 6px; background: var(--green); border-radius: 50%; }
    .pdv-exit-btn {
        display: flex; align-items: center; gap: 6px;
        background: transparent; border: 1px solid var(--border);
        border-radius: 7px; padding: 6px 12px;
        font-size: 12px; color: var(--text3);
        text-decoration: none; transition: all .15s; white-space: nowrap;
    }
    .pdv-exit-btn:hover { border-color: var(--text2); color: var(--text); }

    /* ── BODY ───────────────────────────────────────────────────────── */
    .pdv-body { flex: 1; display: flex; overflow: hidden; }

    /* ── LEFT: CATALOG ──────────────────────────────────────────────── */
    .pdv-catalog {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-right: 1px solid var(--border);
    }

    /* Search bar */
    .pdv-searchbar {
        flex-shrink: 0;
        padding: .65rem 1rem;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        display: flex;
        gap: .65rem;
        align-items: center;
    }
    .pdv-search-wrap { flex: 1; position: relative; }
    .pdv-search-input {
        width: 100%; height: 40px;
        background: var(--bg); border: 1.5px solid var(--border);
        border-radius: 8px; padding: 0 1rem 0 2.6rem;
        color: var(--text); font-size: 14px; outline: none;
        transition: border-color .15s;
    }
    .pdv-search-input:focus { border-color: var(--accent); }
    .pdv-search-input::placeholder { color: var(--text3); }
    .pdv-search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text3); pointer-events: none; }

    /* Category tabs */
    .pdv-cattabs {
        flex-shrink: 0;
        display: flex;
        gap: 2px;
        padding: .5rem 1rem 0;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        overflow-x: auto;
        scrollbar-width: none;
    }
    .pdv-cattabs::-webkit-scrollbar { display: none; }
    .pdv-catbtn {
        flex-shrink: 0;
        padding: 7px 14px;
        background: transparent; border: none;
        border-bottom: 2px solid transparent;
        color: var(--text3); font-size: 12px; font-weight: 600;
        border-radius: 6px 6px 0 0; cursor: pointer;
        transition: all .15s; white-space: nowrap;
    }
    .pdv-catbtn:hover { color: var(--text2); }
    .pdv-catbtn.active { color: var(--accent); border-bottom-color: var(--accent); background: rgba(59,130,246,.08); }

    /* Product grid */
    .pdv-grid-wrap { flex: 1; overflow-y: auto; padding: .85rem 1rem; }
    .pdv-grid-wrap::-webkit-scrollbar { width: 5px; }
    .pdv-grid-wrap::-webkit-scrollbar-track { background: transparent; }
    .pdv-grid-wrap::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

    .pdv-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
        gap: .75rem;
    }

    .pdv-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
        transition: all .15s;
        position: relative;
    }
    .pdv-card:hover { border-color: var(--accent); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(59,130,246,.12); }
    .pdv-card:active { transform: translateY(0); }

    .pdv-card-img {
        width: 100%; aspect-ratio: 1 / 1;
        background: var(--surface2);
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
    }
    .pdv-card-img img { width: 100%; height: 100%; object-fit: cover; }
    .pdv-card-img-placeholder { font-size: 32px; opacity: .2; }

    .pdv-card-body { padding: .5rem .65rem .6rem; }
    .pdv-card-name {
        font-size: 12px; font-weight: 500; color: var(--text);
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; line-height: 1.35; margin-bottom: .3rem; min-height: 2.4em;
    }
    .pdv-card-price { font-size: 14px; font-weight: 700; color: var(--green); }
    .pdv-card-stock { font-size: 10px; color: var(--text3); margin-top: 2px; }
    .pdv-card-stock.low { color: var(--yellow); }
    .pdv-card-stock.zero { color: var(--red); }
    .pdv-card-overlay {
        position: absolute; inset: 0; background: rgba(15,17,23,.7);
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; color: var(--red); letter-spacing: .06em;
    }
    .pdv-card-var-badge {
        position: absolute; top: 6px; right: 6px;
        background: var(--accent); color: #fff;
        font-size: 9px; font-weight: 700; padding: 2px 6px; border-radius: 4px;
        letter-spacing: .04em;
    }

    /* Empty state */
    .pdv-empty-state {
        grid-column: 1 / -1;
        padding: 4rem 0;
        display: flex; flex-direction: column; align-items: center; gap: .75rem;
        color: var(--text3);
    }
    .pdv-empty-state-icon { font-size: 40px; opacity: .3; }
    .pdv-empty-state p { font-size: 13px; }

    /* ── RIGHT: CART ────────────────────────────────────────────────── */
    .pdv-cart {
        width: 340px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        background: var(--surface);
        overflow: hidden;
    }

    /* Customer */
    .pdv-customer-bar {
        flex-shrink: 0;
        padding: .65rem .9rem;
        border-bottom: 1px solid var(--border);
    }
    .pdv-label { font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text3); margin-bottom: .4rem; }

    .pdv-customer-selected {
        display: flex; align-items: center; gap: 8px;
        background: var(--bg); border: 1px solid var(--border);
        border-radius: 7px; padding: 7px 10px;
    }
    .pdv-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: rgba(59,130,246,.15); border: 1px solid rgba(59,130,246,.3);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; color: var(--accent); flex-shrink: 0;
    }
    .pdv-customer-info { flex: 1; min-width: 0; }
    .pdv-customer-name { font-size: 13px; font-weight: 500; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pdv-customer-phone { font-size: 11px; color: var(--text3); }
    .pdv-clear-btn {
        background: none; border: none; cursor: pointer;
        color: var(--text3); padding: 2px; border-radius: 4px;
        display: flex; align-items: center; transition: color .15s;
    }
    .pdv-clear-btn:hover { color: var(--red); }

    .pdv-customer-search-wrap { position: relative; }
    .pdv-customer-input {
        width: 100%; height: 36px;
        background: var(--bg); border: 1.5px solid var(--border);
        border-radius: 7px; padding: 0 2.2rem 0 .75rem;
        color: var(--text); font-size: 13px; outline: none;
        transition: border-color .15s;
    }
    .pdv-customer-input:focus { border-color: var(--accent); }
    .pdv-customer-input::placeholder { color: var(--text3); }
    .pdv-customer-search-icon { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: var(--text3); pointer-events: none; }

    .pdv-customer-dropdown {
        position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 30;
        background: var(--surface2); border: 1px solid var(--border);
        border-radius: 8px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,.4);
    }
    .pdv-customer-option {
        display: flex; align-items: center; gap: 8px;
        padding: 9px 12px; cursor: pointer; transition: background .1s;
    }
    .pdv-customer-option:hover { background: rgba(59,130,246,.1); }
    .pdv-customer-option-name { font-size: 13px; font-weight: 500; }
    .pdv-customer-option-phone { font-size: 11px; color: var(--text3); }

    /* Cart items */
    .pdv-items {
        flex: 1;
        overflow-y: auto;
        padding: .5rem .9rem;
    }
    .pdv-items::-webkit-scrollbar { width: 4px; }
    .pdv-items::-webkit-scrollbar-track { background: transparent; }
    .pdv-items::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

    .pdv-empty-cart {
        height: 100%; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: .75rem; color: var(--text3);
        padding: 2rem;
    }
    .pdv-empty-cart-icon { font-size: 38px; opacity: .25; }
    .pdv-empty-cart p { font-size: 12px; text-align: center; line-height: 1.6; }

    .pdv-item {
        display: flex; gap: .6rem; align-items: flex-start;
        padding: .55rem 0;
        border-bottom: 1px solid var(--border);
    }
    .pdv-item:last-child { border-bottom: none; }
    .pdv-item-info { flex: 1; min-width: 0; }
    .pdv-item-name { font-size: 12px; font-weight: 500; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pdv-item-var { font-size: 11px; color: var(--text3); }
    .pdv-item-price { font-size: 12px; color: var(--green); font-weight: 600; margin-top: 2px; }

    .pdv-qty {
        display: flex; align-items: center; gap: 4px; flex-shrink: 0;
    }
    .pdv-qty-btn {
        width: 22px; height: 22px; border-radius: 5px;
        background: var(--surface2); border: 1px solid var(--border);
        color: var(--text2); font-size: 14px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all .1s; padding: 0; line-height: 1;
    }
    .pdv-qty-btn:hover { background: var(--accent); border-color: var(--accent); color: #fff; }
    .pdv-qty-num { font-size: 13px; font-weight: 700; color: var(--text); min-width: 20px; text-align: center; }

    .pdv-remove-btn {
        background: none; border: none; cursor: pointer;
        color: var(--text3); padding: 2px; border-radius: 4px;
        display: flex; align-items: center; transition: color .1s; flex-shrink: 0;
    }
    .pdv-remove-btn:hover { color: var(--red); }

    /* Discount */
    .pdv-discount-row {
        flex-shrink: 0;
        padding: .55rem .9rem;
        border-top: 1px solid var(--border);
        display: flex; align-items: center; gap: .5rem;
    }
    .pdv-discount-label { font-size: 12px; color: var(--text3); white-space: nowrap; }
    .pdv-discount-input {
        width: 90px; height: 30px;
        background: var(--bg); border: 1px solid var(--border);
        border-radius: 6px; padding: 0 .5rem;
        color: var(--text); font-size: 13px; outline: none;
        transition: border-color .15s; text-align: right;
    }
    .pdv-discount-input:focus { border-color: var(--accent); }
    /* Hide the native number-input spinner; type="number" is kept only for
       the numeric keypad on touch devices. */
    .pdv-discount-input::-webkit-outer-spin-button,
    .pdv-discount-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .pdv-discount-input { -moz-appearance: textfield; }

    /* Summary */
    .pdv-summary {
        flex-shrink: 0;
        padding: .6rem .9rem;
        border-top: 1px solid var(--border);
    }
    .pdv-sum-row { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .2rem; }
    .pdv-sum-key { font-size: 12px; color: var(--text3); }
    .pdv-sum-val { font-size: 12px; color: var(--text2); font-weight: 500; }
    .pdv-sum-val.strike { text-decoration: line-through; opacity: .5; }
    .pdv-total-row {
        display: flex; justify-content: space-between; align-items: baseline;
        margin-top: .4rem; padding-top: .4rem; border-top: 1px solid var(--border);
    }
    .pdv-total-key { font-size: 12px; color: var(--text2); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .pdv-total-val { font-size: 26px; font-weight: 800; color: var(--green); letter-spacing: -1px; line-height: 1; }

    /* Payment */
    .pdv-payment {
        flex-shrink: 0;
        padding: .6rem .9rem;
        border-top: 1px solid var(--border);
    }
    .pdv-payment-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .4rem; }
    .pdv-pay-btn {
        padding: 8px 4px;
        background: var(--surface2); border: 1.5px solid var(--border);
        border-radius: 8px; color: var(--text3); font-size: 11px; font-weight: 600;
        cursor: pointer; transition: all .15s; text-align: center;
        display: flex; flex-direction: column; align-items: center; gap: 3px;
    }
    .pdv-pay-btn:hover { border-color: var(--text2); color: var(--text2); }
    .pdv-pay-btn.active { background: rgba(59,130,246,.12); border-color: var(--accent); color: var(--accent); }
    .pdv-pay-btn svg { opacity: .7; }
    .pdv-pay-btn.active svg { opacity: 1; }

    .pdv-cash-row {
        display: flex; gap: .5rem; align-items: center; margin-top: .5rem;
    }
    .pdv-cash-label { font-size: 12px; color: var(--text3); white-space: nowrap; }
    .pdv-cash-input {
        flex: 1; height: 32px;
        background: var(--bg); border: 1px solid var(--border);
        border-radius: 6px; padding: 0 .6rem;
        color: var(--text); font-size: 13px; outline: none;
        transition: border-color .15s;
    }
    .pdv-cash-input:focus { border-color: var(--accent); }
    .pdv-cash-input::-webkit-outer-spin-button,
    .pdv-cash-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .pdv-cash-input { -moz-appearance: textfield; }
    .pdv-change-row { display: flex; justify-content: space-between; margin-top: .35rem; }
    .pdv-change-key { font-size: 12px; color: var(--text3); }
    .pdv-change-val { font-size: 14px; font-weight: 700; color: var(--yellow); }

    /* Actions */
    .pdv-actions {
        flex-shrink: 0;
        padding: .65rem .9rem;
        border-top: 1px solid var(--border);
        display: flex; flex-direction: column; gap: .4rem;
    }
    .pdv-finalize-btn {
        width: 100%; height: 50px;
        background: var(--green); border: none; border-radius: 10px;
        color: #fff; font-size: 15px; font-weight: 700;
        cursor: pointer; transition: all .15s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .pdv-finalize-btn:hover { background: #16a34a; }
    .pdv-finalize-btn:disabled { opacity: .35; cursor: not-allowed; background: var(--green); }
    .pdv-sec-row { display: flex; gap: .4rem; }
    .pdv-sec-btn {
        flex: 1; height: 36px;
        background: var(--surface2); border: 1px solid var(--border);
        border-radius: 8px; color: var(--text3); font-size: 12px; font-weight: 600;
        cursor: pointer; transition: all .15s;
    }
    .pdv-sec-btn:hover { border-color: var(--text2); color: var(--text2); }
    .pdv-sec-btn.danger:hover { border-color: var(--red); color: var(--red); }

    /* ── MODAL ──────────────────────────────────────────────────────── */
    .pdv-modal-overlay {
        position: fixed; inset: 0; z-index: 100;
        background: rgba(0,0,0,.65); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
    }
    .pdv-modal {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 16px; width: 420px; max-width: 95vw;
        padding: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,.5);
    }
    .pdv-modal-title { font-size: 18px; font-weight: 700; margin-bottom: .35rem; }
    .pdv-modal-sub { font-size: 13px; color: var(--text3); margin-bottom: 1.25rem; }
    .pdv-modal-total { font-size: 38px; font-weight: 800; color: var(--green); letter-spacing: -2px; margin-bottom: .25rem; }
    .pdv-modal-order { font-size: 12px; color: var(--text3); margin-bottom: 1.5rem; }
    .pdv-modal-btn {
        width: 100%; height: 44px;
        background: var(--accent); border: none; border-radius: 9px;
        color: #fff; font-size: 14px; font-weight: 700;
        cursor: pointer; transition: background .15s;
    }
    .pdv-modal-btn:hover { background: #2563eb; }

    /* Variation modal */
    .pdv-var-modal { width: 360px; }
    .pdv-var-grid { display: flex; flex-direction: column; gap: .4rem; margin-bottom: 1rem; }
    .pdv-var-btn {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 14px;
        background: var(--bg); border: 1px solid var(--border);
        border-radius: 8px; cursor: pointer; transition: all .15s;
    }
    .pdv-var-btn:hover { border-color: var(--accent); }
    .pdv-var-info { display: flex; flex-direction: column; gap: 2px; align-items: flex-start; }
    .pdv-var-name { font-size: 13px; font-weight: 500; }
    .pdv-var-price { font-size: 11px; color: var(--green); }
    .pdv-var-stock { font-size: 11px; color: var(--text3); }
    .pdv-var-btn.zero-stock { opacity: .4; cursor: not-allowed; }
    .pdv-modal-cancel {
        width: 100%; height: 38px;
        background: var(--surface2); border: 1px solid var(--border);
        border-radius: 8px; color: var(--text3); font-size: 13px;
        cursor: pointer; transition: all .15s;
    }
    .pdv-modal-cancel:hover { border-color: var(--text2); color: var(--text2); }
</style>

    {{-- TOPBAR --}}
    <div class="pdv-top">
        <div class="pdv-brand">Zenvyra <small>PDV</small></div>
        <div class="pdv-vsep"></div>
        <div class="pdv-meta">Operador: <strong>{{ $operator?->name }}</strong></div>
        <div class="pdv-meta">Loja: <strong>{{ $tenant?->te_name }}</strong></div>
        <div class="pdv-spacer"></div>
        <div class="pdv-clock" x-data="{t:''}" x-init="setInterval(()=>{t=new Date().toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'})},1000)" x-text="t"></div>
        <div class="pdv-vsep"></div>
        <div class="pdv-pill-green">CAIXA ABERTO</div>
        <a href="{{ $dashUrl }}" class="pdv-exit-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M5 12l7-7M5 12l7 7"/></svg>
            Sair do PDV
        </a>
    </div>

    {{-- BODY --}}
    <div class="pdv-body">

        {{-- LEFT: CATALOG --}}
        <div class="pdv-catalog">

            {{-- Search --}}
            <div class="pdv-searchbar">
                <div class="pdv-search-wrap">
                    <svg class="pdv-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input
                        type="text"
                        class="pdv-search-input"
                        placeholder="Nome, SKU ou código de barras…"
                        wire:model.live.debounce.300ms="search"
                        autocomplete="off"
                    >
                </div>
            </div>

            {{-- Category tabs --}}
            <div class="pdv-cattabs">
                <button
                    type="button"
                    class="pdv-catbtn {{ $categoryFilter === null ? 'active' : '' }}"
                    wire:click="$set('categoryFilter', null)"
                >Todos</button>
                @foreach ($this->categories as $cat)
                    <button
                        type="button"
                        class="pdv-catbtn {{ $categoryFilter === $cat->id ? 'active' : '' }}"
                        wire:click="$set('categoryFilter', {{ $cat->id }})"
                    >{{ $cat->name }}</button>
                @endforeach
            </div>

            {{-- Product grid --}}
            <div class="pdv-grid-wrap">
                <div class="pdv-grid">
                    @forelse ($this->products as $product)
                        @php
                            $stockTotal   = $product->effectiveStock();
                            $stockClass   = $stockTotal === 0 ? 'zero' : ($product->isLowStock() ? 'low' : '');
                            $hasVariation = $product->hasVariations();
                            $imgUrl       = $product->primaryImage?->path
                                            ? \Illuminate\Support\Facades\Storage::url($product->primaryImage->path)
                                            : null;
                        @endphp
                        <div
                            class="pdv-card"
                            wire:click="addProduct({{ $product->id }})"
                            title="{{ $product->title }}"
                        >
                            @if ($hasVariation)
                                <span class="pdv-card-var-badge">VAR</span>
                            @endif

                            <div class="pdv-card-img">
                                @if ($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="{{ $product->title }}" loading="lazy">
                                @else
                                    <span class="pdv-card-img-placeholder">📦</span>
                                @endif
                            </div>

                            <div class="pdv-card-body">
                                <div class="pdv-card-name">{{ $product->title }}</div>
                                <div class="pdv-card-price">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
                                <div class="pdv-card-stock {{ $stockClass }}">
                                    @if ($stockTotal === 0)
                                        Sem estoque
                                    @elseif ($product->isLowStock())
                                        ⚠ {{ $stockTotal }} restante{{ $stockTotal > 1 ? 's' : '' }}
                                    @else
                                        {{ $stockTotal }} em estoque
                                    @endif
                                </div>
                            </div>

                            @if ($stockTotal === 0)
                                <div class="pdv-card-overlay">SEM ESTOQUE</div>
                            @endif
                        </div>
                    @empty
                        <div class="pdv-empty-state">
                            <div class="pdv-empty-state-icon">🔍</div>
                            <p>Nenhum produto encontrado.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- RIGHT: CART --}}
        <div class="pdv-cart">

            {{-- Customer --}}
            <div class="pdv-customer-bar">
                <div class="pdv-label">Cliente</div>
                @if ($selectedCustomerId)
                    <div class="pdv-customer-selected">
                        <div class="pdv-avatar">{{ mb_strtoupper(mb_substr($selectedCustomerName, 0, 1)) }}</div>
                        <div class="pdv-customer-info">
                            <div class="pdv-customer-name">{{ $selectedCustomerName }}</div>
                            @if ($selectedCustomerPhone)
                                <div class="pdv-customer-phone">{{ $selectedCustomerPhone }}</div>
                            @endif
                        </div>
                        <button type="button" class="pdv-clear-btn" wire:click="clearCustomer" title="Remover cliente">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                @else
                    <div class="pdv-customer-search-wrap">
                        <input
                            type="text"
                            class="pdv-customer-input"
                            placeholder="Buscar cliente por nome ou telefone…"
                            wire:model.live.debounce.300ms="customerSearch"
                            wire:focus="$set('showCustomerResults', true)"
                            autocomplete="off"
                        >
                        <svg class="pdv-customer-search-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        @if ($showCustomerResults && $this->customerResults->isNotEmpty())
                            <div class="pdv-customer-dropdown">
                                @foreach ($this->customerResults as $customer)
                                    <div class="pdv-customer-option" wire:click="selectCustomer({{ $customer->id }})">
                                        <div class="pdv-avatar" style="width:24px;height:24px;font-size:10px">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</div>
                                        <div>
                                            <div class="pdv-customer-option-name">{{ $customer->name }}</div>
                                            @if ($customer->phone)
                                                <div class="pdv-customer-option-phone">{{ $customer->phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <div class="pdv-items">
                @if (empty($cart))
                    <div class="pdv-empty-cart">
                        <div class="pdv-empty-cart-icon">🛒</div>
                        <p>Clique em um produto ao lado para adicioná-lo à venda.</p>
                    </div>
                @else
                    @foreach ($cart as $index => $item)
                        <div class="pdv-item">
                            <div class="pdv-item-info">
                                <div class="pdv-item-name" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                                @if ($item['variation_name'])
                                    <div class="pdv-item-var">{{ $item['variation_name'] }}</div>
                                @endif
                                <div class="pdv-item-price">
                                    R$ {{ number_format((float)$item['price'] * $item['quantity'], 2, ',', '.') }}
                                    <span style="font-size:10px;color:var(--text3);font-weight:400"> × R$ {{ number_format($item['price'], 2, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="pdv-qty">
                                <button type="button" class="pdv-qty-btn" wire:click="decrementQuantity({{ $index }})">−</button>
                                <span class="pdv-qty-num">{{ $item['quantity'] }}</span>
                                <button type="button" class="pdv-qty-btn" wire:click="incrementQuantity({{ $index }})">+</button>
                            </div>
                            <button type="button" class="pdv-remove-btn" wire:click="removeFromCart({{ $index }})" title="Remover">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Discount --}}
            <div class="pdv-discount-row">
                <span class="pdv-discount-label">Desconto (R$)</span>
                <div style="flex:1"></div>
                <input
                    type="number"
                    class="pdv-discount-input"
                    min="0"
                    step="0.01"
                    placeholder="0,00"
                    wire:model.lazy="discountAmount"
                >
            </div>

            {{-- Summary --}}
            <div class="pdv-summary">
                <div class="pdv-sum-row">
                    <span class="pdv-sum-key">{{ $this->cartCount }} {{ $this->cartCount === 1 ? 'item' : 'itens' }}</span>
                    <span class="pdv-sum-val">R$ {{ number_format($this->cartSubtotal, 2, ',', '.') }}</span>
                </div>
                @if ((float)$discountAmount > 0)
                    <div class="pdv-sum-row">
                        <span class="pdv-sum-key">Desconto</span>
                        <span class="pdv-sum-val" style="color:var(--yellow)">− R$ {{ number_format(min((float)$discountAmount, (float)$this->cartSubtotal), 2, ',', '.') }}</span>
                    </div>
                @endif
                <div class="pdv-total-row">
                    <span class="pdv-total-key">Total</span>
                    <span class="pdv-total-val">R$ {{ number_format($this->cartTotal, 2, ',', '.') }}</span>
                </div>
            </div>

            {{-- Payment --}}
            <div class="pdv-payment">
                <div class="pdv-label">Forma de pagamento</div>
                <div class="pdv-payment-grid">
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'pix' ? 'active' : '' }}" wire:click="$set('paymentMethod','pix')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.5 2 4.5 4.5L17.5 2"/><path d="M2 8.5 6.5 13 2 17.5"/><path d="m8.5 22 4.5-4.5 4.5 4.5"/><path d="M22 8.5 17.5 13l4.5 4.5"/></svg>
                        PIX
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'cartao_credito' ? 'active' : '' }}" wire:click="$set('paymentMethod','cartao_credito')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Crédito
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'cartao_debito' ? 'active' : '' }}" wire:click="$set('paymentMethod','cartao_debito')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Débito
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'dinheiro' ? 'active' : '' }}" wire:click="$set('paymentMethod','dinheiro')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
                        Dinheiro
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'transferencia' ? 'active' : '' }}" wire:click="$set('paymentMethod','transferencia')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        Transfer.
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'outro' ? 'active' : '' }}" wire:click="$set('paymentMethod','outro')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                        Outro
                    </button>
                </div>

                @if ($paymentMethod === 'dinheiro')
                    <div class="pdv-cash-row">
                        <span class="pdv-cash-label">Recebido</span>
                        <input
                            type="number"
                            class="pdv-cash-input"
                            placeholder="0,00"
                            step="0.01"
                            wire:model.lazy="cashGiven"
                        >
                    </div>
                    <div class="pdv-change-row">
                        <span class="pdv-change-key">Troco</span>
                        <span class="pdv-change-val">R$ {{ number_format($this->change, 2, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="pdv-actions">
                <button
                    type="button"
                    class="pdv-finalize-btn"
                    wire:click="finalizeOrder"
                    wire:loading.attr="disabled"
                    @disabled(empty($cart))
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                    <span wire:loading.remove>Finalizar Venda</span>
                    <span wire:loading>Processando…</span>
                </button>
                <div class="pdv-sec-row">
                    <button type="button" class="pdv-sec-btn danger" wire:click="cancelSale" wire:confirm="Cancelar a venda atual?">
                        ✕ Cancelar venda
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL: Variation picker --}}
    @if ($showVariationModal && $this->variationProduct)
        <div class="pdv-modal-overlay" wire:click.self="closeVariationModal">
            <div class="pdv-modal pdv-var-modal">
                <div class="pdv-modal-title">Selecionar variação</div>
                <div class="pdv-modal-sub">{{ $this->variationProduct->title }}</div>
                <div class="pdv-var-grid">
                    @foreach ($this->variationProduct->variations as $variation)
                        <button
                            type="button"
                            class="pdv-var-btn {{ $variation->stock === 0 ? 'zero-stock' : '' }}"
                            @disabled($variation->stock === 0)
                            wire:click="addProductVariation({{ $this->variationProduct->id }}, {{ $variation->id }})"
                        >
                            <span class="pdv-var-info">
                                <span class="pdv-var-name">{{ $variation->name }}</span>
                                <span class="pdv-var-price">R$ {{ number_format($variation->effectivePrice($this->variationProduct), 2, ',', '.') }}</span>
                            </span>
                            <span class="pdv-var-stock">
                                @if ($variation->stock === 0) Sem estoque
                                @else {{ $variation->stock }} em estoque
                                @endif
                            </span>
                        </button>
                    @endforeach
                </div>
                <button type="button" class="pdv-modal-cancel" wire:click="closeVariationModal">Cancelar</button>
            </div>
        </div>
    @endif

    {{-- MODAL: Sale success --}}
    @if ($showSuccessModal)
        <div class="pdv-modal-overlay">
            <div class="pdv-modal" style="text-align:center">
                <div style="font-size:48px;margin-bottom:.5rem">✅</div>
                <div class="pdv-modal-title">Venda finalizada!</div>
                <div class="pdv-modal-sub">Pedido registrado com sucesso</div>
                <div class="pdv-modal-total">R$ {{ number_format($lastOrderTotal, 2, ',', '.') }}</div>
                <div class="pdv-modal-order">Pedido #{{ $lastOrderId }}</div>
                <button type="button" class="pdv-modal-btn" wire:click="closeSuccessModal">
                    Nova venda
                </button>
            </div>
        </div>
    @endif

</div>
