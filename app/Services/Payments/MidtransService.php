<?php

namespace App\Services\Payments;

use App\Models\Payment;

class MidtransService
{
    public function generateSnapToken(Payment $payment): string
    {
        // Placeholder for real Midtrans Snap token request.
        return hash('sha256', $payment->id.'|'.$payment->amount.'|'.config('services.midtrans.server_key'));
    }

    public function generatePaymentUrl(Payment $payment): string
    {
        $baseUrl = config('services.midtrans.snap_url');
        $token = $this->generateSnapToken($payment);

        return rtrim($baseUrl, '/').'/'.$token;
    }
}
