<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant;
use Filament\Facades\Filament;

/**
 * Preferências operacionais da loja, persistidas em te_gestao_settings.
 */
final class TenantGestaoSettings
{
    /** @param  array<string, bool>  $values */
    private function __construct(private array $values) {}

    public static function defaults(): self
    {
        return new self([
            'exige_telefone_cliente' => false,
            'exige_email_cliente' => false,
            'modulo_vendedores_ativo' => true,
            'permitir_estoque_negativo' => false,
        ]);
    }

    /** @param  array<string, mixed>|null  $raw */
    public static function from(?array $raw): self
    {
        $merged = array_merge(self::defaults()->values, $raw ?? []);

        return new self([
            'exige_telefone_cliente' => (bool) ($merged['exige_telefone_cliente'] ?? false),
            'exige_email_cliente' => (bool) ($merged['exige_email_cliente'] ?? false),
            'modulo_vendedores_ativo' => (bool) ($merged['modulo_vendedores_ativo'] ?? true),
            'permitir_estoque_negativo' => (bool) ($merged['permitir_estoque_negativo'] ?? false),
        ]);
    }

    public static function forCurrentTenant(): self
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant
            ? $tenant->gestaoSettings()
            : self::defaults();
    }

    public function exigeTelefoneCliente(): bool
    {
        return $this->values['exige_telefone_cliente'];
    }

    public function exigeEmailCliente(): bool
    {
        return $this->values['exige_email_cliente'];
    }

    public function moduloVendedoresAtivo(): bool
    {
        return $this->values['modulo_vendedores_ativo'];
    }

    public function permitirEstoqueNegativo(): bool
    {
        return $this->values['permitir_estoque_negativo'];
    }

    /** @return array<string, bool> */
    public function toArray(): array
    {
        return $this->values;
    }
}
