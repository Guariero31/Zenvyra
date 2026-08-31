<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A coluna "Produtos" na listagem de Categorias conta itens de catálogo:
 * cada variação de um produto conta como um item separado, e um produto
 * sem variações conta como 1 — não é uma contagem simples de linhas da
 * tabela `products`.
 */
class CategoryProductsCountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $tenant = Tenant::create(['te_name' => 'Loja Categoria Teste']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();
        session(['tenant_id' => $tenant->getKey()]);
    }

    public function test_catalog_items_count_sums_variations_and_products_without_variations(): void
    {
        $category = Category::create(['name' => 'Bebidas', 'slug' => 'bebidas']);

        $withVariations = Product::create([
            'category_id' => $category->id,
            'title' => 'Com Variação',
            'sku' => 'SKU-COM-VAR',
            'price' => '10.00',
            'stock' => 0,
            'status' => 'active',
        ]);
        $withVariations->variations()->createMany([
            ['name' => 'M', 'stock' => 5],
            ['name' => 'G', 'stock' => 3],
            ['name' => 'GG', 'stock' => 2],
        ]);

        Product::create([
            'category_id' => $category->id,
            'title' => 'Sem Variação',
            'sku' => 'SKU-SEM-VAR',
            'price' => '20.00',
            'stock' => 10,
            'status' => 'active',
        ]);

        $fresh = Category::with(['products' => fn ($query) => $query->withCount('variations')])
            ->find($category->id);

        // 3 variações + 1 produto sem variação = 4
        $this->assertSame(4, $fresh->catalogItemsCount());
    }

    public function test_categories_table_shows_the_correct_item_count(): void
    {
        $category = Category::create(['name' => 'Bebidas', 'slug' => 'bebidas']);

        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Com Variação',
            'sku' => 'SKU-COM-VAR',
            'price' => '10.00',
            'stock' => 0,
            'status' => 'active',
        ]);
        $product->variations()->createMany([
            ['name' => 'M', 'stock' => 5],
            ['name' => 'G', 'stock' => 3],
        ]);

        Livewire::test(ListCategories::class)
            ->assertTableColumnStateSet('products_count', 2, record: $category);
    }
}
