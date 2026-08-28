<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Backfill otomatis untuk akun lama yang belum punya subscription row.
        if (! $user->subscription) {
            app(SubscriptionService::class)->ensureUserSubscription((int) $user->id);
            $user->refresh();
        }

        // Auto-downgrade ke Free jika masa berlaku paket berbayar sudah habis.
        if (app(SubscriptionService::class)->downgradeIfExpired((int) $user->id)) {
            $user->refresh();
        }

        if ($user && ! $user->isSuperAdmin() && ! $user->hasCompletedOnboarding()) {
            return redirect('/onboarding')->with('error', 'Silakan selesaikan onboarding sebelum mengakses dasbor.');
        }

        return $next($request);
    }
}
