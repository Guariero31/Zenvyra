<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Plans\PlanResource;
use App\Filament\Admin\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Admin\Resources\Tenants\TenantResource;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected string $view = 'filament.admin.widgets.quick-actions';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{title: string, description: string, url: string}>
     */
    protected function getViewData(): array
    {
        return [
            'actions' => [
                [
                    'title' => 'Criar Nova Loja',
                    'description' => 'Adicione um novo tenant ao sistema',
                    'url' => TenantResource::getUrl('create'),
                ],
                [
                    'title' => 'Ver Relatórios',
                    'description' => 'Análise de uso e métricas',
                    'url' => SubscriptionResource::getUrl('index'),
                ],
                [
                    'title' => 'Gestão de Planos',
                    'description' => 'Configure planos e preços',
                    'url' => PlanResource::getUrl('index'),
                ],
            ],
        ];
    }
}
