<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendTomorrowBookingReminders extends Command
{
    protected $signature = 'bookings:send-tomorrow-reminders';

    protected $description = 'Kirim pengingat H-1 untuk booking besok (email + WhatsApp).';

    public function __construct(
        private readonly NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $bookings = Booking::withoutGlobalScopes()
            ->with(['tenant', 'customer', 'service'])
            ->whereDate('booking_date', $tomorrow)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->whereNull('tomorrow_reminder_sent_at')
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($bookings as $booking) {
            $tenant = $booking->tenant;
            if ($tenant === null || ! $tenant->notify_tomorrow_booking) {
                $skipped++;
                continue;
            }

            if (! is_string($tenant->email) || trim($tenant->email) === '') {
                $skipped++;
                continue;
            }

            $this->notificationService->sendTomorrowBookingReminderEmail($tenant, $booking);
            $this->notificationService->sendTomorrowBookingReminderWhatsApp($tenant, $booking);

            $booking->forceFill([
                'tomorrow_reminder_sent_at' => now(),
            ])->save();

            $sent++;
        }

        $this->info(sprintf(
            'Selesai kirim reminder H-1. Terkirim: %d, dilewati: %d.',
            $sent,
            $skipped
        ));

        return self::SUCCESS;
    }
}
