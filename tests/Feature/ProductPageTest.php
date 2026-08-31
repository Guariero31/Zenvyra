<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Tenant;
use App\Models\TenantTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => 'database/migrations/tenant']);

        $this->tenant = Tenant::factory()->create(['te_slug' => 'loja-prod', 'te_ativo' => true]);

        TenantTheme::firstOrCreate(
            ['tenant_id' => $this->tenant->getKey()],
            TenantTheme::defaults($this->tenant->getKey()),
        );
    }

    public function test_product_page_renders_for_active_product(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'title' => 'Camiseta Legal']);

        $this->get("/{$this->tenant->te_slug}/produto/{$product->id}")
            ->assertOk()
            ->assertSee('Camiseta Legal');
    }

    public function test_inactive_product_returns_404(): void
    {
        $product = Product::factory()->create(['status' => 'inactive']);

        $this->get("/{$this->tenant->te_slug}/produto/{$product->id}")
            ->assertNotFound();
    }

    public function test_nonexistent_product_returns_404(): void
    {
        $this->get("/{$this->tenant->te_slug}/produto/99999")
            ->assertNotFound();
    }

    public function test_product_page_shows_price(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 129.90]);

        $this->get("/{$this->tenant->te_slug}/produto/{$product->id}")
            ->assertOk()
            ->assertSee('129,90');
    }

    public function test_product_page_shows_description(): void
    {
        $product = Product::factory()->create([
            'status' => 'active',
            'description' => 'Descrição detalhada do produto para teste.',
        ]);

        $this->get("/{$this->tenant->te_slug}/produto/{$product->id}")
            ->assertOk()
            ->assertSee('Descrição detalhada do produto para teste.');
    }

    public function test_product_page_shows_variations(): void
    {
        $product = Product::factory()->create(['status' => 'active']);
        ProductVariation::factory()->create(['product_id' => $product->id, 'name' => 'P', 'stock' => 5]);
        ProductVariation::factory()->create(['product_id' => $product->id, 'name' => 'M', 'stock' => 10]);

        $this->get("/{$this->tenant->te_slug}/produto/{$product->id}")
            ->assertOk()
            ->assertSee('P')
            ->assertSee('M');
    }

    public function test_product_page_shows_category_breadcrumb(): void
    {
        $category = Category::factory()->create(['name' => 'Roupas']);
        $product = Product::factory()->create(['status' => 'active', 'category_id' => $category->id]);

        $this->get("/{$this->tenant->te_slug}/produto/{$product->id}")
            ->assertOk()
            ->assertSee('Roupas');
    }

    public function test_product_page_shows_whatsapp_button_when_configured(): void
    {
        /** @var TenantTheme $theme */
        $theme = TenantTheme::where('tenant_id', $this->tenant->getKey())->first();
        $theme->update(['store_config' => ['whatsapp' => '+5511999999999']]);

        $product = Product::factory()->create(['status' => 'active']);

        $this->get("/{$this->tenant->te_slug}/produto/{$product->id}")
            ->assertOk()
            ->assertSee('Pedir pelo WhatsApp');
    }

    public function test_product_page_hides_whatsapp_button_when_not_configured(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $this->get("/{$this->tenant->te_slug}/produto/{$product->id}")
            ->assertOk()
            ->assertDontSee('Pedir pelo WhatsApp');
    }

    public function test_related_products_shown_from_same_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'category_id' => $category->id]);
        $related = Product::factory()->create(['status' => 'active', 'category_id' => $category->id, 'title' => 'Produto Relacionado']);

        $this->get("/{$this->tenant->te_slug}/produto/{$product->id}")
            ->assertOk()
            ->assertSee('Produto Relacionado');
    }

    public function test_inactive_tenant_returns_404(): void
    {
        $inactive = Tenant::factory()->create(['te_slug' => 'loja-off', 'te_ativo' => false]);
        $product = Product::factory()->create(['status' => 'active']);

        $this->get("/{$inactive->te_slug}/produto/{$product->id}")
            ->assertNotFound();
    }
}
