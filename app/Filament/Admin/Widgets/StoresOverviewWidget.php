<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Models\Tenant;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class StoresOverviewWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 2];

    protected static ?string $heading = 'Lojas e assinaturas';

    public function table(Table $table): Table
    {
        return $table->query(Tenant::query()->with('currentSubscription.plan')->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('te_name')->label('Loja')->weight('semibold')->description(fn (Tenant $record): string => $record->te_slug),
                TextColumn::make('currentSubscription.plan.name')->label('Plano')->placeholder('Sem plano'),
                TextColumn::make('currentSubscription.status')->label('Situação')->badge()->placeholder('Sem assinatura'),
                TextColumn::make('currentSubscription.next_billing_at')->label('Próximo vencimento')->date('d/m/Y')->placeholder('Não definido'),
            ])
            ->headerActions([Action::make('all')->label('Ver todas')->url(TenantResource::getUrl())->color('gray')->link()->icon('heroicon-m-arrow-right')])
            ->recordUrl(fn (Tenant $record): string => TenantResource::getUrl('edit', ['record' => $record]));
    }
}
