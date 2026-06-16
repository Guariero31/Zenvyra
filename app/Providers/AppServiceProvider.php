<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Tenant;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use App\Observers\TenantObserver;
use App\Support\PgsqlTenantSearchPath;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        Tenant::observe(TenantObserver::class);

        // A spec pede 'component.boot', mas esse evento não existe no Livewire v4;
        // o evento real disparado na re-hidratação assíncrona é 'hydrate'. O guard
        // pelo tenant ativo evita reaplicar o schema fora do painel do lojista.
        Livewire::listen('hydrate', function (): void {
            if (! Filament::getTenant() instanceof Tenant) {
                return;
            }

            if ($schema = session('tenant_schema')) {
                PgsqlTenantSearchPath::apply($schema);
            }
        });
    }
}
