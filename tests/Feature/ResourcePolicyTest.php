<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ProductPolicy;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcePolicyTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

    private User $nonManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->tenant = Tenant::create(['te_name' => 'Loja Policy Teste']);

        $this->manager = User::factory()->create();
        $this->tenant->users()->attach($this->manager, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        $this->nonManager = User::factory()->create();
        $this->tenant->users()->attach($this->nonManager, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
        ]);

        $this->actingAs($this->manager);

        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
    }

    // ──────────────────────────── CategoryPolicy ───────────────────────────────

    public function test_category_view_any_allowed_for_any_tenant_user(): void
    {
        $policy = new CategoryPolicy;

        $this->assertTrue($policy->viewAny($this->manager));
        $this->assertTrue($policy->viewAny($this->nonManager));
    }

    public function test_category_view_allowed_for_any_tenant_user(): void
    {
        $policy = new CategoryPolicy;
        $category = Category::factory()->create();

        $this->assertTrue($policy->view($this->manager, $category));
        $this->assertTrue($policy->view($this->nonManager, $category));
    }

    public function test_category_create_only_allowed_for_manager(): void
    {
        $policy = new CategoryPolicy;

        $this->assertTrue($policy->create($this->manager));
        $this->assertFalse($policy->create($this->nonManager));
    }

    public function test_category_update_only_allowed_for_manager(): void
    {
        $policy = new CategoryPolicy;
        $category = Category::factory()->create();

        $this->assertTrue($policy->update($this->manager, $category));
        $this->assertFalse($policy->update($this->nonManager, $category));
    }

    public function test_category_delete_only_allowed_for_manager(): void
    {
        $policy = new CategoryPolicy;
        $category = Category::factory()->create();

        $this->assertTrue($policy->delete($this->manager, $category));
        $this->assertFalse($policy->delete($this->nonManager, $category));
    }

    // ──────────────────────────── ProductPolicy ────────────────────────────────

    public function test_product_view_any_allowed_for_any_tenant_user(): void
    {
        $policy = new ProductPolicy;

        $this->assertTrue($policy->viewAny($this->manager));
        $this->assertTrue($policy->viewAny($this->nonManager));
    }

    public function test_product_view_allowed_for_any_tenant_user(): void
    {
        $policy = new ProductPolicy;
        $product = Product::factory()->create();

        $this->assertTrue($policy->view($this->manager, $product));
        $this->assertTrue($policy->view($this->nonManager, $product));
    }

    public function test_product_create_only_allowed_for_manager(): void
    {
        $policy = new ProductPolicy;

        $this->assertTrue($policy->create($this->manager));
        $this->assertFalse($policy->create($this->nonManager));
    }

    public function test_product_update_only_allowed_for_manager(): void
    {
        $policy = new ProductPolicy;
        $product = Product::factory()->create();

        $this->assertTrue($policy->update($this->manager, $product));
        $this->assertFalse($policy->update($this->nonManager, $product));
    }

    public function test_product_delete_only_allowed_for_manager(): void
    {
        $policy = new ProductPolicy;
        $product = Product::factory()->create();

        $this->assertTrue($policy->delete($this->manager, $product));
        $this->assertFalse($policy->delete($this->nonManager, $product));
    }

    // ──────────────────────────── CustomerPolicy ───────────────────────────────

    public function test_customer_view_any_allowed_for_any_tenant_user(): void
    {
        $policy = new CustomerPolicy;

        $this->assertTrue($policy->viewAny($this->manager));
        $this->assertTrue($policy->viewAny($this->nonManager));
    }

    public function test_customer_view_allowed_for_any_tenant_user(): void
    {
        $policy = new CustomerPolicy;
        $customer = Customer::factory()->create();

        $this->assertTrue($policy->view($this->manager, $customer));
        $this->assertTrue($policy->view($this->nonManager, $customer));
    }

    public function test_customer_create_only_allowed_for_manager(): void
    {
        $policy = new CustomerPolicy;

        $this->assertTrue($policy->create($this->manager));
        $this->assertFalse($policy->create($this->nonManager));
    }

    public function test_customer_update_only_allowed_for_manager(): void
    {
        $policy = new CustomerPolicy;
        $customer = Customer::factory()->create();

        $this->assertTrue($policy->update($this->manager, $customer));
        $this->assertFalse($policy->update($this->nonManager, $customer));
    }

    public function test_customer_delete_only_allowed_for_manager(): void
    {
        $policy = new CustomerPolicy;
        $customer = Customer::factory()->create();

        $this->assertTrue($policy->delete($this->manager, $customer));
        $this->assertFalse($policy->delete($this->nonManager, $customer));
    }

    // ──────────────────────────── Outsider ─────────────────────────────────────

    public function test_user_not_linked_to_tenant_cannot_view_any_resource(): void
    {
        $outsider = User::factory()->create();

        $this->assertFalse((new CategoryPolicy)->viewAny($outsider));
        $this->assertFalse((new ProductPolicy)->viewAny($outsider));
        $this->assertFalse((new CustomerPolicy)->viewAny($outsider));
    }
}
