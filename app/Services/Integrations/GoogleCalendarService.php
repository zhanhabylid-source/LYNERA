<?php

namespace App\Services\Integrations;

use App\Models\Booking;
use App\Models\CalendarIntegration;

class GoogleCalendarService
{
    public function syncBooking(Booking $booking): void
    {
        // Placeholder implementation:
        // persist mapping row so future Google event ID can be attached safely.
        CalendarIntegration::query()->firstOrCreate(
            ['booking_id' => $booking->id],
            ['google_event_id' => null]
        );
    }

    public function detachBooking(Booking $booking): void
    {
        CalendarIntegration::query()
            ->where('booking_id', $booking->id)
            ->delete();
    }
}
