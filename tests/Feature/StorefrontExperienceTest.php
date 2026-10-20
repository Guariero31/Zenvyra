<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Tenant;
use App\Models\TenantTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontExperienceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations/tenant', '--no-interaction' => true]);
        $this->tenant = Tenant::factory()->create(['te_slug' => 'loja-ux', 'te_ativo' => true]);
        TenantTheme::obterOuCriarParaTenant($this->tenant->getKey())->update([
            'nome_loja' => 'Loja UX',
            'cor_primaria_light' => '#B6F542',
            'store_config' => ['whatsapp' => '+5565999999999'],
        ]);
    }

    public function test_store_has_search_mobile_navigation_and_readable_brand_buttons(): void
    {
        $this->get('/loja-ux')->assertOk()
            ->assertSee('Loja UX')
            ->assertSee('role="search"', false)
            ->assertSee('name="busca"', false)
            ->assertSee('Abrir menu da loja')
            ->assertSee('--primary: #B6F542;', false)
            ->assertSee('--on-primary: #111111;', false);
    }

    public function test_search_returns_only_matching_active_products(): void
    {
        Product::factory()->create(['title' => 'Camiseta Essencial']);
        Product::factory()->create(['title' => 'Calça Jeans']);
        Product::factory()->create(['title' => 'Camiseta Oculta', 'status' => 'inactive']);

        $this->get('/loja-ux/catalogo?busca=camiseta')->assertOk()
            ->assertSee('Camiseta Essencial')->assertDontSee('Calça Jeans')->assertDontSee('Camiseta Oculta')
            ->assertSee('1 produto encontrado')
            ->assertViewHas('search', 'camiseta');
    }

    public function test_search_category_sort_and_pagination_work_together(): void
    {
        $category = Category::factory()->create(['slug' => 'roupas']);
        Product::factory()->count(13)->create(['category_id' => $category->id, 'title' => 'Camiseta Teste', 'price' => '80.00']);
        Product::factory()->create(['title' => 'Camiseta Outra Categoria']);
        Product::factory()->create(['category_id' => $category->id, 'title' => 'Calça']);

        $response = $this->get('/loja-ux/catalogo?busca=Camiseta&categoria=roupas&ordenar=menor_preco');

        $response->assertOk()->assertSee('13 produtos encontrados')->assertDontSee('Camiseta Outra Categoria')
            ->assertViewHas('products', fn ($products): bool => $products->total() === 13 && $products->count() === 12);
        $this->assertStringContainsString('busca=Camiseta', $response->viewData('products')->nextPageUrl());
        $this->assertStringContainsString('categoria=roupas', $response->viewData('products')->nextPageUrl());
        $this->assertStringContainsString('ordenar=menor_preco', $response->viewData('products')->nextPageUrl());
    }

    public function test_search_sorts_by_price_and_preserves_the_selected_category_in_links(): void
    {
        $category = Category::factory()->create(['name' => 'Acessórios', 'slug' => 'acessorios']);
        Product::factory()->create(['category_id' => $category->id, 'title' => 'Bolsa Grande', 'price' => '120.00']);
        Product::factory()->create(['category_id' => $category->id, 'title' => 'Bolsa Pequena', 'price' => '50.00']);

        $this->get('/loja-ux/catalogo?busca=Bolsa&categoria=acessorios&ordenar=menor_preco')->assertOk()
            ->assertSeeInOrder(['Bolsa Pequena', 'Bolsa Grande'])
            ->assertSee('name="busca" value="Bolsa"', false);
    }

    public function test_empty_search_offers_a_way_back_and_escapes_user_text(): void
    {
        $this->get('/loja-ux/catalogo?busca='.rawurlencode('<script>alert(1)</script>'))->assertOk()
            ->assertSee('Nenhum produto encontrado.')
            ->assertSee('Ver todos os produtos')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_invalid_search_parameters_are_rejected(): void
    {
        $this->get('/loja-ux/catalogo?busca[]=teste&ordenar=invalido')->assertRedirect()
            ->assertSessionHasErrors(['busca', 'ordenar']);
        $this->get('/loja-ux/catalogo?busca='.str_repeat('a', 121))->assertRedirect()
            ->assertSessionHasErrors('busca');
    }

    public function test_variation_buttons_expose_exact_cent_values_and_unavailable_options(): void
    {
        $product = Product::factory()->create(['has_variations' => true, 'price' => '129.90']);
        ProductVariation::factory()->create(['product_id' => $product->id, 'name' => 'Azul', 'price' => '159.99', 'stock' => 3]);
        ProductVariation::factory()->create(['product_id' => $product->id, 'name' => 'Verde', 'price' => null, 'stock' => 0]);

        $this->get('/loja-ux/produto/'.$product->id)->assertOk()
            ->assertSee('Escolha uma opção')->assertSee('data-price-cents="15999"', false)
            ->assertSee('data-price-cents="12990"', false)->assertSee('data-variation="Azul"', false)
            ->assertSee('aria-pressed="false"', false)->assertSee('disabled', false)
            ->assertSee('data-whatsapp', false);
    }

    public function test_disabled_product_variations_are_not_offered(): void
    {
        $product = Product::factory()->create(['has_variations' => false]);
        ProductVariation::factory()->create(['product_id' => $product->id, 'name' => 'Opção antiga']);

        $this->get('/loja-ux/produto/'.$product->id)->assertOk()->assertDontSee('data-variation=', false);
    }

    public function test_unavailable_product_invites_an_availability_enquiry(): void
    {
        $product = Product::factory()->create(['stock' => 0]);

        $this->get('/loja-ux/produto/'.$product->id)->assertOk()
            ->assertSee('Fora de estoque')->assertSee('Consultar disponibilidade')
            ->assertDontSee('Pedir pelo WhatsApp');
    }

    public function test_landing_and_registration_share_the_light_editorial_design(): void
    {
        $this->get('/')->assertOk()->assertSee('css/marketing.css', false)
            ->assertSee('Seu negócio cresce.')
            ->assertSee('Prévia ilustrativa do painel')
            ->assertSee('Menos tarefas repetidas.')
            ->assertSee('Dúvidas frequentes')
            ->assertSeeInOrder(['Experimentar a Zenvyra', 'Catálogo online', 'Menos tarefas repetidas.', 'Funcionalidades'])
            ->assertDontSee('de uptime');
        $this->get('/cadastro')->assertOk()->assertSee('css/marketing.css', false)
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('data-submit-label="Criando sua loja…"', false)
            ->assertSee('Termos de uso')->assertSee('Política de privacidade');
    }

    public function test_inactive_categories_are_not_presented_as_filters(): void
    {
        $category = Category::factory()->create(['name' => 'Categoria Oculta']);
        Product::factory()->create(['category_id' => $category->id, 'status' => 'inactive']);

        $this->get('/loja-ux/catalogo')->assertOk()->assertDontSee('Categoria Oculta');
    }

    public function test_plan_cards_use_named_features_and_keep_plan_context_in_registration(): void
    {
        Plan::factory()->create([
            'name' => 'Plano Teste', 'slug' => 'plano-teste', 'is_active' => true,
            'max_users' => 2, 'max_products' => 500,
            'features' => ['usuarios' => '2', 'produtos' => '500', 'suporte' => 'E-mail'],
        ]);

        $this->get('/')->assertOk()->assertSee('Suporte: E-mail')->assertSee('plano=plano-teste', false)
            ->assertDontSee('<li>500</li>', false)->assertDontSee('<li>2</li>', false);
        $this->get('/cadastro?plano=plano-teste')->assertOk()->assertSee('Plano Teste')
            ->assertSee('Você começa pela avaliação, antes de contratar um plano.');
        $this->get('/cadastro?plano=inexistente')->assertOk()->assertDontSee('Seu interesse:');
    }
}
