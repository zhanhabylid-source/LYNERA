<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RescheduleBookingRequest;
use App\Http\Requests\StoreCalendarBookingRequest;
use App\Services\BookingService;
use App\Services\CustomerService;
use App\Services\ServiceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class CalendarController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly ServiceService $serviceService,
        private readonly CustomerService $customerService
    ) {
    }

    public function index(): View
    {
        $tomorrowDate = now()->addDay()->toDateString();
        $tomorrowBookings = $this->bookingService->getBookingsForDate($tomorrowDate);

        return view('admin.calendar.index', [
            'services' => $this->serviceService->allForSelect(),
            'customers' => $this->customerService->allForSelect(),
            'tomorrowDate' => $tomorrowDate,
            'tomorrowBookings' => $tomorrowBookings,
        ]);
    }

    public function events(): JsonResponse
    {
        if (request()->boolean('debug_static')) {
            return response()->json([[
                'title' => 'Test Event',
                'start' => '2026-03-25T10:00:00',
                'end' => '2026-03-25T12:00:00',
            ]]);
        }

        $start = $this->parseQueryDate((string) request()->query('start', ''));
        $end = $this->parseQueryDate((string) request()->query('end', ''));

        return response()->json(
            $this->bookingService->getCalendarEvents($start, $end)->values()
        );
    }

    public function store(StoreCalendarBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->create($request->validated());
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Booking berhasil dibuat.',
            'booking_id' => $booking->id,
        ], 201);
    }

    public function reschedule(RescheduleBookingRequest $request, int $booking): JsonResponse
    {
        $model = $this->bookingService->findOrFail($booking);

        try {
            $updatedBooking = $this->bookingService->rescheduleFromCalendar($model, $request->validated('start'));
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Jadwal booking berhasil diubah.',
            'booking_id' => $updatedBooking->id,
        ]);
    }

    private function parseQueryDate(string $value): ?string
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
