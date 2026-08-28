<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\CustomerService;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly CustomerService $customerService,
        private readonly PlanService $planService,
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();
        $plan = $this->planService->normalizePlan(getUserPlan($user));
        $trialDaysLeft = getRemainingDays($user);
        $servicesCount = $user?->services()->count() ?? 0;
        $planDetail = $this->planService->detail($plan);
        $bookingUsage = $this->subscriptionService->getBookingUsage((int) ($user?->id ?? 0));
        $hasPlanActivationNotice = $user?->plan_activation_notice_until !== null
            && Carbon::parse($user->plan_activation_notice_until)->isFuture();

        $subscription = $user?->subscription;
        $expiryWarning = null;
        if ($subscription && in_array($subscription->plan, ['pro', 'premium'], true) && $subscription->expired_at) {
            $expiry = $subscription->expired_at;
            if ($expiry->isPast()) {
                $expiryWarning = ['type' => 'expired', 'date' => $expiry, 'days' => 0];
            } elseif ($expiry->lte(now()->addDays(7))) {
                $daysLeft = (int) ceil(now()->diffInDays($expiry, false));
                $expiryWarning = ['type' => 'soon', 'date' => $expiry, 'days' => max(0, $daysLeft)];
            }
        }

        return view('admin.dashboard', [
            'totalBookings' => $this->bookingService->getTotalBookings(),
            'totalCustomers' => $this->customerService->getTotalCustomers(),
            'totalRevenue' => $this->bookingService->getTotalRevenue(),
            'upcomingBookings' => $this->bookingService->getUpcomingBookings(5),
            'plan' => $plan,
            'planDetail' => $planDetail,
            'trialDaysLeft' => $trialDaysLeft,
            'trialExpiry' => $user?->subscription?->expired_at,
            'bookingUsage' => $bookingUsage,
            'servicesCount' => $servicesCount,
            'hasPlanActivationNotice' => $hasPlanActivationNotice,
            'planActivationNoticePlan' => (string) ($user?->plan_activation_notice_plan ?? $plan),
            'expiryWarning' => $expiryWarning,
        ]);
    }
}
