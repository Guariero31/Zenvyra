@if ($notice instanceof \App\Data\SubscriptionRenewalNotice)
    @php
        $colors = match ($notice->severity) {
            'danger' => 'border-danger-600/30 bg-danger-50 text-danger-800 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-200',
            'warning' => 'border-warning-600/30 bg-warning-50 text-warning-900 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-100',
            default => 'border-info-600/30 bg-info-50 text-info-900 dark:border-info-500/30 dark:bg-info-500/10 dark:text-info-100',
        };
    @endphp

    <div class="fi-section mb-6 rounded-xl border px-4 py-3 shadow-sm {{ $colors }}" role="alert">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <div>
                <p class="text-sm font-semibold">{{ $notice->title }}</p>
                <p class="mt-1 text-sm leading-relaxed opacity-90">{{ $notice->message }}</p>
                @if ($notice->graceDaysAfterExpiry !== null && $notice->graceDaysAfterExpiry > 0)
                    <p class="mt-2 text-xs font-medium opacity-80">
                        Tolerância após o vencimento: {{ $notice->graceDaysAfterExpiry }} {{ $notice->graceDaysAfterExpiry === 1 ? 'dia' : 'dias' }}.
                    </p>
                @elseif ($notice->graceDaysAfterExpiry === 0)
                    <p class="mt-2 text-xs font-medium opacity-80">
                        Avaliações não possuem tolerância após o vencimento.
                    </p>
                @endif
            </div>

            <a
                href="mailto:suporte@zenvyra.com.br"
                class="inline-flex shrink-0 items-center justify-center rounded-lg border border-current/20 px-3 py-1.5 text-xs font-semibold transition hover:bg-black/5 dark:hover:bg-white/10"
            >
                Falar com o suporte
            </a>
        </div>
    </div>
@endif
