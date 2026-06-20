<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\IdentidadeVisual;
use App\Models\IdentidadeVisualHistorico;
use App\Models\Tenant;
use App\Models\TenantTheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentidadeVisualHistoricoTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_nao_cria_registro_quando_nada_mudou(): void
    {
        $estado = ['cor_primaria' => '#4F46E5', 'cor_sucesso' => '#10B981'];

        IdentidadeVisualHistorico::registrar('master', $estado, $estado);

        $this->assertDatabaseCount('identidade_visual_historico', 0);
    }

    public function test_registrar_cria_registro_com_apenas_campos_alterados(): void
    {
        $anterior = ['cor_primaria' => '#4F46E5', 'cor_sucesso' => '#10B981'];
        $novo = ['cor_primaria' => '#7C3AED', 'cor_sucesso' => '#10B981'];

        IdentidadeVisualHistorico::registrar('master', $anterior, $novo);

        $this->assertDatabaseCount('identidade_visual_historico', 1);

        $registro = IdentidadeVisualHistorico::first();
        $this->assertSame('master', $registro->painel);
        $this->assertArrayHasKey('cor_primaria', $registro->alteracoes);
        $this->assertArrayNotHasKey('cor_sucesso', $registro->alteracoes);
        $this->assertSame('#4F46E5', $registro->alteracoes['cor_primaria']['de']);
        $this->assertSame('#7C3AED', $registro->alteracoes['cor_primaria']['para']);
    }

    public function test_registrar_associa_tenant_id_para_painel_tenant(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja']);
        $anterior = ['cor_primaria_light' => '#4F46E5'];
        $novo = ['cor_primaria_light' => '#FF5733'];

        IdentidadeVisualHistorico::registrar('tenant', $anterior, $novo, $tenant->id);

        $this->assertDatabaseHas('identidade_visual_historico', [
            'painel' => 'tenant',
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_registrar_associa_usuario_autenticado(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        IdentidadeVisualHistorico::registrar('master', ['cor_primaria' => '#000000'], ['cor_primaria' => '#FFFFFF']);

        $this->assertDatabaseHas('identidade_visual_historico', ['user_id' => $user->id]);
    }

    public function test_cor_normalizada_antes_de_salvar_identidade_visual(): void
    {
        $iv = IdentidadeVisual::obterOuCriar();
        $iv->cor_primaria = 'rgba(124, 58, 237, 1)';
        $iv->save();

        $this->assertDatabaseHas('identidade_visual', ['cor_primaria' => '#7c3aed']);
    }

    public function test_cor_normalizada_antes_de_salvar_tenant_theme(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja']);
        $theme = TenantTheme::obterOuCriarParaTenant($tenant->id);
        $theme->cor_primaria_light = 'rgba(99, 102, 241, 1)';
        $theme->save();

        $this->assertDatabaseHas('tenant_themes', ['cor_primaria_light' => '#6366f1']);
    }

    public function test_historico_nao_tem_timestamps_automaticos(): void
    {
        $anterior = ['cor_primaria' => '#000000'];
        $novo = ['cor_primaria' => '#FFFFFF'];

        IdentidadeVisualHistorico::registrar('master', $anterior, $novo);

        $registro = IdentidadeVisualHistorico::first();
        $this->assertNotNull($registro->criado_em);
        $this->assertNull($registro->getAttribute('updated_at'));
    }
}
