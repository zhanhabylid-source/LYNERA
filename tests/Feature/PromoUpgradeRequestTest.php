<?php

namespace Tests\Feature;

use App\Models\PlanOverride;
use App\Models\Subscription;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoUpgradeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_upgrade_request_stores_effective_promo_price_when_promo_is_active(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        PlanOverride::query()->create([
            'plan_key' => Subscription::PLAN_PRO,
            'price' => 'Rp 149.000',
            'promo_price' => 'Rp 99.000',
            'promo_label' => 'tscheck-upgrade-promo',
            'promo_is_active' => true,
            'promo_starts_at' => now()->subDay(),
            'promo_ends_at' => now()->addDays(30),
        ]);

        $user = User::factory()->create(['email' => 'tscheck-upgrade-promo-tenant@example.com']);
        Subscription::query()->create([
            'user_id' => $user->id,
            'plan' => Subscription::PLAN_FREE,
        ]);

        $response = $this->actingAs($user)->post(route('billing.upgrade-request'), [
            'requested_plan' => Subscription::PLAN_PRO,
            'request_note' => 'tscheck upgrade promo test',
        ]);

        $response->assertRedirect(route('billing.index'));

        $upgradeRequest = SubscriptionUpgradeRequest::query()
            ->where('tenant_id', $user->id)
            ->where('requested_plan', Subscription::PLAN_PRO)
            ->latest()
            ->first();

        $this->assertNotNull($upgradeRequest);
        $this->assertSame('Rp 99.000', $upgradeRequest->requested_price);
        $this->assertNotSame('Rp 149.000', $upgradeRequest->requested_price);
    }

    public function test_upgrade_request_stores_regular_price_when_promo_is_expired(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        PlanOverride::query()->create([
            'plan_key' => Subscription::PLAN_PRO,
            'price' => 'Rp 149.000',
            'promo_price' => 'Rp 99.000',
            'promo_label' => 'tscheck-upgrade-promo-expired',
            'promo_is_active' => true,
            'promo_starts_at' => now()->subDays(30),
            'promo_ends_at' => now()->subDay(),
        ]);

        $user = User::factory()->create(['email' => 'tscheck-upgrade-expired-tenant@example.com']);
        Subscription::query()->create([
            'user_id' => $user->id,
            'plan' => Subscription::PLAN_FREE,
        ]);

        $response = $this->actingAs($user)->post(route('billing.upgrade-request'), [
            'requested_plan' => Subscription::PLAN_PRO,
        ]);

        $response->assertRedirect(route('billing.index'));

        $upgradeRequest = SubscriptionUpgradeRequest::query()
            ->where('tenant_id', $user->id)
            ->where('requested_plan', Subscription::PLAN_PRO)
            ->latest()
            ->first();

        $this->assertNotNull($upgradeRequest);
        $this->assertSame('Rp 149.000', $upgradeRequest->requested_price);
    }
}
