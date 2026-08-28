<?php

namespace App\Services;

use App\Models\BillingLog;
use App\Models\Payment;
use Carbon\Carbon;

class BillingLogService
{
    public function logPayment(Payment $payment, string $eventType): BillingLog
    {
        return BillingLog::query()->create([
            'tenant_id' => $payment->tenant_id,
            'payment_id' => $payment->id,
            'event_type' => $eventType,
            'amount' => $payment->amount,
            'metadata' => [
                'booking_id' => $payment->booking_id,
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
            ],
        ]);
    }

    public function logSubscriptionUpgrade(int $tenantId, string $plan, ?Carbon $expiredAt = null): BillingLog
    {
        return BillingLog::query()->create([
            'tenant_id' => $tenantId,
            'payment_id' => null,
            'event_type' => 'subscription_upgrade',
            'amount' => null,
            'metadata' => [
                'plan' => $plan,
                'expired_at' => $expiredAt?->toIso8601String(),
            ],
        ]);
    }
}
