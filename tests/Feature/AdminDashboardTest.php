<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Widgets\PlatformOverviewWidget;
use App\Filament\Admin\Widgets\QuickActionsWidget;
use App\Filament\Admin\Widgets\StoresByPlanChart;
use App\Filament\Admin\Widgets\SubscriptionStatusChart;
use App\Filament\Admin\Widgets\TenantsGrowthChart;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs(User::factory()->platformAdmin()->create());
    }

    public function test_dashboard_page_renders(): void
    {
        Livewire::test(Dashboard::class)->assertOk();
    }

    public function test_platform_overview_widget_renders_with_data(): void
    {
        $plan = Plan::factory()->create(['price' => 199.90]);
        $tenant = Tenant::factory()->create(['te_ativo' => true]);
        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'next_billing_at' => now()->addDays(3),
        ]);

        Livewire::test(PlatformOverviewWidget::class)->assertOk();
    }

    public function test_quick_actions_widget_renders(): void
    {
        Livewire::test(QuickActionsWidget::class)
            ->assertOk()
            ->assertSee('Criar Nova Loja')
            ->assertSee('Ver Relatórios')
            ->assertSee('Gestão de Planos');
    }

    public function test_bi_chart_widgets_render(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create();
        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        Livewire::test(TenantsGrowthChart::class)->assertOk();
        Livewire::test(SubscriptionStatusChart::class)->assertOk();
        Livewire::test(StoresByPlanChart::class)->assertOk();
    }
}
