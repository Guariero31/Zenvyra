<?php

namespace App\Filament\Admin\Resources\Subscriptions\Schemas;

use App\Enums\SubscriptionIntent;
use App\Enums\SubscriptionStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Loja')
                            ->relationship('tenant', 'te_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->hintIcon(Heroicon::OutlinedQuestionMarkCircle, 'Loja que é dona desta assinatura.'),
                        Select::make('plan_id')
                            ->label('Plano')
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->hintIcon(Heroicon::OutlinedQuestionMarkCircle, 'Plano comercial que define preço, período de cobrança e limites.'),
                        Select::make('status')
                            ->label('Status')
                            ->options(SubscriptionStatus::options())
                            ->required()
                            ->default(SubscriptionStatus::Active->value)
                            ->hintIcon(Heroicon::OutlinedQuestionMarkCircle, 'Situação atual do ciclo: em teste, ativa, atrasada, cancelada ou expirada.'),
                        Select::make('intent')
                            ->label('Intenção')
                            ->options(SubscriptionIntent::options())
                            ->required()
                            ->default(SubscriptionIntent::Commercial->value)
                            ->hintIcon(Heroicon::OutlinedQuestionMarkCircle, 'Avaliação: ambiente reduzido sem tolerância após o prazo. Comercial: assinatura paga com ciclo normal.'),
                        DatePicker::make('trial_ends_at')
                            ->label('Fim do teste')
                            ->default(now()->addDays((int) config('subscriptions.default_trial_days')))
                            ->hintIcon(Heroicon::OutlinedQuestionMarkCircle, 'Quando o período de teste grátis termina. Depois disso, a assinatura fica atrasada se não for ativada.'),
                        DatePicker::make('starts_at')
                            ->label('Início')
                            ->default(now())
                            ->live()
                            ->hintIcon(Heroicon::OutlinedQuestionMarkCircle, 'Data em que esta assinatura começou.'),
                        DatePicker::make('ends_at')
                            ->label('Fim do ciclo')
                            ->default(now()->addDays((int) config('subscriptions.billing_cycle_days')))
                            ->hintIcon(Heroicon::OutlinedQuestionMarkCircle, 'Até quando o ciclo atual está pago/ativo (padrão: 30 dias após o início). Ao vencer, a assinatura passa para atrasada. Pode ser ajustado manualmente, ex.: para dar dias de cortesia.'),
                        DatePicker::make('grace_ends_at')
                            ->label('Fim da tolerância')
                            ->default(now()->addDays((int) config('subscriptions.billing_cycle_days') + (int) config('subscriptions.grace_days')))
                            ->hintIcon(Heroicon::OutlinedQuestionMarkCircle, 'Prazo final para regularizar enquanto está atrasada (padrão: 5 dias após o fim do ciclo). Depois disso, o acesso à loja é bloqueado.'),
                        TextEntry::make('next_billing_at')
                            ->label('Próxima cobrança')
                            ->badge()
                            ->color('info')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->state(fn (Get $get): string => ($get('starts_at') ? Carbon::parse($get('starts_at')) : now())
                                ->addDays((int) config('subscriptions.billing_cycle_days'))
                                ->format('d/m/Y'))
                            ->hintIcon(Heroicon::OutlinedQuestionMarkCircle, 'Calculada automaticamente: 30 dias após o início. Não é editável.'),
                    ]),
            ]);
    }
}
