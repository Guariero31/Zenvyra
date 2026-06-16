<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();
        $slug = Str::slug($name);

        return [
            'te_name' => $name,
            'te_slug' => $slug,
            'te_schema' => 'tenant_'.str_replace('-', '_', $slug),
            'te_ativo' => true,
            'te_gestao_settings' => null,
        ];
    }
}
