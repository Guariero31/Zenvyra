<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Subscriptions\SubscriptionResource;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringEvaluationsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Subscription::query()->expiringEvaluations()->exists();
    }

    public function getHeading(): ?string
    {
        $days = (int) config('subscriptions.evaluation_expiring_alert_days', 7);

        return "Avaliações expirando em {$days} dias";
    }

    public function getDescription(): ?string
    {
        return 'Lojas em período de teste comercial que precisam de follow-up antes do bloqueio automático.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscription::query()
                    ->expiringEvaluations()
                    ->with(['tenant', 'plan'])
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('tenant.te_name')
                    ->label('Loja')
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),

                TextColumn::make('plan.name')
                    ->label('Plano')
                    ->badge()
                    ->color('info'),

                TextColumn::make('trial_ends_at')
                    ->label('Fim da avaliação')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('days_remaining')
                    ->label('Dias restantes')
                    ->state(fn (Subscription $record): string => self::formatDaysRemaining($record))
                    ->badge()
                    ->color(fn (Subscription $record): string => self::daysRemainingColor($record)),
            ])
            ->headerActions([
                Action::make('ver_assinaturas')
                    ->label('Ver assinaturas')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->url(SubscriptionResource::getUrl('index'))
                    ->color('gray'),
            ])
            ->recordUrl(
                fn (Subscription $record): string => SubscriptionResource::getUrl('edit', ['record' => $record])
            );
    }

    private static function formatDaysRemaining(Subscription $record): string
    {
        $days = $record->daysUntilTrialEnds();

        if ($days === null) {
            return '-';
        }

        if ($days === 0) {
            return 'Hoje';
        }

        if ($days === 1) {
            return '1 dia';
        }

        return "{$days} dias";
    }

    private static function daysRemainingColor(Subscription $record): string
    {
        $days = $record->daysUntilTrialEnds();

        return match (true) {
            $days === null => 'gray',
            $days <= 2 => 'danger',
            $days <= 5 => 'warning',
            default => 'success',
        };
    }
}
