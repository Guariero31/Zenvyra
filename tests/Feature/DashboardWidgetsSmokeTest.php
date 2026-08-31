<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Widgets\MonthlyRevenueChart;
use App\Filament\App\Widgets\PaymentMethodRevenueChart;
use App\Filament\App\Widgets\RecentOrdersWidget;
use App\Filament\App\Widgets\SalesStatsOverview;
use App\Filament\App\Widgets\TopSellersWidget;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);
    }

    public function test_redesigned_dashboard_widgets_render_without_error(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Dashboard Teste']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();
        session(['tenant_id' => $tenant->getKey()]);

        foreach ([
            SalesStatsOverview::class,
            MonthlyRevenueChart::class,
            PaymentMethodRevenueChart::class,
            RecentOrdersWidget::class,
            TopSellersWidget::class,
        ] as $widgetClass) {
            Livewire::test($widgetClass)->assertSuccessful();
        }
    }
}
