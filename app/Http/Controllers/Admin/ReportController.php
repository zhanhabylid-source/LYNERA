<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\SubscriptionService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    public function index(): View
    {
        $summary = $this->reportService->getDashboardMetrics();

        return view('admin.reports.index', [
            'totalRevenue' => $summary['total_revenue'],
            'monthlyRevenue' => $summary['monthly_revenue'],
            'paidCount' => $summary['paid_count'],
            'pendingCount' => $summary['pending_count'],
            'conversionRate' => $summary['conversion_rate'],
            'totalBookings' => $summary['total_bookings'],
            'hasAdvancedReports' => $this->subscriptionService->canAccessFeatureForUser((int) auth()->id(), 'advanced_reports'),
        ]);
    }
}
