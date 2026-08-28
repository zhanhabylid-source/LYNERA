<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RescheduleBookingDateTimeRequest;
use App\Http\Requests\UpdateBookingTermsRequest;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use App\Services\CustomerService;
use App\Services\InvoiceService;
use App\Services\Payments\PaymentService;
use App\Services\PublicBookingFormService;
use App\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly CustomerService $customerService,
        private readonly ServiceService $serviceService,
        private readonly PaymentService $paymentService,
        private readonly PublicBookingFormService $publicBookingFormService
    ) {
    }

    public function index(): View
    {
        $tenant = auth()->user();

        return view('admin.bookings.index', [
            'bookings' => $this->bookingService->paginate(),
            'tenant' => $tenant,
        ]);
    }

    public function updateTerms(UpdateBookingTermsRequest $request): RedirectResponse
    {
        $tenant = auth()->user();
        if ($tenant === null) {
            return redirect()
                ->route('login');
        }

        $tenant->forceFill([
            'booking_terms_title' => trim((string) $request->validated('booking_terms_title')),
            'booking_terms_content' => trim((string) $request->validated('booking_terms_content')),
            'booking_terms_updated_at' => now(),
        ])->save();

        $this->publicBookingFormService->syncTermsToActiveForms(
            (int) $tenant->id,
            (string) $tenant->booking_terms_title,
            (string) $tenant->booking_terms_content
        );

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'T&C booking berhasil disimpan.');
    }

    public function create(): View
    {
        return view('admin.bookings.create', [
            'customers' => $this->customerService->allForSelect(),
            'services' => $this->serviceService->allForSelect(),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        try {
            $this->bookingService->create($request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['booking_time' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Booking berhasil dibuat.');
    }

    public function edit(int $booking): View|RedirectResponse
    {
        $model = $this->bookingService->findOrFail($booking);
        if ($model->hasServicePassed()) {
            return redirect()
                ->route('admin.bookings.show', $model)
                ->withErrors([
                    'booking' => 'Booking yang tanggal layanannya sudah berlalu tidak dapat diperbarui. Anda masih bisa melihat detailnya.',
                ]);
        }

        return view('admin.bookings.edit', [
            'booking' => $model,
            'customers' => $this->customerService->allForSelect(),
            'services' => $this->serviceService->allForSelect(),
        ]);
    }

    public function update(StoreBookingRequest $request, int $booking): RedirectResponse
    {
        $model = $this->bookingService->findOrFail($booking);

        try {
            $this->bookingService->update($model, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['booking_time' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Booking berhasil diperbarui.');
    }

    public function show(int $booking): View
    {
        return view('admin.bookings.show', [
            'booking' => $this->bookingService->findOrFail($booking),
        ]);
    }

    public function destroy(int $booking): RedirectResponse
    {
        $model = $this->bookingService->findOrFail($booking);

        try {
            $this->bookingService->delete($model);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'booking' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Booking berhasil dihapus.');
    }

    public function invoice(int $booking, InvoiceService $invoiceService): Response
    {
        $model = $this->bookingService->findOrFail($booking);

        return $invoiceService->downloadBookingInvoice($model);
    }

    public function invoicePreview(int $booking, InvoiceService $invoiceService): Response
    {
        $model = $this->bookingService->findOrFail($booking);

        return $invoiceService->previewBookingInvoice($model);
    }

    public function payNow(int $booking): RedirectResponse
    {
        $model = $this->bookingService->findOrFail($booking);
        if ($model->status === Booking::STATUS_CANCELED) {
            return redirect()
                ->route('admin.bookings.show', $model)
                ->withErrors([
                    'booking' => 'Pembayaran tidak tersedia untuk booking yang dibatalkan.',
                ]);
        }

        return redirect()
            ->route('admin.payments.index', ['booking_id' => $model->id])
            ->with('success', "Buka halaman pembayaran untuk booking #{$model->id}. Proses pembayaran menggunakan alur DP dan pelunasan.");
    }

    public function reschedule(RescheduleBookingDateTimeRequest $request, int $booking): JsonResponse
    {
        $model = $this->bookingService->findOrFail($booking);

        try {
            $this->bookingService->rescheduleByDateTime(
                $model,
                $request->validated('date'),
                $request->validated('start_time'),
                $request->validated('end_time')
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function confirm(Request $request, int $booking): RedirectResponse
    {
        $model = $this->bookingService->findOrFail($booking);
        $payment = $this->paymentService->createForBooking($model);
        if (! $payment->isDpPaid()) {
            return back()->withErrors([
                'calendar' => 'Booking belum bisa dikonfirmasi karena DP belum dibayar.',
            ]);
        }

        try {
            $this->bookingService->update($model, [
                'status' => 'confirmed',
            ]);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'calendar' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Booking berhasil dikonfirmasi.');
    }
}
