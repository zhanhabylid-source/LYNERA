<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_subscription_is_backfilled_and_can_open_onboarding(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('onboarding.index'));

        $response->assertOk();
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan' => Subscription::PLAN_FREE,
        ]);
    }

    public function test_user_is_redirected_to_onboarding_when_incomplete(): void
    {
        $user = User::factory()->create();
        Subscription::query()->create([
            'user_id' => $user->id,
            'plan' => Subscription::PLAN_FREE,
            'expired_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/onboarding');
    }

    public function test_user_can_complete_onboarding_flow_and_access_dashboard(): void
    {
        $user = User::factory()->create();
        Subscription::query()->create([
            'user_id' => $user->id,
            'plan' => Subscription::PLAN_FREE,
            'expired_at' => now()->addDays(7),
        ]);

        $this->actingAs($user)
            ->post(route('onboarding.profile'), [
                'name' => 'LYNERA Owner',
                'studio_name' => 'LYNERA Flow Studio',
                'studio_location' => 'Jakarta',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('onboarding.service'), [
                'name' => 'Soft LYNERA',
                'price' => 500000,
                'duration' => 120,
                'description' => 'Natural soft LYNERA package',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('onboarding.customer'), [
                'name' => 'First Client',
                'phone' => '08123456789',
                'email' => 'client@example.com',
            ])
            ->assertRedirect();

        $serviceId = $user->services()->value('id');
        $customerId = $user->customers()->value('id');

        $this->actingAs($user)
            ->post(route('onboarding.booking'), [
                'customer_id' => $customerId,
                'service_id' => $serviceId,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '10:00',
                'status' => 'pending',
                'location' => 'Client address',
                'notes' => 'First onboarding booking',
            ])
            ->assertRedirect(route('admin.calendar.index', absolute: false));

        $this->assertNotNull($user->fresh()->onboarding_completed_at);

        $this->actingAs($user)
            ->get(route('admin.calendar.index'))
            ->assertOk();
    }

    public function test_onboarding_booking_conflict_returns_helpful_error(): void
    {
        $user = User::factory()->create();
        Subscription::query()->create([
            'user_id' => $user->id,
            'plan' => Subscription::PLAN_FREE,
            'expired_at' => now()->addDays(7),
        ]);

        $this->actingAs($user)->post(route('onboarding.service'), [
            'name' => 'Soft LYNERA',
            'price' => 500000,
            'duration' => 120,
            'description' => 'Natural soft LYNERA package',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('onboarding.customer'), [
            'name' => 'First Client',
            'phone' => '08123456789',
            'email' => 'client@example.com',
        ])->assertRedirect();

        $serviceId = $user->services()->value('id');
        $customerId = $user->customers()->value('id');
        $slotDate = now()->addDays(2)->toDateString();

        $this->actingAs($user)->post(route('onboarding.booking'), [
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'booking_date' => $slotDate,
            'booking_time' => '10:00',
            'status' => 'pending',
            'location' => 'Client address',
            'notes' => 'First onboarding booking',
        ])->assertRedirect(route('admin.calendar.index', absolute: false));

        $conflictResponse = $this->actingAs($user)->from(route('onboarding.index'))->post(route('onboarding.booking'), [
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'booking_date' => $slotDate,
            'booking_time' => '10:00',
            'status' => 'pending',
            'location' => 'Client address',
            'notes' => 'Second booking conflict',
        ]);

        $conflictResponse->assertRedirect(route('onboarding.index'));
        $conflictResponse->assertSessionHasErrors(['booking_time']);
    }
}
