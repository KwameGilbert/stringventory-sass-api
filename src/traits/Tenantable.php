<?php

declare(strict_types=1);

namespace App\Traits;

use App\Scopes\TenantScope;
use App\Services\TenantContext;

/**
 * Tenantable Trait
 * 
 * Applies the TenantScope to the model and automatically sets the businessId
 * when a new record is created.
 */
trait Tenantable
{
    /**
     * Boot the trait for a model.
     *
     * @return void
     */
    protected static function bootTenantable()
    {
        // Add the global scope to automatically filter queries by tenant
        static::addGlobalScope(new TenantScope());

        // Automatically set the businessId when creating a new record
        static::creating(function ($model) {
            // Only set if not already set manually and a context exists
            if (empty($model->businessId)) {
                $tenantId = TenantContext::getTenantId();
                if ($tenantId !== null) {
                    $model->businessId = $tenantId;
                }
            }
        });
    }

    /**
     * Define the relationship to the Business model.
     * All tenantable models belong to a business.
     */
    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class, 'businessId');
    }
}
