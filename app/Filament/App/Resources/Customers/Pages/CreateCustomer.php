<?php

namespace App\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\CustomerResource;
use App\Filament\Concerns\RedirectsToIndexAfterCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    use RedirectsToIndexAfterCreate;

    protected static string $resource = CustomerResource::class;
}
