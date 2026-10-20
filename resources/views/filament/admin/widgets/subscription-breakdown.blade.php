<x-filament-widgets::widget>
    <x-filament::section heading="Assinaturas por situação" :description="'Total de '.array_sum($statuses).' assinaturas atuais'">
        <div class="panel-breakdown">
            @foreach (\App\Enums\SubscriptionStatus::cases() as $status)
                @php
                    $count = $statuses[$status->value] ?? 0;
                    $percentage = array_sum($statuses) ? (int) round($count / array_sum($statuses) * 100) : 0;
                @endphp
                <div class="panel-breakdown-row">
                    <span>{{ $status->getLabel() }}</span><strong>{{ $count }}</strong>
                    <progress class="panel-progress panel-progress-{{ $status->value }}" value="{{ $count }}" max="{{ max(1, array_sum($statuses)) }}" aria-label="{{ $status->getLabel() }}"></progress>
                    <small>{{ $percentage }}%</small>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
