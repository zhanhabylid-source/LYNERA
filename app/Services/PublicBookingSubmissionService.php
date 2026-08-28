<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Services\Payments\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PublicBookingSubmissionService
{
    public function __construct(
        private readonly PublicBookingFormService $publicBookingFormService,
        private readonly SubscriptionService $subscriptionService,
        private readonly PaymentService $paymentService,
        private readonly GoogleCalendarService $googleCalendarService,
        private readonly NotificationService $notificationService
    ) {
    }

    public function submit(string $token, array $data): Booking
    {
        $form = $this->publicBookingFormService->findAccessibleByToken($token);
        if ($form === null) {
            throw new InvalidArgumentException('Tautan booking tidak valid atau sudah kedaluwarsa.');
        }

        $tenantId = (int) $form->tenant_id;
        $tenant = User::query()->find($tenantId);
        if ($tenant === null) {
            throw new InvalidArgumentException('Akun tenant tidak ditemukan.');
        }
        $terms = $this->publicBookingFormService->resolveTerms($form, $tenant);
        $allowedServiceIds = $this->publicBookingFormService->getAllowedServiceIds($form);
        $serviceId = (int) $data['service_id'];
        if (! in_array($serviceId, $allowedServiceIds, true)) {
            throw new InvalidArgumentException('Layanan yang dipilih tidak tersedia pada form booking ini.');
        }

        $service = Service::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->find($serviceId);
        if ($service === null) {
            throw new InvalidArgumentException('Layanan yang dipilih tidak ditemukan.');
        }

        $this->subscriptionService->assertBookingCreationAllowed($tenantId);

        $peopleCount = max(1, (int) $data['people_count']);
        $bookingTime = $this->normalizeTime($data['booking_time']);
        $endTime = now()->setTimeFromTimeString($bookingTime)->addMinutes(((int) $service->duration) * $peopleCount)->format('H:i:s');
        $bookingDate = (string) $data['booking_date'];
        $serviceLocation = (string) ($data['service_location'] ?? 'home_service');
        $resolvedLocation = $this->resolveLocationByType($serviceLocation, $data, $tenant);
        $defaultTransportFee = $this->publicBookingFormService->resolveTransportFee($form, $tenant);
        $transportFee = $serviceLocation === 'studio'
            ? 0.0
            : round(max(0, (float) ($data['transport_fee'] ?? $defaultTransportFee)), 2);

        $this->assertFutureDateTime($bookingDate, $bookingTime);
        $this->assertAvailability($tenantId, $bookingDate, $bookingTime, $endTime);

        // Guest requests have no authenticated user, which makes TenantScope
        // blank out every relation load. Force the tenant context for the whole
        // submission so payment creation / notifications resolve correctly.
        TenantScope::usingTenant($tenantId);

        try {
            $booking = DB::transaction(function () use ($tenantId, $data, $service, $peopleCount, $bookingTime, $endTime, $bookingDate, $resolvedLocation, $terms, $transportFee) {
                $customer = $this->findOrCreateCustomer($tenantId, $data);

                $booking = Booking::withoutGlobalScopes()->create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $customer->id,
                    'service_id' => $service->id,
                    'total_people' => $peopleCount,
                    'booking_date' => $bookingDate,
                    'booking_time' => $bookingTime,
                    'end_time' => $endTime,
                    'location' => $resolvedLocation,
                    'transport_fee' => $transportFee,
                    'status' => Booking::STATUS_PENDING,
                    'notes' => $data['notes'] ?? null,
                    'terms_accepted_at' => $data['terms_accepted_at'] ?? now(),
                    'terms_version' => null,
                    'terms_snapshot' => $terms['title']."\n\n".$terms['content'],
                    'terms_acceptance_ip' => $data['terms_acceptance_ip'] ?? null,
                    'terms_acceptance_user_agent' => $data['terms_acceptance_user_agent'] ?? null,
                ]);

                BookingItem::withoutGlobalScopes()->create([
                    'tenant_id' => $tenantId,
                    'booking_id' => $booking->id,
                    'service_id' => $service->id,
                    'people_count' => $peopleCount,
                    'unit_price' => (float) $service->price,
                    'duration_minutes' => (int) $service->duration * $peopleCount,
                    'subtotal' => (float) $service->price * $peopleCount,
                ]);

                $bookingModel = $booking->load(['service', 'customer', 'bookingItems']);

                $this->paymentService->createForBooking($bookingModel);

                return $bookingModel;
            });

            $this->googleCalendarService->syncBooking($booking);
            $this->notificationService->sendWhatsApp(
                $booking->customer->phone,
                sprintf(
                    'Halo %s, booking Anda untuk %s pada %s sudah kami terima.',
                    $booking->customer->name,
                    $booking->service->name,
                    $booking->booking_date?->format('d M Y')
                )
            );
            $this->notificationService->sendPublicBookingAlert($tenant, $booking);

            $this->publicBookingFormService->incrementSubmission($form);
        } finally {
            TenantScope::forgetTenant();
        }

        return $booking;
    }

    private function findOrCreateCustomer(int $tenantId, array $data): Customer
    {
        $phone = $this->normalizePhone((string) $data['phone']);

        $existing = Customer::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('phone', $phone)
            ->first();

        if ($existing !== null) {
            $existing->fill([
                'name' => $data['name'],
                'email' => $data['email'] ?? $existing->email,
            ])->save();

            return $existing->refresh();
        }

        return Customer::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'phone' => $phone,
            'email' => $data['email'] ?? null,
            'instagram' => null,
        ]);
    }

    private function assertAvailability(int $tenantId, string $date, string $start, string $end): void
    {
        $hasConflict = Booking::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereDate('booking_date', $date)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->where(function ($query) use ($start, $end) {
                $query->where('booking_time', '<', $end)
                    ->where('end_time', '>', $start);
            })
            ->exists();

        if ($hasConflict) {
            throw new InvalidArgumentException('Jadwal yang dipilih bentrok dengan booking lain.');
        }
    }

    private function assertFutureDateTime(string $date, string $time): void
    {
        if (Carbon::parse($date.' '.$time)->isPast()) {
            throw new InvalidArgumentException('Tanggal dan waktu booking harus di masa depan.');
        }
    }

    private function normalizePhone(string $phone): string
    {
        $normalized = preg_replace('/\s+/', '', $phone) ?? $phone;

        return Str::of($normalized)->replaceMatches('/[^0-9+]/', '')->value();
    }

    private function normalizeTime(string $time): string
    {
        if (preg_match('/^\d{2}:\d{2}$/', $time) === 1) {
            return $time.':00';
        }

        return now()->setTimeFromTimeString($time)->format('H:i:s');
    }

    private function resolveLocationByType(string $serviceLocation, array $data, User $tenant): string
    {
        if ($serviceLocation === 'studio') {
            $studio = trim((string) ($tenant->studio_maps_link ?: $tenant->studio_location ?: ''));
            if ($studio === '') {
                throw new InvalidArgumentException('Lokasi studio belum dikonfigurasi oleh MUA.');
            }

            return $studio;
        }

        $location = trim((string) ($data['location'] ?? ''));
        if ($location === '') {
            throw new InvalidArgumentException('Lokasi wajib diisi untuk layanan ke rumah.');
        }

        return $location;
    }

}
