<x-filament-widgets::widget>
    <div class="panel-notice {{ $overdue ? 'panel-notice-warning' : 'panel-notice-success' }}">
        @svg($overdue ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle', 'panel-notice-icon')
        <p>{{ $overdue ? $overdue.' '.($overdue === 1 ? 'assinatura precisa' : 'assinaturas precisam').' de atenção.' : 'Nenhuma assinatura em atraso no momento.' }}</p>
        <x-filament::link :href="\App\Filament\Admin\Resources\Subscriptions\SubscriptionResource::getUrl()" color="gray" icon="heroicon-m-arrow-right" icon-position="after">Ver assinaturas</x-filament::link>
    </div>
</x-filament-widgets::widget>
