<?php

namespace App\Services;

use App\Services\Payments\PaymentService;

class ReportService
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly BookingService $bookingService
    ) {
    }

    public function getDashboardMetrics(): array
    {
        $paymentSummary = $this->paymentService->getRevenueSummary();

        return [
            'total_revenue' => $paymentSummary['total_revenue'],
            'monthly_revenue' => $paymentSummary['monthly_revenue'],
            'paid_count' => $paymentSummary['paid_count'],
            'pending_count' => $paymentSummary['pending_count'],
            'conversion_rate' => $paymentSummary['conversion_rate'],
            'total_bookings' => $this->bookingService->getTotalBookings(),
        ];
    }
}
