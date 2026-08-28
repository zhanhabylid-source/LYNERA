<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPlanFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_free_plan_blocks_booking_when_total_quota_is_reached(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        $user = $this->createTenantWithPlan(Subscription::PLAN_FREE);
        [$service, $customer] = $this->createServiceAndCustomer($user);
        $this->seedBookings($user, $service, $customer, 10);

        $response = $this->actingAs($user)
            ->from(route('onboarding.index'))
            ->post(route('onboarding.booking'), [
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'booking_date' => now()->addDays(14)->toDateString(),
                'booking_time' => '15:00',
                'status' => 'pending',
                'location' => 'Klien A',
                'notes' => 'Test limit free',
            ]);

        $response->assertRedirect(route('onboarding.index'));
        $response->assertSessionHasErrors(['plan']);
    }

    public function test_pro_plan_can_create_booking_above_free_quota(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        $user = $this->createTenantWithPlan(Subscription::PLAN_PRO);
        [$service, $customer] = $this->createServiceAndCustomer($user);
        $this->seedBookings($user, $service, $customer, 10);

        $response = $this->actingAs($user)
            ->post(route('onboarding.booking'), [
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'booking_date' => now()->addDays(14)->toDateString(),
                'booking_time' => '15:00',
                'status' => 'pending',
                'location' => 'Klien B',
                'notes' => 'Test pro unlimited',
            ]);

        $response->assertRedirect(route('admin.calendar.index', absolute: false));
        $this->assertDatabaseCount('bookings', 11);
    }

    public function test_expired_subscription_date_is_ignored_and_user_is_redirected_to_onboarding(): void
    {
        Carbon::setTestNow('2026-04-08 09:00:00');
        $user = $this->createTenantWithPlan(Subscription::PLAN_FREE, now()->subDay());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect('/onboarding');
    }

    private function createTenantWithPlan(string $plan, ?Carbon $expiredAt = null): User
    {
        $user = User::factory()->create();
        Subscription::query()->create([
            'user_id' => $user->id,
            'plan' => $plan,
            'expired_at' => $expiredAt,
        ]);

        return $user;
    }

    /**
     * @return array{Service, Customer}
     */
    private function createServiceAndCustomer(User $user): array
    {
        $service = Service::query()->create([
            'tenant_id' => $user->id,
            'name' => 'Soft LYNERA',
            'price' => 500000,
            'duration' => 60,
            'description' => 'Paket test',
        ]);

        $customer = Customer::query()->create([
            'tenant_id' => $user->id,
            'name' => 'Pelanggan Test',
            'phone' => '08123456789',
            'email' => 'pelanggan@example.com',
            'instagram' => null,
        ]);

        return [$service, $customer];
    }

    private function seedBookings(User $user, Service $service, Customer $customer, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $dayOffset = min(20, $i);
            Booking::query()->create([
                'tenant_id' => $user->id,
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'total_people' => 1,
                'booking_date' => now()->startOfMonth()->addDays($dayOffset)->toDateString(),
                'booking_time' => '08:00:00',
                'end_time' => '09:00:00',
                'status' => Booking::STATUS_PENDING,
                'location' => 'Lokasi seeded',
                'notes' => 'Seeded',
            ]);
        }
    }
}
