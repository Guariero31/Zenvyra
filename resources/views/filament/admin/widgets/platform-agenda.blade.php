<x-filament-widgets::widget class="panel-agenda-widget">
    <x-filament::section heading="Agenda da plataforma">
        <div class="panel-agenda">
            <div class="panel-agenda-item">
                @svg('heroicon-o-calendar-days', 'panel-square-icon')
                <div><strong>{{ $upcoming }} {{ $upcoming === 1 ? 'vencimento' : 'vencimentos' }}</strong><p>nos próximos 7 dias</p></div>
            </div>
            <div class="panel-agenda-item">
                @svg('heroicon-o-users', 'panel-square-icon')
                <div><strong>{{ $evaluations }} {{ $evaluations === 1 ? 'avaliação' : 'avaliações' }}</strong><p>encerram nos próximos 7 dias</p></div>
            </div>
            <div class="panel-agenda-actions">
                <p>Ações rápidas</p>
                <x-filament::button color="gray" outlined icon="heroicon-o-calendar-days" tag="a" :href="\App\Filament\Admin\Resources\Subscriptions\SubscriptionResource::getUrl()">Revisar assinaturas</x-filament::button>
                <x-filament::button color="gray" outlined icon="heroicon-o-squares-2x2" tag="a" :href="\App\Filament\Admin\Resources\Plans\PlanResource::getUrl()">Gerenciar planos</x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
