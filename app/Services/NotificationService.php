<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private readonly PlanService $planService
    ) {
    }

    public function sendWhatsApp(string $phone, string $message): void
    {
        // Placeholder for real WhatsApp gateway integration.
        Log::info('WhatsApp notification queued', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }

    public function sendWelcomeMessage(User $user, Subscription $subscription): void
    {
        $planDetail = $this->planService->detail($subscription->plan);
        $message = sprintf(
            'Selamat datang! Paket Anda: %s. %s.',
            strtoupper((string) ($planDetail['name'] ?? $subscription->plan)),
            (string) ($planDetail['booking_limit_label'] ?? '')
        );

        Log::info('Welcome onboarding message', [
            'user_id' => $user->id,
            'email' => $user->email,
            'message' => $message,
        ]);
    }

    public function sendPublicBookingAlert(User $tenant, Booking $booking): bool
    {
        $message = sprintf(
            'Booking publik baru: %s memesan %s pada %s pukul %s.',
            $booking->customer->name,
            $booking->service->name,
            $booking->booking_date?->format('d M Y'),
            substr((string) $booking->booking_time, 0, 5)
        );

        Log::info('Public booking alert', [
            'tenant_id' => $tenant->id,
            'tenant_email' => $tenant->email,
            'booking_id' => $booking->id,
            'message' => $message,
        ]);

        try {
            Mail::send('emails.notification', [
                'preheader' => $message,
                'badge' => 'Booking Baru',
                'heading' => 'Ada Booking Baru Masuk!',
                'intro' => sprintf('Halo %s, seorang pelanggan baru saja melakukan booking melalui tautan publik Anda. Berikut detailnya:', $tenant->name),
                'details' => [
                    'Pelanggan' => (string) ($booking->customer->name ?? '-'),
                    'WhatsApp' => (string) ($booking->customer->phone ?? '-'),
                    'Layanan' => (string) ($booking->service->name ?? '-'),
                    'Jumlah Orang' => (string) ($booking->total_people ?? 1),
                    'Tanggal' => (string) ($booking->booking_date?->translatedFormat('d F Y') ?? '-'),
                    'Jam' => substr((string) ($booking->booking_time ?? ''), 0, 5),
                    'Lokasi' => (string) ($booking->location ?: '-'),
                ],
                'actionLabel' => 'Buka Dashboard Booking',
                'actionUrl' => route('admin.bookings.index'),
                'outro' => 'Silakan buka dasbor LYNERA untuk mengonfirmasi jadwal dan memproses pembayaran booking ini.',
            ], static function ($mail) use ($tenant): void {
                $mail->to($tenant->email)->subject('Booking Publik Baru - LYNERA');
            });
            return true;
        } catch (\Throwable $exception) {
            Log::warning('Failed to send tenant public booking email.', [
                'tenant_id' => $tenant->id,
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    public function sendTomorrowBookingReminderEmail(User $tenant, Booking $booking): bool
    {
        try {
            Mail::send('emails.notification', [
                'preheader' => 'Pengingat booking untuk besok',
                'badge' => 'Pengingat',
                'heading' => 'Pengingat: Ada Booking Besok',
                'intro' => sprintf('Halo %s, ini pengingat bahwa Anda memiliki jadwal booking besok. Berikut detailnya:', $tenant->name),
                'details' => [
                    'Pelanggan' => (string) ($booking->customer->name ?? '-'),
                    'Layanan' => (string) ($booking->service->name ?? '-'),
                    'Tanggal' => (string) ($booking->booking_date?->translatedFormat('d F Y') ?? '-'),
                    'Jam' => substr((string) ($booking->booking_time ?? ''), 0, 5),
                    'Lokasi' => (string) ($booking->location ?: '-'),
                ],
                'actionLabel' => 'Lihat Kalender',
                'actionUrl' => route('admin.calendar.index'),
                'outro' => 'Pastikan peralatan dan perlengkapan Anda sudah siap. Semangat berkarya!',
            ], static function ($mail) use ($tenant): void {
                $mail->to($tenant->email)->subject('Pengingat Booking Besok - LYNERA');
            });
            return true;
        } catch (\Throwable $exception) {
            Log::warning('Failed to send tomorrow booking email reminder.', [
                'tenant_id' => $tenant->id,
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    public function sendTomorrowBookingReminderWhatsApp(User $tenant, Booking $booking): void
    {
        $phone = $booking->customer->phone ?: null;
        if ($phone === null || trim($phone) === '') {
            return;
        }

        $message = sprintf(
            'Pengingat: Besok ada booking %s untuk %s pukul %s. Lokasi: %s.',
            (string) ($booking->service->name ?? '-'),
            (string) ($booking->customer->name ?? '-'),
            substr((string) ($booking->booking_time ?? ''), 0, 5),
            (string) ($booking->location ?: '-')
        );

        $this->sendWhatsApp($phone, $message);

        Log::info('Tomorrow booking WhatsApp reminder sent.', [
            'tenant_id' => $tenant->id,
            'booking_id' => $booking->id,
            'phone' => $phone,
        ]);
    }

    public function sendSubscriptionExpiringEmail(User $tenant, Subscription $subscription, int $daysLeft): bool
    {
        try {
            $planDetail = $this->planService->detail($subscription->plan);
            $planName = strtoupper((string) ($planDetail['name'] ?? $subscription->plan));
            $expiryLabel = $subscription->expired_at?->translatedFormat('d F Y') ?? '-';
            $daysText = $daysLeft <= 0 ? 'hari ini' : ('dalam '.$daysLeft.' hari');

            Mail::send('emails.notification', [
                'preheader' => 'Langganan LYNERA Anda akan berakhir '.$daysText,
                'badge' => 'Pengingat Langganan',
                'heading' => 'Langganan Anda Akan Segera Berakhir',
                'intro' => sprintf('Halo %s, paket %s Anda akan berakhir %s (%s). Perpanjang sekarang agar seluruh fitur bisnis Anda tetap aktif tanpa gangguan.', $tenant->name, $planName, $daysText, $expiryLabel),
                'details' => [
                    'Paket' => $planName,
                    'Berakhir Pada' => $expiryLabel,
                    'Sisa Waktu' => $daysLeft <= 0 ? 'Berakhir hari ini' : ($daysLeft.' hari lagi'),
                ],
                'actionLabel' => 'Perpanjang Sekarang',
                'actionUrl' => route('billing.index'),
                'outro' => 'Jika Anda sudah memperpanjang langganan, abaikan email ini.',
                'securityNote' => 'Perpanjangan hanya diproses melalui halaman Billing resmi LYNERA.',
            ], static function ($mail) use ($tenant): void {
                $mail->to($tenant->email)->subject('Langganan LYNERA Akan Berakhir - Segera Perpanjang');
            });

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Failed to send subscription expiring email.', [
                'tenant_id' => $tenant->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
