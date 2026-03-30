<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use LogicException;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Guard — every model using this trait MUST have a tenant_id column
        if (!in_array('tenant_id', (new static)->getFillable())) {
            throw new LogicException(
                static::class . ' uses BelongsToTenant but tenant_id is not in $fillable.'
            );
        }

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

    // Escape hatch — run a query without the tenant scope
    public static function withoutTenant(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
