<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentRepository
{
    public function paginate(int $perPage = 15, ?int $bookingId = null): LengthAwarePaginator
    {
        return Payment::query()
            ->with(['booking.customer', 'booking.service', 'booking.bookingItems'])
            ->when($bookingId !== null, fn ($query) => $query->where('booking_id', $bookingId))
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Payment
    {
        return Payment::query()
            ->with(['booking.customer', 'booking.service', 'booking.bookingItems'])
            ->findOrFail($id);
    }

    public function findByBookingId(int $bookingId): ?Payment
    {
        return Payment::query()
            ->where('booking_id', $bookingId)
            ->first();
    }

    public function create(array $data): Payment
    {
        return Payment::query()->create($data);
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->refresh();
    }

    public function sumPaidRevenue(): float
    {
        return (float) Payment::query()
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');
    }

    public function sumPaidRevenueCurrentMonth(): float
    {
        return (float) Payment::query()
            ->where('paid_amount', '>', 0)
            ->where(function ($query) {
                $query
                    ->where(function ($paidAt) {
                        $paidAt->whereNotNull('paid_at')
                            ->whereYear('paid_at', now()->year)
                            ->whereMonth('paid_at', now()->month);
                    })
                    ->orWhere(function ($dpPaidAt) {
                        $dpPaidAt->whereNull('paid_at')
                            ->whereNotNull('dp_paid_at')
                            ->whereYear('dp_paid_at', now()->year)
                            ->whereMonth('dp_paid_at', now()->month);
                    });
            })
            ->sum('paid_amount');
    }

    public function countByStatus(string $status): int
    {
        return Payment::query()
            ->where('status', $status)
            ->count();
    }
}
