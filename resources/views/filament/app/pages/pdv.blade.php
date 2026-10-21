@php
    $tenant    = \Filament\Facades\Filament::getTenant();
    $operator  = auth()->user();
    $dashUrl   = \App\Filament\App\Pages\Dashboard::getUrl(panel: 'app');
@endphp

<div class="pdv" wire:poll.60s="" x-data="{}"
    x-on:keydown.f2.window.prevent="if (!$wire.showVariationModal && !$wire.showSuccessModal) $refs.productSearch.focus()"
    x-on:keydown.f4.window.prevent="if (!$wire.showVariationModal && !$wire.showSuccessModal) $refs.customerSearch?.focus()"
    x-on:keydown.f8.window.prevent="if (!$wire.showVariationModal && !$wire.showSuccessModal) $refs.finalizeSale.click()">



    {{-- TOPBAR --}}
    <div class="pdv-top">
        <div class="pdv-brand">{{ filament()->getBrandName() }} <small>PDV</small></div>
        <div class="pdv-vsep"></div>
        <div class="pdv-meta">Operador: <strong>{{ $operator?->name }}</strong></div>
        <div class="pdv-meta">Loja: <strong>{{ $tenant?->te_name }}</strong></div>
        <div class="pdv-spacer"></div>
        <div class="pdv-clock" x-data="{t:''}" x-init="setInterval(()=>{t=new Date().toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'})},1000)" x-text="t"></div>
        <div class="pdv-vsep"></div>
        <div class="pdv-pill-green">Caixa aberto</div>
        <div class="pdv-theme-control">
            <x-filament-panels::theme-switcher />
        </div>
        <a href="{{ $dashUrl }}" class="pdv-exit-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M5 12l7-7M5 12l7 7"/></svg>
            Sair do PDV
        </a>
    </div>

    {{-- BODY --}}
    <div class="pdv-body">

        {{-- LEFT: CATALOG --}}
        <div class="pdv-catalog">

            <div class="pdv-catalog-heading">
                <h1>Vamos vender?</h1>
                <p>Busque um produto ou leia o código de barras.</p>
                <a class="pdv-mobile-cart-link" href="#pdv-current-sale">Ver venda · {{ $this->cartCount }} {{ $this->cartCount === 1 ? 'item' : 'itens' }}</a>
            </div>

            {{-- Search --}}
            <div class="pdv-searchbar">
                <div class="pdv-search-wrap">
                    <svg class="pdv-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input
                        type="text"
                        x-ref="productSearch"
                        aria-label="Buscar produto por nome, SKU ou código de barras"
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
                    aria-pressed="{{ $categoryFilter === null ? 'true' : 'false' }}"
                >Todos</button>
                @foreach ($this->categories as $cat)
                    <button
                        type="button"
                        class="pdv-catbtn {{ $categoryFilter === $cat->id ? 'active' : '' }}"
                        wire:click="$set('categoryFilter', {{ $cat->id }})"
                        aria-pressed="{{ $categoryFilter === $cat->id ? 'true' : 'false' }}"
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
                        <button type="button"
                            class="pdv-card"
                            wire:key="pdv-product-{{ $product->id }}"
                            wire:click="addProduct({{ $product->id }})"
                            title="{{ $product->title }}"
                            aria-label="Adicionar {{ $product->title }} à venda"
                            @disabled($stockTotal <= 0)
                        >
                            @if ($hasVariation)
                                <span class="pdv-card-var-badge">VAR</span>
                            @endif

                            <div class="pdv-card-img">
                                @if ($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="{{ $product->title }}" loading="lazy" x-on:error.once="$el.src = '{{ asset('images/product-placeholder.svg') }}'">
                                @else
                                    <img src="{{ asset('images/product-placeholder.svg') }}" alt="" loading="lazy">
                                @endif
                            </div>

                            <div class="pdv-card-body">
                                <div class="pdv-card-name">{{ $product->title }}</div>
                                <div class="pdv-card-price">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
                                <div class="pdv-card-stock {{ $stockClass }}">
                                    @if ($stockTotal === 0)
                                        Sem estoque
                                    @elseif ($product->isLowStock())
                                        Últimas {{ $stockTotal }} unidade{{ $stockTotal > 1 ? 's' : '' }}
                                    @else
                                        {{ $stockTotal }} em estoque
                                    @endif
                                </div>
                                <span class="pdv-card-add" aria-hidden="true">+</span>
                            </div>

                            @if ($stockTotal === 0)
                                <div class="pdv-card-overlay">SEM ESTOQUE</div>
                            @endif
                        </button>
                    @empty
                        <div class="pdv-empty-state">
                            @svg('heroicon-o-magnifying-glass', 'pdv-empty-state-icon')
                            <p>Nenhum produto encontrado.</p>
                            <span>Tente outro nome, SKU ou categoria.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pdv-shortcuts" aria-label="Atalhos de teclado">
                <span><kbd>F2</kbd> Buscar produto</span>
                <span><kbd>F4</kbd> Cliente</span>
                <span><kbd>F8</kbd> Finalizar</span>
            </div>

        </div>

        {{-- RIGHT: CART --}}
        <section class="pdv-cart" id="pdv-current-sale" aria-labelledby="pdv-sale-heading">

            <div class="pdv-cart-heading">
                <h2 id="pdv-sale-heading">Venda atual</h2>
                <p aria-live="polite">{{ $this->cartCount }} {{ $this->cartCount === 1 ? 'item' : 'itens' }}</p>
            </div>

            {{-- Customer --}}
            <div class="pdv-customer-bar">
                <div class="pdv-customer-label"><span>Cliente</span><span>Opcional</span></div>
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
                            x-ref="customerSearch"
                            aria-label="Buscar cliente por nome ou telefone"
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
                                    <button type="button" class="pdv-customer-option" wire:click="selectCustomer({{ $customer->id }})">
                                        <div class="pdv-avatar" style="width:24px;height:24px;font-size:10px">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</div>
                                        <div>
                                            <div class="pdv-customer-option-name">{{ $customer->name }}</div>
                                            @if ($customer->phone)
                                                <div class="pdv-customer-option-phone">{{ $customer->phone }}</div>
                                            @endif
                                        </div>
                                    </button>
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
                        @svg('heroicon-o-shopping-cart', 'pdv-empty-cart-icon')
                        <strong>Sua venda começa aqui</strong>
                        <p>Selecione um produto no catálogo para adicioná-lo à venda.</p>
                    </div>
                @else
                    @foreach ($cart as $index => $item)
                        <div class="pdv-item" wire:key="pdv-cart-{{ $item['key'] }}">
                            @php($cartProduct = $this->cartProducts->get($item['product_id']))
                            <img class="pdv-item-image" src="{{ $cartProduct?->primaryImage?->path ? \Illuminate\Support\Facades\Storage::url($cartProduct->primaryImage->path) : asset('images/product-placeholder.svg') }}" alt="">
                            <div class="pdv-item-info">
                                <div class="pdv-item-name" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                                @if ($item['variation_name'])
                                    <div class="pdv-item-var">{{ $item['variation_name'] }}</div>
                                @endif
                                <div class="pdv-item-price">
                                    R$ {{ number_format($item['price'], 2, ',', '.') }} / un.
                                </div>
                            </div>
                            <div class="pdv-qty">
                                <button type="button" class="pdv-qty-btn" wire:click="decrementQuantity({{ $index }})" aria-label="Diminuir quantidade de {{ $item['name'] }}">−</button>
                                <span class="pdv-qty-num">{{ $item['quantity'] }}</span>
                                <button type="button" class="pdv-qty-btn" wire:click="incrementQuantity({{ $index }})" aria-label="Aumentar quantidade de {{ $item['name'] }}" @disabled($item['quantity'] >= $item['stock'])>+</button>
                            </div>
                            <strong class="pdv-item-total">R$ {{ number_format(bcmul($item['price'], (string) $item['quantity'], 2), 2, ',', '.') }}</strong>
                            <button type="button" class="pdv-remove-btn" wire:click="removeFromCart({{ $index }})" aria-label="Remover {{ $item['name'] }}" title="Remover">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Discount --}}
            <div class="pdv-discount-row">
                <label class="pdv-discount-label" for="pdv-discount">Desconto (R$)</label>
                <div style="flex:1"></div>
                <input
                    type="number"
                    id="pdv-discount"
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
                    <span class="pdv-sum-key">Subtotal</span>
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
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'pix' ? 'active' : '' }}" aria-pressed="{{ $paymentMethod === 'pix' ? 'true' : 'false' }}" wire:click="$set('paymentMethod','pix')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.5 2 4.5 4.5L17.5 2"/><path d="M2 8.5 6.5 13 2 17.5"/><path d="m8.5 22 4.5-4.5 4.5 4.5"/><path d="M22 8.5 17.5 13l4.5 4.5"/></svg>
                        PIX
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'cartao_credito' ? 'active' : '' }}" aria-pressed="{{ $paymentMethod === 'cartao_credito' ? 'true' : 'false' }}" wire:click="$set('paymentMethod','cartao_credito')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Crédito
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'cartao_debito' ? 'active' : '' }}" aria-pressed="{{ $paymentMethod === 'cartao_debito' ? 'true' : 'false' }}" wire:click="$set('paymentMethod','cartao_debito')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Débito
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'dinheiro' ? 'active' : '' }}" aria-pressed="{{ $paymentMethod === 'dinheiro' ? 'true' : 'false' }}" wire:click="$set('paymentMethod','dinheiro')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
                        Dinheiro
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'transferencia' ? 'active' : '' }}" aria-pressed="{{ $paymentMethod === 'transferencia' ? 'true' : 'false' }}" wire:click="$set('paymentMethod','transferencia')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        Transferência
                    </button>
                    <button type="button" class="pdv-pay-btn {{ $paymentMethod === 'outro' ? 'active' : '' }}" aria-pressed="{{ $paymentMethod === 'outro' ? 'true' : 'false' }}" wire:click="$set('paymentMethod','outro')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                        Outro
                    </button>
                </div>

                @if ($paymentMethod === 'dinheiro')
                    <div class="pdv-cash-row">
                        <label class="pdv-cash-label" for="pdv-cash">Recebido (R$)</label>
                        <input
                            type="number"
                            id="pdv-cash"
                            min="0"
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
                    x-ref="finalizeSale"
                    wire:click="finalizeOrder"
                    wire:loading.attr="disabled"
                    @disabled(empty($cart))
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                    <span wire:loading.remove wire:target="finalizeOrder">Finalizar venda</span>
                    <span wire:loading wire:target="finalizeOrder">Processando…</span>
                    <kbd>F8</kbd>
                </button>
                <div class="pdv-sec-row">
                    <button type="button" class="pdv-sec-btn danger" wire:click="cancelSale" wire:confirm="Cancelar a venda atual?">
                        Cancelar venda
                    </button>
                </div>
            </div>

        </section>
    </div>

    {{-- MODAL: Variation picker --}}
    @if ($showVariationModal && $this->variationProduct)
        <div class="pdv-modal-overlay" wire:click.self="closeVariationModal">
            <div class="pdv-modal pdv-var-modal" role="dialog" aria-modal="true" aria-labelledby="pdv-variation-title" x-trap.inert.noscroll="true" x-on:keydown.escape.stop="$wire.closeVariationModal()">
                <h2 class="pdv-modal-title" id="pdv-variation-title">Selecionar variação</h2>
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
            <div class="pdv-modal pdv-success-modal" role="dialog" aria-modal="true" aria-label="Venda finalizada" x-trap.inert.noscroll="true">
                @svg('heroicon-o-check-circle', 'pdv-success-icon')
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
