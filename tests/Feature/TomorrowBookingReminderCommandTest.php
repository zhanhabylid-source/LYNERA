<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TomorrowBookingReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_reminder_only_for_users_with_notification_enabled(): void
    {
        $enabledUser = User::factory()->create([
            'notify_tomorrow_booking' => true,
            'email' => 'enabled@example.com',
        ]);
        $disabledUser = User::factory()->create([
            'notify_tomorrow_booking' => false,
            'email' => 'disabled@example.com',
        ]);

        $enabledService = Service::query()->create([
            'tenant_id' => $enabledUser->id,
            'name' => 'Soft LYNERA',
            'price' => 500000,
            'duration' => 120,
        ]);
        $enabledCustomer = Customer::query()->create([
            'tenant_id' => $enabledUser->id,
            'name' => 'Client Enabled',
            'phone' => '081234567890',
            'email' => 'client-enabled@example.com',
        ]);
        $disabledService = Service::query()->create([
            'tenant_id' => $disabledUser->id,
            'name' => 'Bold LYNERA',
            'price' => 650000,
            'duration' => 150,
        ]);
        $disabledCustomer = Customer::query()->create([
            'tenant_id' => $disabledUser->id,
            'name' => 'Client Disabled',
            'phone' => '089876543210',
            'email' => 'client-disabled@example.com',
        ]);

        $enabledBooking = Booking::withoutGlobalScopes()->create([
            'tenant_id' => $enabledUser->id,
            'customer_id' => $enabledCustomer->id,
            'service_id' => $enabledService->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => Booking::STATUS_PENDING,
        ]);

        $disabledBooking = Booking::withoutGlobalScopes()->create([
            'tenant_id' => $disabledUser->id,
            'customer_id' => $disabledCustomer->id,
            'service_id' => $disabledService->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '13:00:00',
            'end_time' => '15:00:00',
            'status' => Booking::STATUS_PENDING,
        ]);

        $this->artisan('bookings:send-tomorrow-reminders')
            ->assertSuccessful();

        $this->assertNotNull($enabledBooking->fresh()->tomorrow_reminder_sent_at);
        $this->assertNull($disabledBooking->fresh()->tomorrow_reminder_sent_at);
    }
}
