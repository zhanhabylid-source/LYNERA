<?php

use App\Models\Subscription;
use App\Models\User;

if (! function_exists('getUserPlan')) {
    function getUserPlan(?User $user = null): string
    {
        $user ??= auth()->user();
        $defaultPlan = (string) config('plans.default', Subscription::PLAN_FREE);

        if (! $user) {
            return $defaultPlan;
        }

        return $user->subscription?->plan ?? $defaultPlan;
    }
}

if (! function_exists('getRemainingDays')) {
    function getRemainingDays(?User $user = null): ?int
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        $expiredAt = $user->subscription?->expired_at;

        if (! $expiredAt) {
            return null;
        }

        return max(0, (int) ceil(now()->diffInDays($expiredAt, false)));
    }
}
