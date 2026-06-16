<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\CarbonInterface;
use Filament\Support\Contracts\HasLabel;

enum BillingPeriod: string implements HasLabel
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function getLabel(): string
    {
        return match ($this) {
            self::Monthly => 'Mensal',
            self::Quarterly => 'Trimestral',
            self::Yearly => 'Anual',
        };
    }

    /**
     * Avança uma data conforme o período do plano. Usado para calcular o
     * próximo vencimento quando uma assinatura é ativada ou renovada.
     */
    public function advance(CarbonInterface $date): CarbonInterface
    {
        return match ($this) {
            self::Monthly => $date->addMonth(),
            self::Quarterly => $date->addMonths(3),
            self::Yearly => $date->addYear(),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $period): array => [$period->value => $period->getLabel()])
            ->all();
    }
}
