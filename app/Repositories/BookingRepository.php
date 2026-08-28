<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Booking::query()
            ->with(['customer', 'service', 'payment', 'bookingItems.service', 'tenant'])
            ->latest('booking_date')
            ->latest('booking_time')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Booking
    {
        return Booking::query()
            ->with(['customer', 'service', 'payment', 'bookingItems.service', 'tenant'])
            ->findOrFail($id);
    }

    public function create(array $data): Booking
    {
        return Booking::query()->create($data);
    }

    public function update(Booking $booking, array $data): Booking
    {
        $booking->update($data);

        return $booking->refresh();
    }

    public function delete(Booking $booking): void
    {
        $booking->delete();
    }

    public function countAll(): int
    {
        return Booking::query()->count();
    }

    public function hasConflict(
        string $bookingDate,
        string $startTime,
        string $endTime,
        ?int $ignoreBookingId = null
    ): bool {
        return Booking::query()
            ->when($ignoreBookingId !== null, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->whereDate('booking_date', $bookingDate)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->where(function ($query) use ($startTime, $endTime) {
                $query
                    ->where('booking_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();
    }

    public function getInRange(?string $startDate = null, ?string $endDate = null): Collection
    {
        return Booking::query()
            ->with(['customer', 'service', 'bookingItems.service'])
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->whereNotNull('booking_date')
            ->whereNotNull('booking_time')
            ->whereNotNull('end_time')
            ->when($startDate !== null, fn ($query) => $query->whereDate('booking_date', '>=', $startDate))
            ->when($endDate !== null, fn ($query) => $query->whereDate('booking_date', '<=', $endDate))
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get();
    }

    public function getUpcoming(int $limit = 5): Collection
    {
        return Booking::query()
            ->with(['customer', 'service', 'bookingItems.service'])
            ->where(function ($query) {
                $query->whereDate('booking_date', '>', now()->toDateString())
                    ->orWhere(function ($nested) {
                        $nested->whereDate('booking_date', now()->toDateString())
                            ->whereTime('booking_time', '>=', now()->format('H:i:s'));
                    });
            })
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->limit($limit)
            ->get();
    }

    public function totalRevenue(): float
    {
        return (float) Booking::query()
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->sum('services.price');
    }
}
