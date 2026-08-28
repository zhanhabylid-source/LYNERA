<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    private static ?int $tenantOverride = null;

    /**
     * Force tenant context for the current request (used by public/guest flows
     * where there is no authenticated user, e.g. public booking submission).
     */
    public static function usingTenant(?int $tenantId): void
    {
        self::$tenantOverride = $tenantId;
    }

    public static function forgetTenant(): void
    {
        self::$tenantOverride = null;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (self::$tenantOverride !== null) {
            $builder->where($model->getTable().'.tenant_id', self::$tenantOverride);

            return;
        }

        if (! Auth::check()) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $user = Auth::user();

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return;
        }

        $builder->where($model->getTable().'.tenant_id', $user->id);
    }
}
