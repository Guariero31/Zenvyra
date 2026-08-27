<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantPortalRole;
use App\Mail\LowStockAlertMail;
use App\Mail\OrderPaidMail;
use App\Mail\SubscriptionExpiredMail;
use App\Mail\SubscriptionExpiringMail;
use App\Mail\WelcomeMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionManager;
use App\Services\TenantTeamService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);
    }

    // ── WelcomeMail ───────────────────────────────────────────────────────────

    public function test_welcome_mail_is_sent_when_admin_is_added_to_tenant(): void
    {
        Mail::fake();

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create();

        /** @var TenantTeamService $service */
        $service = app(TenantTeamService::class);

        $service->addMember($tenant, [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        Mail::assertSent(WelcomeMail::class, function (WelcomeMail $mail) use ($user, $tenant): bool {
            return $mail->hasTo($user->email)
                && $mail->userName === $user->name
                && $mail->storeName === $tenant->displayName();
        });
    }

    public function test_welcome_mail_is_not_sent_for_non_admin_roles(): void
    {
        Mail::fake();

        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create();

        /** @var TenantTeamService $service */
        $service = app(TenantTeamService::class);

        // Garante que haja ao menos um admin (exigência da loja)
        $service->addMember($tenant, [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => 'password',
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        Mail::fake(); // reset após o admin ser adicionado

        $member = User::factory()->create();
        $service->addMember($tenant, [
            'name' => $member->name,
            'email' => $member->email,
            'password' => 'password',
            'tu_papel' => TenantPortalRole::Member->value,
        ]);

        Mail::assertNotSent(WelcomeMail::class);
    }

    // ── SubscriptionExpiredMail ───────────────────────────────────────────────

    public function test_expired_mail_is_sent_to_admin_when_subscription_expires(): void
    {
        Mail::fake();

        $plan = Plan::factory()->create(['name' => 'Starter']);
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create();
        $tenant->users()->attach($admin, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
            'tu_commission_rate' => 0,
        ]);

        $subscription = Subscription::factory()->trialing()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        /** @var SubscriptionManager $manager */
        $manager = app(SubscriptionManager::class);
        $manager->expire($subscription);

        Mail::assertSent(SubscriptionExpiredMail::class, function (SubscriptionExpiredMail $mail) use ($admin, $tenant, $plan): bool {
            return $mail->hasTo($admin->email)
                && $mail->storeName === $tenant->displayName()
                && $mail->planName === $plan->name;
        });
    }

    public function test_expired_mail_is_not_sent_when_tenant_has_no_admins(): void
    {
        Mail::fake();

        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->trialing()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        /** @var SubscriptionManager $manager */
        $manager = app(SubscriptionManager::class);
        $manager->expire($subscription);

        Mail::assertNotSent(SubscriptionExpiredMail::class);
    }

    // ── NotifyExpiringSubscriptions command ───────────────────────────────────

    public function test_notify_expiring_command_sends_mail_for_expiring_subscriptions(): void
    {
        Mail::fake();

        $plan = Plan::factory()->create(['name' => 'Pro']);
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create();
        $tenant->users()->attach($admin, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
            'tu_commission_rate' => 0,
        ]);

        $noticeDays = (int) config('subscriptions.renewal_notice_days', 7);

        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(25),
            'ends_at' => now()->addDays($noticeDays - 1),
            'next_billing_at' => now()->addDays($noticeDays - 1),
        ]);

        $this->artisan('subscriptions:notify-expiring')->assertSuccessful();

        Mail::assertSent(SubscriptionExpiringMail::class, function (SubscriptionExpiringMail $mail) use ($admin): bool {
            return $mail->hasTo($admin->email);
        });
    }

    public function test_notify_expiring_command_does_not_send_for_subscriptions_outside_window(): void
    {
        Mail::fake();

        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create();
        $tenant->users()->attach($admin, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
            'tu_commission_rate' => 0,
        ]);

        $noticeDays = (int) config('subscriptions.renewal_notice_days', 7);

        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDays(25),
            'ends_at' => now()->addDays($noticeDays + 10), // fora da janela
            'next_billing_at' => now()->addDays($noticeDays + 10),
        ]);

        $this->artisan('subscriptions:notify-expiring')->assertSuccessful();

        Mail::assertNotSent(SubscriptionExpiringMail::class);
    }

    // ── OrderPaidMail ─────────────────────────────────────────────────────────

    public function test_order_paid_mail_is_sent_to_customer_when_order_becomes_paid(): void
    {
        Mail::fake();

        $tenant = Tenant::factory()->create();
        TenantContext::set($tenant);

        $customer = Customer::factory()->create(['email' => 'cliente@teste.com']);
        $product = Product::factory()->create(['title' => 'Camiseta', 'stock' => 10]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending,
            'total_amount' => '99.90',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_at_sale' => '49.95',
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        Mail::assertSent(OrderPaidMail::class, function (OrderPaidMail $mail) use ($customer, $order): bool {
            return $mail->hasTo($customer->email)
                && $mail->orderId === $order->id
                && $mail->customerName === $customer->name;
        });

        TenantContext::clear();
    }

    public function test_order_paid_mail_is_not_sent_when_customer_has_no_email(): void
    {
        Mail::fake();

        $tenant = Tenant::factory()->create();
        TenantContext::set($tenant);

        $customer = Customer::factory()->create(['email' => null]);
        $product = Product::factory()->create(['stock' => 10]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending,
            'total_amount' => '50.00',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_sale' => '50.00',
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        Mail::assertNotSent(OrderPaidMail::class);

        TenantContext::clear();
    }

    // ── LowStockAlertMail ─────────────────────────────────────────────────────

    public function test_low_stock_alert_is_sent_when_stock_drops_to_threshold(): void
    {
        Mail::fake();

        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create();
        $tenant->users()->attach($admin, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
            'tu_commission_rate' => 0,
        ]);
        TenantContext::set($tenant);

        $product = Product::factory()->create([
            'title' => 'Produto Critico',
            'stock' => 3,
            'low_stock_threshold' => 3, // já no limite
        ]);

        $customer = Customer::factory()->create(['email' => 'c@c.com']);
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending,
            'total_amount' => '30.00',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_sale' => '30.00',
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        Mail::assertSent(LowStockAlertMail::class, function (LowStockAlertMail $mail) use ($admin, $product): bool {
            return $mail->hasTo($admin->email)
                && $mail->productName === $product->title;
        });

        TenantContext::clear();
    }

    public function test_low_stock_alert_is_not_sent_when_threshold_is_not_set(): void
    {
        Mail::fake();

        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create();
        $tenant->users()->attach($admin, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
            'tu_commission_rate' => 0,
        ]);
        TenantContext::set($tenant);

        $product = Product::factory()->create([
            'stock' => 1,
            'low_stock_threshold' => 0, // 0 = sem alerta configurado
        ]);

        $customer = Customer::factory()->create(['email' => 'c@c.com']);
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending,
            'total_amount' => '10.00',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_sale' => '10.00',
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        Mail::assertNotSent(LowStockAlertMail::class);

        TenantContext::clear();
    }
}
