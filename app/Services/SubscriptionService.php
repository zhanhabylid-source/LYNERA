<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Booking;
use App\Models\User;
use App\Repositories\SubscriptionRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly BillingLogService $billingLogService,
        private readonly PlanService $planService
    ) {
    }

    public function ensureUserSubscription(int $userId): Subscription
    {
        return $this->subscriptionRepository->firstOrCreateForUser($userId);
    }

    public function createTrialForUser(int $userId, string $plan, int $trialDays = 7): Subscription
    {
        $normalizedPlan = $this->planService->normalizePlan($plan);
        $subscription = $this->subscriptionRepository->firstOrCreateForUser($userId);

        return $this->subscriptionRepository->update($subscription, [
            'plan' => $normalizedPlan,
            'expired_at' => null,
        ]);
    }

    public function getCurrentPlanForUser(int $userId): string
    {
        $subscription = $this->subscriptionRepository->findByUserId($userId);

        return $this->planService->normalizePlan($subscription?->plan);
    }

    /**
     * @return array{plan:string,bookings_count:int,limit:?int,remaining:?int,is_unlimited:bool,percent_used:int}
     */
    public function getBookingUsage(int $userId): array
    {
        $plan = $this->getCurrentPlanForUser($userId);
        $limit = $this->planService->totalBookingLimit($plan);
        $count = $this->countConsumedBookings($userId);
        $isUnlimited = $limit === null;
        $remaining = $isUnlimited ? null : max(0, $limit - $count);
        $percentUsed = $isUnlimited || $limit <= 0
            ? 0
            : (int) min(100, round(($count / $limit) * 100));

        return [
            'plan' => $plan,
            'bookings_count' => $count,
            'limit' => $limit,
            'remaining' => $remaining,
            'is_unlimited' => $isUnlimited,
            'percent_used' => $percentUsed,
        ];
    }

    /**
     * @deprecated Gunakan getBookingUsage(). Dipertahankan sementara untuk kompatibilitas.
     * @return array{plan:string,bookings_count:int,limit:?int,remaining:?int,is_unlimited:bool,percent_used:int}
     */
    public function getBookingUsageForCurrentMonth(int $userId): array
    {
        return $this->getBookingUsage($userId);
    }

    public function assertBookingCreationAllowed(int $userId): void
    {
        $user = User::query()->find($userId);
        if ($user?->isSuperAdmin()) {
            return;
        }

        $usage = $this->getBookingUsage($userId);
        if ($usage['is_unlimited']) {
            return;
        }

        if ($usage['remaining'] !== null && $usage['remaining'] <= 0) {
            throw ValidationException::withMessages([
                'plan' => sprintf(
                    'Batas booking paket %s sudah tercapai (%d/%d total). Upgrade ke Pro atau Premium untuk booking tanpa batas.',
                    strtoupper($usage['plan']),
                    $usage['bookings_count'],
                    (int) $usage['limit']
                ),
            ]);
        }
    }

    public function upgradePlan(int $userId, string $plan, ?Carbon $expiredAt = null): Subscription
    {
        $normalizedPlan = $this->planService->normalizePlan($plan);

        // Paket berbayar aktif 1 bulan secara default; paket free tanpa masa berlaku.
        if ($expiredAt === null) {
            $expiredAt = $this->defaultExpiryForPlan($normalizedPlan);
        }

        $subscription = $this->ensureUserSubscription($userId);
        $updated = $this->subscriptionRepository->update($subscription, [
            'plan' => $normalizedPlan,
            'expired_at' => $expiredAt,
            'expiry_reminder_sent_at' => null,
        ]);

        $this->billingLogService->logSubscriptionUpgrade($userId, $normalizedPlan, $expiredAt);

        return $updated;
    }

    public function defaultExpiryForPlan(string $plan): ?Carbon
    {
        if ($this->planService->normalizePlan($plan) === Subscription::PLAN_FREE) {
            return null;
        }

        return now()->addMonth();
    }

    /**
     * Turunkan tenant ke paket Free secara otomatis jika masa berlaku berbayar sudah habis.
     */
    public function downgradeIfExpired(int $userId): bool
    {
        $subscription = $this->subscriptionRepository->findByUserId($userId);

        if (! $subscription || $subscription->plan === Subscription::PLAN_FREE) {
            return false;
        }

        if (! $subscription->expired_at || ! $subscription->expired_at->isPast()) {
            return false;
        }

        $this->subscriptionRepository->update($subscription, [
            'plan' => Subscription::PLAN_FREE,
            'expired_at' => null,
            'expiry_reminder_sent_at' => null,
        ]);

        $this->billingLogService->logSubscriptionUpgrade($userId, Subscription::PLAN_FREE, null);

        return true;
    }

    public function canAccessFeatureForUser(int $userId, string $feature): bool
    {
        $plan = $this->getCurrentPlanForUser($userId);

        return $this->planService->canAccessFeature($plan, $feature);
    }

    public function recordBookingConsumed(int $userId, int $amount = 1): void
    {
        if ($amount <= 0) {
            return;
        }

        $subscription = $this->ensureUserSubscription($userId);
        Subscription::query()
            ->whereKey($subscription->id)
            ->increment('bookings_consumed_total', $amount);
    }

    /**
     * Reduce the counter when a booking is removed so quota is released.
     * Never goes below 0.
     */
    public function releaseBookingConsumed(int $userId, int $amount = 1): void
    {
        if ($amount <= 0) {
            return;
        }

        $subscription = $this->ensureUserSubscription($userId);
        $current = (int) ($subscription->bookings_consumed_total ?? 0);
        $next = max(0, $current - $amount);
        Subscription::query()
            ->whereKey($subscription->id)
            ->update(['bookings_consumed_total' => $next]);
    }

    private function countConsumedBookings(int $userId): int
    {
        // Source of truth is the actual live booking count: create() increments,
        // delete() decrements, and this always reflects reality. The persisted
        // counter is kept in sync for admin dashboards but no longer sticky-high.
        $subscription = $this->ensureUserSubscription($userId);
        $currentBookings = (int) Booking::withoutGlobalScopes()
            ->where('tenant_id', $userId)
            ->count();

        $persistedConsumed = max(0, (int) ($subscription->bookings_consumed_total ?? 0));
        if ($currentBookings !== $persistedConsumed) {
            $this->subscriptionRepository->update($subscription, [
                'bookings_consumed_total' => $currentBookings,
            ]);
        }

        return $currentBookings;
    }
}
