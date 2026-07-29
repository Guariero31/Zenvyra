<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        Mail::fake();
    }

    // ── Product ───────────────────────────────────────────────────────────────

    public function test_creating_product_generates_activity_log(): void
    {
        $product = Product::factory()->create(['title' => 'Camiseta Azul']);

        $log = ActivityLog::where('log_name', 'produto')
            ->where('event', 'created')
            ->where('subject_type', Product::class)
            ->where('subject_id', $product->id)
            ->first();

        $this->assertNotNull($log, 'Log de criação de produto não foi registrado.');
    }

    public function test_updating_product_price_generates_activity_log(): void
    {
        $product = Product::factory()->create(['price' => '50.00']);

        $product->update(['price' => '75.00']);

        $log = ActivityLog::where('log_name', 'produto')
            ->where('event', 'updated')
            ->where('subject_id', $product->id)
            ->first();

        $this->assertNotNull($log, 'Log de atualização de produto não foi registrado.');
        $this->assertEquals('75.00', data_get($log->properties, 'attributes.price'));
        $this->assertEquals('50.00', data_get($log->properties, 'old.price'));
    }

    public function test_unchanged_product_does_not_generate_activity_log(): void
    {
        $product = Product::factory()->create(['title' => 'Sem mudança']);

        ActivityLog::query()->delete(); // limpa o log de criação

        $product->update(['title' => 'Sem mudança']); // mesmo valor

        $this->assertDatabaseCount('activity_log', 0);
    }

    // ── Order ─────────────────────────────────────────────────────────────────

    public function test_updating_order_status_generates_activity_log(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $order->update(['status' => 'paid']);

        $log = ActivityLog::where('log_name', 'pedido')
            ->where('event', 'updated')
            ->where('subject_id', $order->id)
            ->first();

        $this->assertNotNull($log, 'Log de atualização de pedido não foi registrado.');
        $this->assertEquals('paid', data_get($log->properties, 'attributes.status'));
    }

    // ── Customer ──────────────────────────────────────────────────────────────

    public function test_creating_customer_generates_activity_log(): void
    {
        $customer = Customer::factory()->create(['name' => 'João Silva']);

        $log = ActivityLog::where('log_name', 'cliente')
            ->where('event', 'created')
            ->where('subject_id', $customer->id)
            ->first();

        $this->assertNotNull($log, 'Log de criação de cliente não foi registrado.');
    }

    public function test_causer_is_recorded_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();

        $log = ActivityLog::where('log_name', 'produto')
            ->where('event', 'created')
            ->where('subject_id', $product->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($user->id, $log->causer_id);
        $this->assertEquals(User::class, $log->causer_type);
    }

    public function test_causer_is_null_when_not_authenticated(): void
    {
        $product = Product::factory()->create();

        $log = ActivityLog::where('log_name', 'produto')
            ->where('subject_id', $product->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertNull($log->causer_id);
    }
}
