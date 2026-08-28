<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingLifecycleRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_past_booking_cannot_be_edited(): void
    {
        Carbon::setTestNow('2026-04-08 12:00:00');
        [$user, $service, $customer] = $this->createTenantContext();
        $booking = $this->createBooking($user, $service, $customer, now()->subDay()->toDateString(), '09:00:00');

        $response = $this->actingAs($user)
            ->from(route('admin.bookings.edit', $booking))
            ->put(route('admin.bookings.update', $booking), [
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '10:00',
                'status' => 'pending',
                'location' => 'Lokasi baru',
                'notes' => 'Catatan baru',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['booking_time']);
        $this->assertSame('09:00:00', (string) $booking->fresh()->booking_time);
    }

    public function test_past_booking_cannot_be_deleted(): void
    {
        Carbon::setTestNow('2026-04-08 12:00:00');
        [$user, $service, $customer] = $this->createTenantContext();
        $booking = $this->createBooking($user, $service, $customer, now()->subDay()->toDateString(), '09:00:00');

        $response = $this->actingAs($user)
            ->from(route('admin.bookings.index'))
            ->delete(route('admin.bookings.destroy', $booking));

        $response->assertRedirect(route('admin.bookings.index', absolute: false));
        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_deleting_future_booking_does_not_restore_plan_quota(): void
    {
        Carbon::setTestNow('2026-04-08 12:00:00');
        [$user, $service, $customer] = $this->createTenantContext();

        $createResponse = $this->actingAs($user)
            ->post(route('admin.bookings.store'), [
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'booking_date' => now()->addDays(2)->toDateString(),
                'booking_time' => '10:00',
                'status' => 'pending',
                'location' => 'Lokasi test',
                'notes' => 'Booking untuk test kuota',
            ]);

        $createResponse->assertRedirect(route('admin.bookings.index', absolute: false));
        $booking = Booking::withoutGlobalScopes()->where('tenant_id', $user->id)->latest('id')->firstOrFail();

        $deleteResponse = $this->actingAs($user)
            ->delete(route('admin.bookings.destroy', $booking));

        $deleteResponse->assertRedirect(route('admin.bookings.index', absolute: false));
        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);

        $usage = app(SubscriptionService::class)->getBookingUsage($user->id);
        $this->assertSame(1, $usage['bookings_count']);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'bookings_consumed_total' => 1,
        ]);
    }

    public function test_canceled_booking_cannot_access_payment_page(): void
    {
        Carbon::setTestNow('2026-04-08 12:00:00');
        [$user, $service, $customer] = $this->createTenantContext();
        $booking = $this->createBooking($user, $service, $customer, now()->addDay()->toDateString(), '09:00:00');
        $booking->forceFill([
            'status' => Booking::STATUS_CANCELED,
        ])->save();

        $payNowResponse = $this->actingAs($user)
            ->from(route('admin.bookings.show', $booking))
            ->post(route('admin.bookings.pay-now', $booking));
        $payNowResponse->assertRedirect(route('admin.bookings.show', $booking, absolute: false));
        $payNowResponse->assertSessionHasErrors(['booking']);

        $paymentPageResponse = $this->actingAs($user)
            ->from(route('admin.bookings.show', $booking))
            ->get(route('admin.payments.index', ['booking_id' => $booking->id]));
        $paymentPageResponse->assertRedirect(route('admin.bookings.show', $booking, absolute: false));
        $paymentPageResponse->assertSessionHasErrors(['booking']);
    }

    /**
     * @return array{User, Service, Customer}
     */
    private function createTenantContext(): array
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => now(),
        ]);

        Subscription::query()->create([
            'user_id' => $user->id,
            'plan' => Subscription::PLAN_FREE,
            'bookings_consumed_total' => 0,
            'expired_at' => null,
        ]);

        $service = Service::query()->create([
            'tenant_id' => $user->id,
            'name' => 'Soft LYNERA',
            'price' => 500000,
            'duration' => 60,
            'description' => 'Test layanan',
        ]);

        $customer = Customer::query()->create([
            'tenant_id' => $user->id,
            'name' => 'Pelanggan Test',
            'phone' => '08123456789',
            'email' => 'pelanggan@example.com',
        ]);

        return [$user, $service, $customer];
    }

    private function createBooking(User $user, Service $service, Customer $customer, string $date, string $startTime): Booking
    {
        return Booking::withoutGlobalScopes()->create([
            'tenant_id' => $user->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'total_people' => 1,
            'booking_date' => $date,
            'booking_time' => $startTime,
            'end_time' => '10:00:00',
            'status' => Booking::STATUS_PENDING,
            'location' => 'Test lokasi',
            'notes' => 'Test booking',
        ]);
    }
}
