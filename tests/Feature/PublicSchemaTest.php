<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\PublicSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_detects_public_tables_after_migrations(): void
    {
        $this->assertTrue(PublicSchema::hasTable('tenant_themes'));
        $this->assertTrue(PublicSchema::hasTable('identidade_visual'));
    }

    public function test_returns_false_for_missing_table(): void
    {
        $this->assertFalse(PublicSchema::hasTable('tabela_inexistente_xyz'));
    }
}
