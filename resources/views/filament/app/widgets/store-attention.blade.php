<x-filament-widgets::widget>
    <div class="panel-attention-grid">
        <div class="panel-notice {{ $pending ? 'panel-notice-warning' : 'panel-notice-success' }}">
            @svg('heroicon-o-shopping-cart', 'panel-notice-icon')
            <div><strong>{{ $pending ? $pending.' '.($pending === 1 ? 'pedido aguarda' : 'pedidos aguardam').' confirmação' : 'Todos os pedidos em dia' }}</strong><p>{{ $pending ? 'Revise e confirme para continuar as vendas.' : 'Nenhum pedido aguardando confirmação.' }}</p></div>
            <x-filament::button tag="a" :href="\App\Filament\App\Resources\Orders\OrderResource::getUrl('index', ['tab' => 'pending'])">Ver pedidos</x-filament::button>
        </div>
        <div class="panel-notice {{ $restock ? 'panel-notice-warning' : 'panel-notice-success' }}">
            @svg('heroicon-o-cube', 'panel-notice-icon')
            <div><strong>{{ $restock ? $restock.' '.($restock === 1 ? 'produto precisa' : 'produtos precisam').' de reposição' : 'Seu estoque está em dia' }}</strong><p>{{ $restock ? 'Evite perder vendas por falta de estoque.' : 'Nenhum produto com estoque baixo ou zerado.' }}</p></div>
            <x-filament::button tag="a" :href="\App\Filament\App\Resources\Products\ProductResource::getUrl('index', ['stock' => 'low'])">Ver estoque</x-filament::button>
        </div>
    </div>
</x-filament-widgets::widget>
