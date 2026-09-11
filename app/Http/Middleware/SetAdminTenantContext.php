<?php

namespace App\Http\Middleware;

use App\Scopes\TenantScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isSuperAdmin()) {
            TenantScope::usingTenant((int) $user->id);
        }

        try {
            return $next($request);
        } finally {
            TenantScope::forgetTenant();
        }
    }
}
