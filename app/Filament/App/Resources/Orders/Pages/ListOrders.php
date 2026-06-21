<?php

namespace App\Filament\App\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\App\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Todos')
                ->badge(fn (): int => $this->tabCount())
                ->deferBadge(),
        ];

        foreach (OrderStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status))
                ->badge(fn (): int => $this->tabCount($status))
                ->badgeColor($status->getColor())
                ->deferBadge();
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    private function tabCount(?OrderStatus $status = null): int
    {
        $query = OrderResource::getEloquentQuery();

        if ($status instanceof OrderStatus) {
            $query->where('status', $status);
        }

        return $query->count();
    }
}
