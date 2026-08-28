<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tenantQuery = User::query()->where('role', '!=', 'super_admin');
        $totalTenants = (clone $tenantQuery)->count();
        $nonSuperAdmin = fn ($query) => $query->whereHas('user', fn ($u) => $u->where('role', '!=', 'super_admin'));
        $activeSubscribers = Subscription::query()->whereIn('plan', ['pro', 'premium'])->tap($nonSuperAdmin)->count();
        $freeSubscribers = Subscription::query()->where('plan', 'free')->tap($nonSuperAdmin)->count();
        $pendingUpgradeCount = SubscriptionUpgradeRequest::query()
            ->where('status', SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION)
            ->count();
        $planBreakdown = Subscription::query()
            ->tap($nonSuperAdmin)
            ->selectRaw('plan, COUNT(*) as total')
            ->groupBy('plan')
            ->orderBy('plan')
            ->get();

        return view('backend.dashboard', [
            'totalTenants' => $totalTenants,
            'activeSubscribers' => $activeSubscribers,
            'freeSubscribers' => $freeSubscribers,
            'pendingUpgradeCount' => $pendingUpgradeCount,
            'planBreakdown' => $planBreakdown,
        ]);
    }
}

