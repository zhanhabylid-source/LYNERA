<?php

namespace Tests\Feature;

use App\Models\PlanOverride;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoBackendPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_super_admin_can_save_promo_settings_for_a_plan(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $admin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'tscheck-plan-admin@example.com',
        ]);

        $response = $this->actingAs($admin)->put(route('backend.plans.update', Subscription::PLAN_PRO), [
            'name' => 'Pro',
            'price' => 'Rp 149.000',
            'promo_price' => 'Rp 99.000',
            'promo_label' => 'tscheck-launching-promo',
            'promo_is_active' => '1',
            'promo_starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'promo_ends_at' => now()->addDays(90)->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('backend.plans.index'));
        $response->assertSessionHas('success');

        $override = PlanOverride::query()->where('plan_key', Subscription::PLAN_PRO)->first();
        $this->assertNotNull($override);
        $this->assertSame('Rp 99.000', $override->promo_price);
        $this->assertSame('tscheck-launching-promo', $override->promo_label);
        $this->assertTrue($override->promo_is_active);
    }

    public function test_non_super_admin_cannot_access_plan_management(): void
    {
        $tenant = User::factory()->create([
            'role' => 'tenant',
            'email' => 'tscheck-plan-tenant@example.com',
        ]);

        $response = $this->actingAs($tenant)->get(route('backend.plans.index'));

        $response->assertStatus(403);
    }
}
