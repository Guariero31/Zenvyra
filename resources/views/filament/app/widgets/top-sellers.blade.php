<x-filament-widgets::widget>
    <x-filament::section heading="Melhores Vendedores">
        @if (empty($sellers))
            <p class="text-sm text-gray-500">Nenhuma venda registrada no período.</p>
        @else
            <div class="space-y-4">
                @foreach ($sellers as $seller)
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $seller['name'] }}
                            </span>
                            <span class="text-sm text-gray-500">
                                R$ {{ number_format($seller['gross'], 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div
                                class="h-full rounded-full"
                                style="width: {{ $seller['percentage'] }}%; background-color: #4648d4;"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
