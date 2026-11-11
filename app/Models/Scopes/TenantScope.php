<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the tenant scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check() && auth()->user()->company_id) {
            $builder->where($model->getTable() . '.company_id', auth()->user()->company_id);
        } elseif (!app()->runningInConsole()) {
            // Defense-in-depth: Unauthenticated web/api requests must never leak multi-tenant records
            $builder->whereRaw('1 = 0');
        }
    }
}
