<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O rodapé da vitrine pública deve exibir as informações de contato
 * (WhatsApp, e-mail, endereço) salvas em Configurações > Site Público,
 * sem precisar acessar a página de Contato para vê-las.
 */
class StoreFooterContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);
    }

    public function test_footer_shows_contact_info_saved_in_admin(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Footer', 'te_slug' => 'loja-footer', 'te_ativo' => true]);
        $theme = TenantTheme::obterOuCriarParaTenant($tenant->getKey());
        $theme->update(['store_config' => [
            'whatsapp' => '+55 51 9 9999-9999',
            'email' => 'contato@lojademo.com.br',
            'endereco' => 'Av. Exemplo de Loja - Vendas. Brasilia-DF. 90000-000',
        ]]);

        $response = $this->get('/'.$tenant->te_slug);

        $response->assertOk();
        $response->assertSee('+55 51 9 9999-9999');
        $response->assertSee('contato@lojademo.com.br');
        $response->assertSee('Av. Exemplo de Loja - Vendas. Brasilia-DF. 90000-000');
    }

    public function test_footer_hides_contact_column_when_nothing_configured(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Sem Contato', 'te_slug' => 'loja-sem-contato', 'te_ativo' => true]);
        TenantTheme::obterOuCriarParaTenant($tenant->getKey());

        $response = $this->get('/'.$tenant->te_slug);

        $response->assertOk();
        $response->assertDontSee('footer__col-title">Contato', escape: false);
    }
}
