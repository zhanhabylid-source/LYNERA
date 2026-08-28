<?php

namespace Tests\Feature;

use App\Models\PlanOverride;
use App\Models\Subscription;
use App\Services\PlanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoPlanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_active_promo_replaces_regular_price_when_enabled_and_within_window(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        PlanOverride::query()->create([
            'plan_key' => Subscription::PLAN_PRO,
            'price' => 'Rp 149.000',
            'promo_price' => 'Rp 99.000',
            'promo_label' => 'tscheck-promo-active',
            'promo_is_active' => true,
            'promo_starts_at' => now()->subDay(),
            'promo_ends_at' => now()->addDays(30),
        ]);

        $detail = app(PlanService::class)->detail(Subscription::PLAN_PRO);

        $this->assertTrue($detail['promo_active']);
        $this->assertSame('active', $detail['promo_status']);
        $this->assertSame('Rp 99.000', $detail['effective_price']);
        $this->assertSame('Rp 149.000', $detail['regular_price']);
    }

    public function test_scheduled_promo_does_not_replace_regular_price_before_start(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        PlanOverride::query()->create([
            'plan_key' => Subscription::PLAN_PRO,
            'price' => 'Rp 149.000',
            'promo_price' => 'Rp 99.000',
            'promo_label' => 'tscheck-promo-scheduled',
            'promo_is_active' => true,
            'promo_starts_at' => now()->addDays(5),
            'promo_ends_at' => now()->addDays(30),
        ]);

        $detail = app(PlanService::class)->detail(Subscription::PLAN_PRO);

        $this->assertFalse($detail['promo_active']);
        $this->assertSame('scheduled', $detail['promo_status']);
        $this->assertSame('Rp 149.000', $detail['effective_price']);
    }

    public function test_expired_promo_does_not_replace_regular_price_after_end(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        PlanOverride::query()->create([
            'plan_key' => Subscription::PLAN_PREMIUM,
            'price' => 'Rp 249.000',
            'promo_price' => 'Rp 199.000',
            'promo_label' => 'tscheck-promo-expired',
            'promo_is_active' => true,
            'promo_starts_at' => now()->subDays(30),
            'promo_ends_at' => now()->subDay(),
        ]);

        $detail = app(PlanService::class)->detail(Subscription::PLAN_PREMIUM);

        $this->assertFalse($detail['promo_active']);
        $this->assertSame('expired', $detail['promo_status']);
        $this->assertSame('Rp 249.000', $detail['effective_price']);
    }

    public function test_inactive_flag_does_not_replace_regular_price_even_within_window(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        PlanOverride::query()->create([
            'plan_key' => Subscription::PLAN_PREMIUM,
            'price' => 'Rp 249.000',
            'promo_price' => 'Rp 199.000',
            'promo_label' => 'tscheck-promo-disabled',
            'promo_is_active' => false,
            'promo_starts_at' => now()->subDay(),
            'promo_ends_at' => now()->addDays(30),
        ]);

        $detail = app(PlanService::class)->detail(Subscription::PLAN_PREMIUM);

        $this->assertFalse($detail['promo_active']);
        $this->assertSame('inactive', $detail['promo_status']);
        $this->assertSame('Rp 249.000', $detail['effective_price']);
    }
}
