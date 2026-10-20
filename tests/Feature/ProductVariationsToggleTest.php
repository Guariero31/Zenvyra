<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Pages\PdvPage;
use App\Filament\App\Resources\Products\Pages\CreateProduct;
use App\Filament\App\Resources\Products\Pages\EditProduct;
use App\Filament\App\Resources\Products\RelationManagers\VariationsRelationManager;
use App\Filament\App\Widgets\SalesStatsOverview;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cobre os 4 casos de uso do toggle "produto tem variações?":
 * 1. Com variações: seção Estoque do produto some, variação pede nome +
 *    estoque + alerta de estoque baixo.
 * 2. Sem variações: seção Estoque fica visível, aba Variações não aparece.
 * 3. Com variações: cada variação pode ter preço próprio.
 * 4. Com variações: cada variação pode ter imagens próprias.
 */
class ProductVariationsToggleTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->tenant = Tenant::create(['te_name' => 'Loja Variações Teste']);
        $user = User::factory()->create();
        $this->tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
        Filament::bootCurrentPanel();
        session(['tenant_id' => $this->tenant->getKey()]);

        $this->category = Category::create(['name' => 'Categoria', 'slug' => 'categoria']);
    }

    public function test_creating_a_product_with_has_variations_off_keeps_stock_section_and_hides_variations_tab(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'title' => 'Produto Simples',
                'category_id' => $this->category->id,
                'price' => '50.00',
                'has_variations' => false,
                'stock' => 20,
                'low_stock_threshold' => 5,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('title', 'Produto Simples')->firstOrFail();

        $this->assertFalse($product->hasVariations());
        $this->assertSame(20, $product->stock);
        $this->assertFalse(VariationsRelationManager::canViewForRecord($product, EditProduct::class));
    }

    public function test_creating_a_product_with_has_variations_on_discards_stock_fields_and_reveals_variations_tab(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'title' => 'Produto Com Variações',
                'category_id' => $this->category->id,
                'price' => '50.00',
                'has_variations' => true,
                // Estoque fields are still technically fillable in the test
                // helper, but should not be dehydrated/saved once the
                // toggle is on.
                'stock' => 999,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('title', 'Produto Com Variações')->firstOrFail();

        $this->assertTrue($product->hasVariations());
        $this->assertSame(0, $product->stock, 'stock não deveria ser salvo quando has_variations está ativo');
        $this->assertTrue(VariationsRelationManager::canViewForRecord($product, EditProduct::class));
    }

    public function test_variation_form_requires_name_stock_and_low_stock_threshold(): void
    {
        $product = $this->createVariableProduct();

        $component = Livewire::test(VariationsRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ]);

        $fields = [];
        foreach ($component->instance()->form($schema = app(Schema::class))->getComponents() as $field) {
            $fields[$field->getName()] = $field;
        }

        $this->assertTrue($fields['name']->isRequired());
        $this->assertTrue($fields['stock']->isRequired());
        $this->assertTrue($fields['low_stock_threshold']->isRequired());
        $this->assertFalse($fields['price']->isRequired());
    }

    public function test_variation_created_directly_stores_name_stock_and_low_stock_threshold(): void
    {
        $product = $this->createVariableProduct();

        $variation = $product->variations()->create([
            'name' => 'M',
            'stock' => 10,
            'low_stock_threshold' => 3,
        ]);

        $this->assertSame('M', $variation->name);
        $this->assertSame(10, $variation->stock);
        $this->assertSame(3, $variation->low_stock_threshold);
        $this->assertNull($variation->price);
    }

    public function test_variation_can_have_its_own_price_overriding_the_product_price(): void
    {
        $product = $this->createVariableProduct(price: '100.00');

        $variation = $product->variations()->create([
            'name' => 'GG',
            'stock' => 5,
            'low_stock_threshold' => 2,
            'price' => '120.00',
        ]);

        $this->assertSame(120.0, $variation->effectivePrice($product));

        $variationWithoutOverride = $product->variations()->create([
            'name' => 'P',
            'stock' => 5,
            'low_stock_threshold' => 2,
        ]);

        $this->assertSame(100.0, $variationWithoutOverride->effectivePrice($product));
    }

    public function test_pdv_uses_the_variation_price_when_it_has_an_override(): void
    {
        $product = $this->createVariableProduct(price: '100.00');
        $variation = $product->variations()->create([
            'name' => 'GG',
            'stock' => 5,
            'low_stock_threshold' => 2,
            'price' => '150.00',
        ]);

        Livewire::test(PdvPage::class)
            ->call('addProductVariation', $product->id, $variation->id);

        $cart = Livewire::test(PdvPage::class)
            ->call('addProductVariation', $product->id, $variation->id)
            ->get('cart');

        $this->assertSame('150.00', $cart[0]['price']);
    }

    public function test_variation_can_have_its_own_images(): void
    {
        $product = $this->createVariableProduct();
        $variation = $product->variations()->create([
            'name' => 'M',
            'stock' => 10,
            'low_stock_threshold' => 3,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'product_variation_id' => $variation->id,
            'image_path' => 'products/variation-image.jpg',
        ]);

        $this->assertSame(1, $variation->images()->count());
        $this->assertSame(0, $product->images()->count(), 'imagem da variação não deve aparecer nas imagens do produto pai');
        $this->assertTrue($variation->images()->first()->is_primary, 'primeira imagem da variação deve virar capa automaticamente');
    }

    public function test_dashboard_low_stock_kpi_checks_each_variation_threshold_not_the_parent_product(): void
    {
        // Produto com variações: campos de estoque do pai ficam com o valor
        // padrão (stock=0, threshold=5), o que faria a query antiga (baseada
        // só no produto pai) contar isso como "estoque baixo" incorretamente.
        $lowStockVariable = $this->createVariableProduct();
        $lowStockVariable->variations()->create(['name' => 'M', 'stock' => 2, 'low_stock_threshold' => 5]);

        $healthyVariable = $this->createVariableProduct();
        $healthyVariable->variations()->create(['name' => 'M', 'stock' => 50, 'low_stock_threshold' => 5]);

        $component = Livewire::test(SalesStatsOverview::class)->instance();
        $method = new \ReflectionMethod($component, 'getStats');
        $method->setAccessible(true);
        $stats = $method->invoke($component);

        $lowStockStat = collect($stats)->first(fn ($stat) => str_contains((string) $stat->getLabel(), 'Produtos para repor'));

        $this->assertNotNull($lowStockStat);
        $this->assertStringContainsString('1', (string) $lowStockStat->getValue());
    }

    private function createVariableProduct(string $price = '50.00'): Product
    {
        return Product::create([
            'category_id' => $this->category->id,
            'title' => 'Produto Variável',
            'sku' => 'SKU-VAR-'.uniqid(),
            'price' => $price,
            'stock' => 0,
            'status' => 'active',
            'has_variations' => true,
        ]);
    }
}
