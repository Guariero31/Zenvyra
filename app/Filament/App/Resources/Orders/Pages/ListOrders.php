<?php

namespace App\Filament\App\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\App\Pages\PdvPage;
use App\Filament\App\Resources\Orders\OrderResource;
use App\Filament\Exports\OrderExporter;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
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
            ExportAction::make('export')
                ->exporter(OrderExporter::class)
                ->label('Exportar')
                ->color('gray')
                ->outlined(),

            Action::make('open_pdv')
                ->label('Abrir PDV')
                ->icon(Heroicon::OutlinedShoppingCart)
                ->color('success')
                ->url(fn (): string => PdvPage::getUrl(panel: 'app'))
                ->openUrlInNewTab(),
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
