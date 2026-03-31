<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Auto-scope all queries to the current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('currentTenant')) {
                $builder->where(
                    (new static)->getTable() . '.tenant_id',
                    app('currentTenant')->id
                );
            }
        });

        // Auto-fill tenant_id on create
        static::creating(function ($model) {
            if (app()->bound('currentTenant') && empty($model->tenant_id)) {
                $model->tenant_id = app('currentTenant')->id;
            }
        });
    }

    // Escape hatch — query across all tenants
    public static function withoutTenant(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
