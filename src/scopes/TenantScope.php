<?php

declare(strict_types=1);

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Services\TenantContext;

/**
 * TenantScope
 * 
 * Automatically applies a where clause to filter records by the current
 * request's businessId, isolating data between tenants.
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // Superadmins bypass tenant isolation so they can view all platform data
        if (TenantContext::isSuperAdmin()) {
            return;
        }

        $tenantId = TenantContext::getTenantId();

        if ($tenantId !== null) {
            // Apply the tenant filter
            $builder->where($model->getTable() . '.businessId', '=', $tenantId);
        } else {
            // If there's no tenant context and the user isn't a superadmin,
            // block access to prevent accidental data leakage.
            $builder->whereRaw('1 = 0');
        }
    }
}
