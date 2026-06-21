<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Enums\TenantPortalRole;
use App\Http\Middleware\EnsureSubscriptionIsActive;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionManager;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): SubscriptionManager
    {
        return app(SubscriptionManager::class);
    }

    public function test_grants_access_for_live_subscriptions(): void
    {
        $active = Subscription::factory()->create();
        $trialing = Subscription::factory()->trialing()->create();
        $inGrace = Subscription::factory()->pastDue()->create();

        $this->assertTrue($active->grantsAccess());
        $this->assertTrue($trialing->grantsAccess());
        $this->assertTrue($inGrace->grantsAccess());
    }

    public function test_denies_access_for_dead_or_lapsed_subscriptions(): void
    {
        $expired = Subscription::factory()->expired()->create();
        $canceled = Subscription::factory()->canceled()->create();
        $trialLapsed = Subscription::factory()->trialing()->create(['trial_ends_at' => now()->subDay()]);
        $graceLapsed = Subscription::factory()->pastDue()->create(['grace_ends_at' => now()->subDay()]);

        $this->assertFalse($expired->grantsAccess());
        $this->assertFalse($canceled->grantsAccess());
        $this->assertFalse($trialLapsed->grantsAccess());
        $this->assertFalse($graceLapsed->grantsAccess());
    }

    public function test_start_subscription_uses_trial_when_plan_offers_one(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['trial_days' => 7]);

        $subscription = $this->manager()->startSubscription($tenant, $plan);

        $this->assertSame(SubscriptionStatus::Trialing, $subscription->status);
        $this->assertNotNull($subscription->trial_ends_at);
        $this->assertNotNull($subscription->next_billing_at);
        $this->assertEquals($subscription->trial_ends_at, $subscription->next_billing_at);
        $this->assertTrue($subscription->onTrial());
    }

    public function test_resolve_next_billing_at_falls_back_when_column_is_null(): void
    {
        $subscription = Subscription::factory()->evaluation()->create([
            'next_billing_at' => null,
            'trial_ends_at' => now()->addDays(5),
        ]);

        $this->assertEquals(
            $subscription->trial_ends_at?->toDateString(),
            $subscription->resolveNextBillingAt()?->toDateString(),
        );
    }

    public function test_resolve_next_billing_at_falls_back_to_starts_at_plus_cycle_for_active(): void
    {
        $startsAt = now()->subDays(3);

        $subscription = Subscription::factory()->create([
            'status' => SubscriptionStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => null,
            'next_billing_at' => null,
        ]);

        $this->assertEqualsWithDelta(
            $startsAt->copy()->addDays(30)->timestamp,
            $subscription->resolveNextBillingAt()?->timestamp,
            5,
        );
    }

    public function test_ensure_billing_dates_backfills_active_subscription(): void
    {
        $startsAt = now()->subDays(3);

        $subscription = Subscription::factory()->create([
            'status' => SubscriptionStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => null,
            'next_billing_at' => null,
        ]);

        $this->manager()->ensureBillingDates($subscription->fresh());

        $subscription->refresh();

        $this->assertNotNull($subscription->ends_at);
        $this->assertNotNull($subscription->next_billing_at);
        $this->assertEquals($subscription->ends_at, $subscription->next_billing_at);
    }

    public function test_start_subscription_activates_directly_without_trial(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['trial_days' => 0]);

        $subscription = $this->manager()->startSubscription($tenant, $plan);

        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertNotNull($subscription->next_billing_at);
        $this->assertTrue($subscription->isActive());
    }

    public function test_activate_sets_billing_cycle_end_date(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['trial_days' => 0]);
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'ends_at' => null,
        ]);

        $this->manager()->activate($subscription);

        $this->assertNotNull($subscription->ends_at);
        $this->assertNotNull($subscription->next_billing_at);
        $this->assertTrue($subscription->ends_at->isFuture());
        $this->assertEquals($subscription->next_billing_at, $subscription->ends_at);
        $this->assertEqualsWithDelta(
            now()->addDays(30)->timestamp,
            $subscription->next_billing_at->timestamp,
            5,
        );
    }

    public function test_next_billing_date_always_uses_thirty_day_cycle(): void
    {
        $start = now()->subDays(3);

        $this->assertEqualsWithDelta(
            $start->copy()->addDays(30)->timestamp,
            $this->manager()->nextBillingDate($start)->timestamp,
            5,
        );
    }

    public function test_refresh_state_moves_active_into_grace_after_cycle_ends(): void
    {
        $subscription = Subscription::factory()->create(['ends_at' => now()->subDay()]);

        $this->manager()->refreshState($subscription);

        $this->assertSame(SubscriptionStatus::PastDue, $subscription->status);
        $this->assertTrue($subscription->grantsAccess());
    }

    public function test_cancel_revokes_access(): void
    {
        $subscription = Subscription::factory()->create();

        $this->manager()->cancel($subscription);

        $this->assertSame(SubscriptionStatus::Canceled, $subscription->status);
        $this->assertNotNull($subscription->canceled_at);
        $this->assertFalse($subscription->grantsAccess());
    }

    public function test_refresh_state_moves_expired_trial_into_grace(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->manager()->refreshState($subscription);

        $this->assertSame(SubscriptionStatus::PastDue, $subscription->status);
        $this->assertTrue($subscription->grantsAccess());
    }

    public function test_refresh_state_expires_after_grace_period(): void
    {
        $subscription = Subscription::factory()->pastDue()->create([
            'grace_ends_at' => now()->subDay(),
        ]);

        $this->manager()->refreshState($subscription);

        $this->assertSame(SubscriptionStatus::Expired, $subscription->status);
        $this->assertFalse($subscription->grantsAccess());
    }

    public function test_refresh_command_transitions_lapsed_subscriptions(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:refresh')->assertSuccessful();

        $this->assertSame(SubscriptionStatus::PastDue, $subscription->refresh()->status);
    }

    public function test_tenant_reports_active_subscription_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        Subscription::factory()->expired()->create(['tenant_id' => $tenant->id]);

        $this->assertFalse($tenant->hasActiveSubscription());

        Subscription::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertTrue($tenant->fresh()->hasActiveSubscription());
    }

    public function test_middleware_transitions_state_and_allows_access_within_grace_period(): void
    {
        $tenant = Tenant::factory()->create();

        // Subscription is Active but ends_at is in the past — no command ran yet
        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'ends_at' => now()->subHour(),
            'grace_ends_at' => null,
        ]);

        $this->bootTenantPanel($tenant);

        // Middleware should call refreshState (→ PastDue) and still allow access
        $response = (new EnsureSubscriptionIsActive(app(SubscriptionManager::class)))
            ->handle(Request::create('/app/'.$tenant->te_slug), fn (): Response => new Response('ok'));

        $this->assertSame('ok', $response->getContent());
        $this->assertSame(SubscriptionStatus::PastDue, $tenant->subscriptions()->latest('id')->first()->status);
    }

    public function test_middleware_transitions_state_and_blocks_access_after_grace_period(): void
    {
        $tenant = Tenant::factory()->create();

        // Subscription is PastDue but grace_ends_at is in the past — no command ran yet
        Subscription::factory()->pastDue()->create([
            'tenant_id' => $tenant->id,
            'grace_ends_at' => now()->subHour(),
        ]);

        $this->bootTenantPanel($tenant);

        // Middleware should call refreshState (→ Expired) and block access without running the command
        $response = (new EnsureSubscriptionIsActive(app(SubscriptionManager::class)))
            ->handle(Request::create('/app/'.$tenant->te_slug), fn (): Response => new Response('ok'));

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('assinatura-pendente', $response->headers->get('Location'));
        $this->assertSame(SubscriptionStatus::Expired, $tenant->subscriptions()->latest('id')->first()->status);
    }

    public function test_middleware_blocks_tenant_without_active_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        Subscription::factory()->expired()->create(['tenant_id' => $tenant->id]);

        $this->bootTenantPanel($tenant);

        $response = (new EnsureSubscriptionIsActive(app(SubscriptionManager::class)))
            ->handle(Request::create('/app/'.$tenant->te_slug), fn (): Response => new Response('ok'));

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('assinatura-pendente', $response->headers->get('Location'));
    }

    public function test_middleware_allows_tenant_with_active_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        Subscription::factory()->create(['tenant_id' => $tenant->id]);

        $this->bootTenantPanel($tenant);

        $response = (new EnsureSubscriptionIsActive(app(SubscriptionManager::class)))
            ->handle(Request::create('/app/'.$tenant->te_slug), fn (): Response => new Response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_blocked_page_renders_with_logout_and_store_switch(): void
    {
        $user = User::factory()->create();

        $blocked = Tenant::factory()->create();
        $blocked->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);
        Subscription::factory()->expired()->create(['tenant_id' => $blocked->id]);

        $active = Tenant::factory()->create();
        $active->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);
        Subscription::factory()->create(['tenant_id' => $active->id]);

        $response = $this->actingAs($user)
            ->get(route('subscription.blocked', ['tenant' => $blocked->te_slug]));

        $response->assertOk();
        $response->assertSee($blocked->te_name);
        $response->assertSee('Sair da conta');
        $response->assertSee($active->te_name);
    }

    private function bootTenantPanel(Tenant $tenant): void
    {
        $this->actingAs(User::factory()->create());

        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
    }
}
