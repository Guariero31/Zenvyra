<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Garante que pedidos listados no painel da loja só tenham vendedor vinculado
 * à loja ativa (ou sem vendedor). Protege contra registros corrompidos no schema.
 */
class TenantMemberOperatorScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenant = TenantContext::current();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $memberIds = $tenant->users()->pluck('users.id');

        $builder->where(function (Builder $query) use ($memberIds): void {
            $query->whereNull('user_id')
                ->orWhereIn('user_id', $memberIds);
        });
    }
}
