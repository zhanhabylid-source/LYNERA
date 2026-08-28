<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MarkDpPaymentRequest;
use App\Http\Requests\UpdatePaymentPricingRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $bookingId = $request->integer('booking_id') ?: null;
        if ($bookingId !== null) {
            $booking = Booking::query()->findOrFail($bookingId);
            if ($booking->status === Booking::STATUS_CANCELED) {
                return redirect()
                    ->route('admin.bookings.show', $booking)
                    ->withErrors([
                        'booking' => 'Pembayaran tidak tersedia untuk booking yang dibatalkan.',
                    ]);
            }
        }

        $this->paymentService->autoSettlePassedServiceBookings(auth()->id());

        return view('admin.payments.index', [
            'payments' => $this->paymentService->paginate(15, $bookingId),
            'bookingId' => $bookingId,
        ]);
    }

    public function update(UpdatePaymentStatusRequest $request, int $payment): RedirectResponse
    {
        $model = $this->paymentService->findOrFail($payment);
        try {
            $this->paymentService->updateStatus($model, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function markAsPaid(int $payment): RedirectResponse
    {
        $model = $this->paymentService->findOrFail($payment);

        try {
            $this->paymentService->markAsSettled($model, Payment::METHOD_MANUAL);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Pembayaran berhasil ditandai lunas.');
    }

    public function markDpPaid(MarkDpPaymentRequest $request, int $payment): RedirectResponse
    {
        $model = $this->paymentService->findOrFail($payment);
        $manualDpAmount = $request->filled('dp_amount')
            ? (float) $request->validated('dp_amount')
            : null;

        try {
            $updated = $this->paymentService->markDpAsPaid($model, Payment::METHOD_MANUAL, $manualDpAmount);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        }

        $message = $updated->isSettled()
            ? 'Pembayaran berhasil diterima dan booking sudah lunas.'
            : 'Pembayaran DP berhasil diterima.';

        return back()->with('success', $message);
    }

    public function markSettled(int $payment): RedirectResponse
    {
        $model = $this->paymentService->findOrFail($payment);

        try {
            $this->paymentService->markAsSettled($model, Payment::METHOD_MANUAL);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Pelunasan berhasil diterima.');
    }

    public function updatePricing(UpdatePaymentPricingRequest $request, int $payment): RedirectResponse
    {
        $model = $this->paymentService->findOrFail($payment);
        $discountAmount = $request->filled('discount_amount')
            ? (float) $request->validated('discount_amount')
            : null;

        try {
            $this->paymentService->updatePricing($model, $discountAmount);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Subtotal dan diskon berhasil diperbarui.');
    }

    public function cancelBooking(int $payment): RedirectResponse
    {
        $model = $this->paymentService->findOrFail($payment);

        try {
            $this->paymentService->cancelBookingFromPayment($model);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Booking berhasil dibatalkan. DP tetap tercatat sebagai pemasukan.');
    }
}
