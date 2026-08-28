<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use App\Services\BillingLogService;
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly NotificationService $notificationService,
        private readonly BillingLogService $billingLogService,
        private readonly GoogleCalendarService $googleCalendarService
    ) {
    }

    public function paginate(int $perPage = 15, ?int $bookingId = null): LengthAwarePaginator
    {
        return $this->paymentRepository->paginate($perPage, $bookingId);
    }

    public function findOrFail(int $id): Payment
    {
        return $this->paymentRepository->findOrFail($id);
    }

    public function createForBooking(Booking $booking): Payment
    {
        $existingPayment = $this->paymentRepository->findByBookingId($booking->id);

        if ($existingPayment !== null) {
            return $existingPayment;
        }

        $booking->loadMissing(['service', 'bookingItems']);
        $subtotal = $this->calculateSubtotal($booking);
        $amount = $subtotal;
        $dpAmount = $this->calculateDpAmount($amount);

        return $this->paymentRepository->create([
            'tenant_id' => $booking->tenant_id,
            'booking_id' => $booking->id,
            'amount' => $amount,
            'discount_amount' => 0,
            'dp_amount' => $dpAmount,
            'paid_amount' => 0,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => Payment::METHOD_MANUAL,
            'dp_paid_at' => null,
            'paid_at' => null,
        ]);
    }

    public function markDpAsPaid(
        Payment $payment,
        string $paymentMethod = Payment::METHOD_MANUAL,
        ?float $manualDpAmount = null
    ): Payment
    {
        if ($payment->status === Payment::STATUS_FAILED) {
            throw new InvalidArgumentException('Pembayaran gagal tidak bisa langsung diproses. Ubah ke pending terlebih dahulu.');
        }

        $booking = $payment->booking;
        if ($booking->status === Booking::STATUS_CANCELED) {
            throw new InvalidArgumentException('Booking yang dibatalkan tidak dapat menerima pembayaran baru.');
        }

        $agreedDpAmount = $manualDpAmount !== null ? round($manualDpAmount, 2) : (float) $payment->dp_amount;
        if ($agreedDpAmount <= 0) {
            throw new InvalidArgumentException('Nominal DP harus lebih dari 0.');
        }
        if ($agreedDpAmount > (float) $payment->amount) {
            throw new InvalidArgumentException('Nominal DP tidak boleh melebihi total pembayaran booking.');
        }

        $targetPaidAmount = max($agreedDpAmount, (float) $payment->paid_amount);
        $isSettled = $targetPaidAmount >= (float) $payment->amount;

        $updated = $this->paymentRepository->update($payment, [
            'status' => $isSettled ? Payment::STATUS_PAID : Payment::STATUS_PENDING,
            'dp_amount' => $agreedDpAmount,
            'paid_amount' => $targetPaidAmount,
            'payment_method' => $paymentMethod,
            'dp_paid_at' => $payment->dp_paid_at ?? Carbon::now(),
            'paid_at' => $isSettled ? Carbon::now() : null,
        ]);
        $this->billingLogService->logPayment($updated, 'payment_dp_marked_paid');

        $booking = $updated->booking;
        $this->notificationService->sendWhatsApp(
            $booking->customer->phone,
            sprintf(
                'Hi %s, DP untuk %s tanggal %s sudah diterima. Terima kasih!',
                $booking->customer->name,
                $booking->service->name,
                $booking->booking_date?->format('d M Y')
            )
        );

        return $updated;
    }

    public function markAsSettled(Payment $payment, string $paymentMethod = Payment::METHOD_MANUAL): Payment
    {
        if ($payment->status === Payment::STATUS_FAILED) {
            throw new InvalidArgumentException('Pembayaran gagal tidak bisa langsung dilunasi. Ubah ke pending terlebih dahulu.');
        }

        if (! $payment->isDpPaid()) {
            throw new InvalidArgumentException('DP wajib dibayar terlebih dahulu sebelum pelunasan.');
        }

        $booking = $payment->booking;
        if ($booking->status === Booking::STATUS_CANCELED) {
            throw new InvalidArgumentException('Booking yang dibatalkan tidak dapat diproses pelunasan.');
        }

        if ($booking->hasServicePassed()) {
            throw new InvalidArgumentException('Pelunasan manual tidak tersedia karena tanggal layanan sudah lewat dan pembayaran otomatis ditandai lunas.');
        }

        $updated = $this->paymentRepository->update($payment, [
            'status' => Payment::STATUS_PAID,
            'paid_amount' => (float) $payment->amount,
            'payment_method' => $paymentMethod,
            'dp_paid_at' => $payment->dp_paid_at ?? Carbon::now(),
            'paid_at' => Carbon::now(),
        ]);
        $this->billingLogService->logPayment($updated, 'payment_marked_settled');

        $this->notificationService->sendWhatsApp(
            $booking->customer->phone,
            sprintf(
                'Hi %s, pelunasan untuk %s tanggal %s sudah diterima. Terima kasih!',
                $booking->customer->name,
                $booking->service->name,
                $booking->booking_date?->format('d M Y')
            )
        );

        return $updated;
    }

    public function updateStatus(Payment $payment, array $data): Payment
    {
        $status = $data['status'];
        $paymentMethod = $data['payment_method'] ?? $payment->payment_method;

        if ($status === Payment::STATUS_PAID) {
            return $this->markAsSettled($payment, $paymentMethod);
        }

        $updated = $this->paymentRepository->update($payment, [
            'tenant_id' => $payment->tenant_id,
            'status' => $status,
            'payment_method' => $paymentMethod,
            'dp_paid_at' => $status === Payment::STATUS_PENDING ? $payment->dp_paid_at : null,
            'paid_at' => null,
        ]);

        $this->billingLogService->logPayment($updated, 'payment_status_updated');

        return $updated;
    }

    public function cancelBookingFromPayment(Payment $payment): Payment
    {
        $booking = $payment->booking;
        if ($booking === null) {
            throw new InvalidArgumentException('Booking tidak ditemukan untuk pembayaran ini.');
        }

        if ($booking->status === Booking::STATUS_COMPLETED) {
            throw new InvalidArgumentException('Booking yang sudah selesai tidak dapat dibatalkan.');
        }

        if ($booking->hasServicePassed()) {
            throw new InvalidArgumentException('Booking yang tanggal layanannya sudah berlalu tidak dapat dibatalkan.');
        }

        if ($booking->status !== Booking::STATUS_CANCELED) {
            $booking->forceFill([
                'status' => Booking::STATUS_CANCELED,
                'tomorrow_reminder_sent_at' => null,
            ])->save();

            $this->googleCalendarService->detachBooking($booking);
        }

        $isSettled = (float) $payment->paid_amount >= (float) $payment->amount;
        $updatedPayment = $this->paymentRepository->update($payment, [
            'status' => $isSettled ? Payment::STATUS_PAID : Payment::STATUS_PENDING,
            'paid_at' => $isSettled ? ($payment->paid_at ?? Carbon::now()) : null,
        ]);

        $this->billingLogService->logPayment($updatedPayment, 'booking_canceled_dp_retained');

        return $updatedPayment;
    }

    public function getRevenueSummary(): array
    {
        $paidCount = $this->paymentRepository->countByStatus(Payment::STATUS_PAID);
        $pendingCount = $this->paymentRepository->countByStatus(Payment::STATUS_PENDING);
        $denominator = $paidCount + $pendingCount;

        return [
            'total_revenue' => $this->paymentRepository->sumPaidRevenue(),
            'monthly_revenue' => $this->paymentRepository->sumPaidRevenueCurrentMonth(),
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'conversion_rate' => $denominator > 0 ? round(($paidCount / $denominator) * 100, 2) : 0.0,
        ];
    }

    public function autoSettlePassedServiceBookings(?int $tenantId = null): int
    {
        $query = Payment::query()
            ->with('booking')
            ->where(function ($paymentQuery) {
                $paymentQuery
                    ->where('status', '!=', Payment::STATUS_PAID)
                    ->orWhereColumn('paid_amount', '<', 'amount');
            })
            ->whereHas('booking', function ($bookingQuery) {
                $bookingQuery
                    ->where('status', '!=', Booking::STATUS_CANCELED)
                    ->whereDate('booking_date', '<=', Carbon::today()->toDateString());
            });

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $settledCount = 0;
        foreach ($query->get() as $payment) {
            $booking = $payment->booking;
            if ($booking === null || ! $booking->hasServicePassed()) {
                continue;
            }

            $updatedPayment = $this->paymentRepository->update($payment, [
                'status' => Payment::STATUS_PAID,
                'paid_amount' => (float) $payment->amount,
                'dp_paid_at' => $payment->dp_paid_at ?? Carbon::now(),
                'paid_at' => $payment->paid_at ?? Carbon::now(),
            ]);

            $this->billingLogService->logPayment($updatedPayment, 'payment_auto_settled_after_service_passed');
            $settledCount++;
        }

        return $settledCount;
    }

    public function processBookingPayment(Booking $booking, float $amount): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount must be greater than zero.',
            ]);
        }

        $payment = $this->createForBooking($booking);

        $this->paymentRepository->update($payment, [
            'amount' => $amount,
            'discount_amount' => 0,
            'dp_amount' => $this->calculateDpAmount($amount),
            'paid_amount' => 0,
            'dp_paid_at' => null,
            'paid_at' => null,
            'status' => Payment::STATUS_PENDING,
        ]);
    }

    public function updatePricing(Payment $payment, ?float $discountAmount = null): Payment
    {
        $booking = $payment->booking;
        if ($booking->status === Booking::STATUS_CANCELED) {
            throw new InvalidArgumentException('Booking yang dibatalkan tidak dapat diubah nominal pembayarannya.');
        }
        if ($booking->hasServicePassed()) {
            throw new InvalidArgumentException('Nominal pembayaran tidak dapat diubah karena tanggal layanan sudah lewat.');
        }

        $booking->loadMissing(['service', 'bookingItems']);
        $subtotal = $this->calculateSubtotal($booking);
        $discount = round(max(0, (float) ($discountAmount ?? 0)), 2);
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        $amount = max(0, round($subtotal - $discount, 2));
        $dpAmount = $this->calculateDpAmount($amount);
        $paidAmount = min((float) $payment->paid_amount, $amount);

        $isDpPaid = $paidAmount >= $dpAmount;
        $isSettled = $paidAmount >= $amount;

        $updated = $this->paymentRepository->update($payment, [
            'amount' => $amount,
            'discount_amount' => $discount,
            'dp_amount' => $dpAmount,
            'paid_amount' => $paidAmount,
            'status' => $isSettled ? Payment::STATUS_PAID : Payment::STATUS_PENDING,
            'dp_paid_at' => $isDpPaid ? ($payment->dp_paid_at ?? Carbon::now()) : null,
            'paid_at' => $isSettled ? ($payment->paid_at ?? Carbon::now()) : null,
        ]);

        $this->billingLogService->logPayment($updated, 'payment_pricing_updated');

        return $updated;
    }

    public function syncWithBookingPricing(Booking $booking): Payment
    {
        $booking->loadMissing(['service', 'bookingItems']);
        $payment = $this->createForBooking($booking);
        if ($booking->status === Booking::STATUS_CANCELED || $booking->hasServicePassed()) {
            return $payment;
        }

        $subtotal = $this->calculateSubtotal($booking);
        $discount = round(max(0, (float) $payment->discount_amount), 2);
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        $amount = max(0, round($subtotal - $discount, 2));
        $dpAmount = $this->calculateDpAmount($amount);
        $paidAmount = min((float) $payment->paid_amount, $amount);

        $isDpPaid = $paidAmount >= $dpAmount;
        $isSettled = $paidAmount >= $amount;

        $updated = $this->paymentRepository->update($payment, [
            'amount' => $amount,
            'discount_amount' => $discount,
            'dp_amount' => $dpAmount,
            'paid_amount' => $paidAmount,
            'status' => $isSettled ? Payment::STATUS_PAID : Payment::STATUS_PENDING,
            'dp_paid_at' => $isDpPaid ? ($payment->dp_paid_at ?? Carbon::now()) : null,
            'paid_at' => $isSettled ? ($payment->paid_at ?? Carbon::now()) : null,
        ]);

        $this->billingLogService->logPayment($updated, 'payment_synced_from_booking');

        return $updated;
    }

    private function calculateDpAmount(float $totalAmount): float
    {
        $percent = (float) config('payment.dp_min_percent', 30);
        $ratio = max(0, min(100, $percent)) / 100;

        return round($totalAmount * $ratio, 2);
    }

    private function calculateSubtotal(Booking $booking): float
    {
        $amountFromItems = (float) $booking->bookingItems->sum('subtotal');
        $transportFee = round(max(0, (float) ($booking->transport_fee ?? 0)), 2);
        if ($amountFromItems > 0) {
            return round($amountFromItems + $transportFee, 2);
        }

        $servicePrice = (float) ($booking->service?->price ?? 0);

        return round($servicePrice + $transportFee, 2);
    }
}
