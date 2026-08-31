<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SubscriptionIntent;
use App\Enums\SubscriptionStatus;
use App\Http\Middleware\EnsureSubscriptionIsActive;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionManager;
use App\Services\TenantEvaluationService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EvaluationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): SubscriptionManager
    {
        return app(SubscriptionManager::class);
    }

    public function test_start_evaluation_uses_configured_default_days(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['slug' => 'avaliacao', 'trial_days' => 0]);

        $subscription = $this->manager()->startEvaluation($tenant, $plan);

        $this->assertSame(SubscriptionIntent::Evaluation, $subscription->intent);
        $this->assertSame(SubscriptionStatus::Trialing, $subscription->status);
        $this->assertNotNull($subscription->next_billing_at);
        $this->assertEquals($subscription->trial_ends_at, $subscription->next_billing_at);
        $this->assertEqualsWithDelta(
            now()->addDays((int) config('subscriptions.evaluation_days'))->timestamp,
            $subscription->trial_ends_at?->timestamp,
            5,
        );
    }

    public function test_start_evaluation_accepts_custom_days(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['slug' => 'avaliacao', 'trial_days' => 0]);

        $subscription = $this->manager()->startEvaluation($tenant, $plan, 12);

        $this->assertEqualsWithDelta(
            now()->addDays(12)->timestamp,
            $subscription->trial_ends_at?->timestamp,
            5,
        );
    }

    public function test_evaluation_expires_without_grace_period(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['slug' => 'avaliacao', 'trial_days' => 0]);
        $subscription = $this->manager()->startEvaluation($tenant, $plan, 7);
        $subscription->update(['trial_ends_at' => now()->subHour()]);

        $this->manager()->refreshState($subscription->fresh());

        $subscription->refresh();
        $tenant->refresh();

        $this->assertSame(SubscriptionStatus::Expired, $subscription->status);
        $this->assertFalse($subscription->grantsAccess());
        $this->assertNull($subscription->grace_ends_at);
        $this->assertNotNull($tenant->te_data_retention_ends_at);
        $this->assertEqualsWithDelta(
            now()->addDays((int) config('subscriptions.evaluation_data_retention_days'))->timestamp,
            $tenant->te_data_retention_ends_at->timestamp,
            5,
        );
    }

    public function test_commercial_trial_still_enters_grace_period(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->subHour(),
        ]);

        $this->manager()->refreshState($subscription);

        $this->assertSame(SubscriptionStatus::PastDue, $subscription->status);
        $this->assertTrue($subscription->grantsAccess());
    }

    public function test_convert_evaluation_to_commercial_activates_and_clears_retention(): void
    {
        $tenant = Tenant::factory()->create([
            'te_data_retention_ends_at' => now()->addDays(30),
        ]);
        $evaluationPlan = Plan::factory()->create(['slug' => 'avaliacao', 'trial_days' => 0]);
        $commercialPlan = Plan::factory()->create(['slug' => 'starter', 'trial_days' => 7]);
        $subscription = Subscription::factory()->evaluation()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $evaluationPlan->id,
            'status' => SubscriptionStatus::Expired,
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->manager()->convertEvaluationToCommercial($subscription, $commercialPlan);

        $subscription->refresh();
        $tenant->refresh();

        $this->assertSame(SubscriptionIntent::Commercial, $subscription->intent);
        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertSame($commercialPlan->id, $subscription->plan_id);
        $this->assertTrue($subscription->grantsAccess());
        $this->assertNull($tenant->te_data_retention_ends_at);
        $this->assertNull($tenant->te_data_purged_at);
    }

    public function test_start_commercial_subscription_skips_trial(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['trial_days' => 14]);

        $subscription = $this->manager()->startCommercialSubscription($tenant, $plan);

        $this->assertSame(SubscriptionIntent::Commercial, $subscription->intent);
        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertNull($subscription->trial_ends_at);
        $this->assertTrue($subscription->isActive());
    }

    public function test_middleware_blocks_expired_evaluation(): void
    {
        $tenant = Tenant::factory()->create();
        Subscription::factory()->evaluation()->create([
            'tenant_id' => $tenant->id,
            'trial_ends_at' => now()->subHour(),
        ]);

        $this->bootTenantPanel($tenant);

        $response = (new EnsureSubscriptionIsActive(app(SubscriptionManager::class)))
            ->handle(Request::create('/app/'.$tenant->te_slug), fn (): Response => new Response('ok'));

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('assinatura-pendente', $response->headers->get('Location'));
        $this->assertSame(SubscriptionStatus::Expired, $tenant->subscriptions()->latest('id')->first()->status);
    }

    public function test_purge_evaluation_data_marks_tenant_as_purged(): void
    {
        $tenant = Tenant::factory()->create([
            'te_data_retention_ends_at' => now()->addDays(10),
        ]);

        app(TenantEvaluationService::class)->purge($tenant);

        $tenant->refresh();

        $this->assertNotNull($tenant->te_data_purged_at);
        $this->assertNull($tenant->te_data_retention_ends_at);
        $this->assertFalse($tenant->te_ativo);
    }

    public function test_purge_due_tenants_command(): void
    {
        $due = Tenant::factory()->create([
            'te_data_retention_ends_at' => now()->subDay(),
        ]);
        $pending = Tenant::factory()->create([
            'te_data_retention_ends_at' => now()->addDay(),
        ]);

        $this->artisan('tenants:purge-evaluation-data')->assertSuccessful();

        $this->assertNotNull($due->fresh()->te_data_purged_at);
        $this->assertNull($pending->fresh()->te_data_purged_at);
    }

    public function test_blocked_page_shows_evaluation_message(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => 'administrador_cliente']);
        Subscription::factory()->evaluation()->create([
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::Expired,
            'trial_ends_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('subscription.blocked', ['tenant' => $tenant->te_slug]));

        $response->assertOk();
        $response->assertSee('Período de avaliação encerrado');
        $response->assertSee($tenant->te_name);
    }

    private function bootTenantPanel(Tenant $tenant): void
    {
        $this->actingAs(User::factory()->create());

        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
    }
}
