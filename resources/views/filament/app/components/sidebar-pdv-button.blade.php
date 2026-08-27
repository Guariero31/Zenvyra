@php
    use App\Filament\App\Pages\PdvPage;
@endphp

@if (PdvPage::canAccess())
    <div class="px-3 pb-3">
        <a
            href="{{ PdvPage::getUrl() }}"
            class="fi-sidebar-pdv-btn"
        >
            <x-filament::icon
                icon="heroicon-o-shopping-cart"
                class="h-5 w-5"
            />
            Abrir PDV
        </a>
    </div>
@endif
