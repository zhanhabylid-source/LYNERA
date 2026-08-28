<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAutoSettleCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_command_auto_settles_passed_service_booking_except_canceled_booking(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        [$user, $service, $customer] = $this->createTenantContext();

        $activeBooking = Booking::withoutGlobalScopes()->create([
            'tenant_id' => $user->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'total_people' => 1,
            'booking_date' => now()->subDay()->toDateString(),
            'booking_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => Booking::STATUS_CONFIRMED,
            'location' => 'Lokasi A',
        ]);

        $canceledBooking = Booking::withoutGlobalScopes()->create([
            'tenant_id' => $user->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'total_people' => 1,
            'booking_date' => now()->subDay()->toDateString(),
            'booking_time' => '11:00:00',
            'end_time' => '12:00:00',
            'status' => Booking::STATUS_CANCELED,
            'location' => 'Lokasi B',
        ]);

        $activePayment = Payment::query()->create([
            'tenant_id' => $user->id,
            'booking_id' => $activeBooking->id,
            'amount' => 500000,
            'dp_amount' => 150000,
            'paid_amount' => 150000,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => Payment::METHOD_MANUAL,
            'dp_paid_at' => now()->subDay(),
        ]);

        $canceledPayment = Payment::query()->create([
            'tenant_id' => $user->id,
            'booking_id' => $canceledBooking->id,
            'amount' => 400000,
            'dp_amount' => 120000,
            'paid_amount' => 120000,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => Payment::METHOD_MANUAL,
            'dp_paid_at' => now()->subDay(),
        ]);

        $this->artisan('payments:auto-settle-past-service')
            ->assertSuccessful();

        $this->assertSame(Payment::STATUS_PAID, (string) $activePayment->fresh()->status);
        $this->assertSame(500000.0, (float) $activePayment->fresh()->paid_amount);
        $this->assertNotNull($activePayment->fresh()->paid_at);

        $this->assertSame(Payment::STATUS_PENDING, (string) $canceledPayment->fresh()->status);
        $this->assertSame(120000.0, (float) $canceledPayment->fresh()->paid_amount);
        $this->assertNull($canceledPayment->fresh()->paid_at);
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
}
