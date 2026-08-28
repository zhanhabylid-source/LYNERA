<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMilestoneFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_booking_creation_generates_dp_minimum_amount(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        [$user, $service, $customer] = $this->createTenantContext();

        $this->actingAs($user)
            ->post(route('admin.bookings.store'), [
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'booking_date' => now()->addDays(2)->toDateString(),
                'booking_time' => '10:00',
                'status' => 'pending',
                'location' => 'Lokasi test',
                'notes' => 'Booking test DP',
            ])
            ->assertRedirect(route('admin.bookings.index', absolute: false));

        $payment = Payment::query()->latest('id')->firstOrFail();
        $this->assertSame(500000.0, (float) $payment->amount);
        $this->assertSame(150000.0, (float) $payment->dp_amount);
        $this->assertSame(0.0, (float) $payment->paid_amount);
        $this->assertNull($payment->dp_paid_at);
    }

    public function test_confirm_booking_is_blocked_when_dp_unpaid(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        [$user, $service, $customer] = $this->createTenantContext();
        $booking = $this->createBooking($user, $service, $customer, now()->addDays(2)->toDateString(), '10:00:00');

        $response = $this->actingAs($user)
            ->from(route('admin.calendar.index'))
            ->patch(route('admin.bookings.confirm', $booking));

        $response->assertRedirect(route('admin.calendar.index', absolute: false));
        $response->assertSessionHasErrors(['calendar']);
        $this->assertSame(Booking::STATUS_PENDING, (string) $booking->fresh()->status);
    }

    public function test_settlement_allowed_anytime_before_service_date_passed(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        [$user, $service, $customer] = $this->createTenantContext();
        $booking = $this->createBooking($user, $service, $customer, now()->addDays(2)->toDateString(), '10:00:00');
        $payment = Payment::query()->create([
            'tenant_id' => $user->id,
            'booking_id' => $booking->id,
            'amount' => 500000,
            'dp_amount' => 150000,
            'paid_amount' => 0,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => Payment::METHOD_MANUAL,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.payments.mark-dp-paid', $payment), [
                'dp_amount' => 200000,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(200000.0, (float) $payment->fresh()->dp_amount);
        $this->assertSame(200000.0, (float) $payment->fresh()->paid_amount);

        $this->actingAs($user)
            ->patch(route('admin.payments.mark-settled', $payment))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $updatedPayment = $payment->fresh();
        $this->assertSame(Payment::STATUS_PAID, (string) $updatedPayment->status);
        $this->assertSame(500000.0, (float) $updatedPayment->paid_amount);
        $this->assertNotNull($updatedPayment->paid_at);
    }

    public function test_manual_settlement_blocked_when_service_has_passed(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        [$user, $service, $customer] = $this->createTenantContext();
        $booking = $this->createBooking($user, $service, $customer, now()->subDay()->toDateString(), '10:00:00');
        $payment = Payment::query()->create([
            'tenant_id' => $user->id,
            'booking_id' => $booking->id,
            'amount' => 500000,
            'dp_amount' => 150000,
            'paid_amount' => 150000,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => Payment::METHOD_MANUAL,
            'dp_paid_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->from(route('admin.payments.index'))
            ->patch(route('admin.payments.mark-settled', $payment));

        $response->assertRedirect(route('admin.payments.index', absolute: false));
        $response->assertSessionHasErrors(['payment']);
        $this->assertSame(Payment::STATUS_PENDING, (string) $payment->fresh()->status);
        $this->assertSame(150000.0, (float) $payment->fresh()->paid_amount);
    }

    public function test_manual_dp_cannot_exceed_total_payment(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        [$user, $service, $customer] = $this->createTenantContext();
        $booking = $this->createBooking($user, $service, $customer, now()->addDays(2)->toDateString(), '10:00:00');
        $payment = Payment::query()->create([
            'tenant_id' => $user->id,
            'booking_id' => $booking->id,
            'amount' => 500000,
            'dp_amount' => 150000,
            'paid_amount' => 0,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => Payment::METHOD_MANUAL,
        ]);

        $response = $this->actingAs($user)
            ->from(route('admin.payments.index'))
            ->patch(route('admin.payments.mark-dp-paid', $payment), [
                'dp_amount' => 700000,
            ]);

        $response->assertRedirect(route('admin.payments.index', absolute: false));
        $response->assertSessionHasErrors(['payment']);
        $this->assertSame(150000.0, (float) $payment->fresh()->dp_amount);
        $this->assertSame(0.0, (float) $payment->fresh()->paid_amount);
    }

    public function test_cancel_booking_keeps_dp_as_revenue(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        [$user, $service, $customer] = $this->createTenantContext();
        $booking = $this->createBooking($user, $service, $customer, now()->addDays(2)->toDateString(), '10:00:00');
        $payment = Payment::query()->create([
            'tenant_id' => $user->id,
            'booking_id' => $booking->id,
            'amount' => 500000,
            'dp_amount' => 150000,
            'paid_amount' => 150000,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => Payment::METHOD_MANUAL,
            'dp_paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('admin.payments.cancel-booking', $payment))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(Booking::STATUS_CANCELED, (string) $booking->fresh()->status);
        $this->assertSame(150000.0, (float) $payment->fresh()->paid_amount);

        $summary = app(PaymentService::class)->getRevenueSummary();
        $this->assertSame(150000.0, (float) $summary['total_revenue']);
    }

    public function test_mark_dp_blocked_for_canceled_booking(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        [$user, $service, $customer] = $this->createTenantContext();
        $booking = $this->createBooking($user, $service, $customer, now()->addDays(2)->toDateString(), '10:00:00');
        $booking->forceFill([
            'status' => Booking::STATUS_CANCELED,
        ])->save();

        $payment = Payment::query()->create([
            'tenant_id' => $user->id,
            'booking_id' => $booking->id,
            'amount' => 500000,
            'dp_amount' => 150000,
            'paid_amount' => 0,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => Payment::METHOD_MANUAL,
        ]);

        $response = $this->actingAs($user)
            ->from(route('admin.bookings.show', $booking))
            ->patch(route('admin.payments.mark-dp-paid', $payment), [
                'dp_amount' => 150000,
            ]);

        $response->assertRedirect(route('admin.bookings.show', $booking, absolute: false));
        $response->assertSessionHasErrors(['payment']);
        $this->assertSame(0.0, (float) $payment->fresh()->paid_amount);
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
            'end_time' => '11:00:00',
            'status' => Booking::STATUS_PENDING,
            'location' => 'Test lokasi',
            'notes' => 'Test booking',
        ]);
    }
}
