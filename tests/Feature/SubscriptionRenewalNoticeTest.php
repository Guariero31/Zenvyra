<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\SubscriptionRenewalNotice;
use App\Enums\SubscriptionIntent;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionRenewalNoticeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionRenewalNoticeTest extends TestCase
{
    use RefreshDatabase;

    private function service(): SubscriptionRenewalNoticeService
    {
        return app(SubscriptionRenewalNoticeService::class);
    }

    public function test_does_not_show_notice_when_renewal_is_far_away(): void
    {
        $tenant = Tenant::factory()->create();
        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'ends_at' => now()->addDays(20),
        ]);

        $this->assertNull($this->service()->forTenant($tenant));
    }

    public function test_shows_commercial_renewal_notice_with_grace_days(): void
    {
        $tenant = Tenant::factory()->create();
        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'intent' => SubscriptionIntent::Commercial,
            'ends_at' => now()->addDays(3),
        ]);

        $notice = $this->service()->forTenant($tenant);

        $this->assertNotNull($notice);
        $this->assertSame('Assinatura próxima do vencimento', $notice->title);
        $this->assertStringContainsString('em 3 dias', $notice->message);
        $this->assertSame((int) config('subscriptions.grace_days'), $notice->graceDaysAfterExpiry);
    }

    public function test_shows_evaluation_notice_without_grace_days(): void
    {
        $tenant = Tenant::factory()->create();
        Subscription::factory()->evaluation()->create([
            'tenant_id' => $tenant->id,
            'trial_ends_at' => now()->addDays(2),
        ]);

        $notice = $this->service()->forTenant($tenant);

        $this->assertNotNull($notice);
        $this->assertSame('Avaliação terminando em breve', $notice->title);
        $this->assertStringContainsString('bloqueado imediatamente', $notice->message);
        $this->assertSame(0, $notice->graceDaysAfterExpiry);
    }

    public function test_shows_grace_period_notice_when_subscription_is_past_due(): void
    {
        $tenant = Tenant::factory()->create();
        Subscription::factory()->pastDue()->create([
            'tenant_id' => $tenant->id,
            'grace_ends_at' => now()->addDays(4),
        ]);

        $notice = $this->service()->forTenant($tenant);

        $this->assertNotNull($notice);
        $this->assertSame('Assinatura em tolerância', $notice->title);
        $this->assertStringContainsString('Restam', $notice->message);
        $this->assertNull($notice->graceDaysAfterExpiry);
    }

    public function test_notice_banner_view_renders_grace_information(): void
    {
        $html = view('filament.app.components.subscription-renewal-notice', [
            'notice' => new SubscriptionRenewalNotice(
                title: 'Assinatura próxima do vencimento',
                message: 'Sua assinatura vence em 2 dias (25/06/2026). Após o vencimento, você terá 5 dias de tolerância para regularizar antes do bloqueio do painel.',
                severity: 'warning',
                daysRemaining: 2,
                expiresAt: now()->addDays(2),
                graceDaysAfterExpiry: 5,
            ),
        ])->render();

        $this->assertStringContainsString('Assinatura próxima do vencimento', $html);
        $this->assertStringContainsString('Tolerância após o vencimento: 5 dias', $html);
        $this->assertStringContainsString('Falar com o suporte', $html);
    }
}
