<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Orders\Pages;

use App\Filament\App\Pages\PdvPage;
use App\Filament\App\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(): void
    {
        // Criação de pedidos é feita exclusivamente pelo PDV
        $this->redirect(PdvPage::getUrl(panel: 'app'));
    }
}
