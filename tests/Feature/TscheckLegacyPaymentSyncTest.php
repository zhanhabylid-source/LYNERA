<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\TenantPaymentAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TscheckLegacyPaymentSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_payment_fields_follow_active_primary_method(): void
    {
        $tenant = $this->createTenant('tscheck-legacy-sync-'.uniqid().'@example.com');

        // Create first (primary) method via controller store action.
        $this->actingAs($tenant)->post(route('profile.payment-methods.store'), [
            'type' => TenantPaymentAccount::TYPE_BANK,
            'bank_name' => 'tscheck-legacy-bca',
            'account_name' => 'Studio A',
            'account_number' => '111222333',
            'contact' => '0811111111',
            'is_active' => '1',
            'is_primary' => '1',
        ])->assertRedirect();

        $tenant->refresh();
        $this->assertSame('tscheck-legacy-bca', $tenant->payment_bank_name);
        $this->assertSame('111222333', $tenant->payment_account_number);

        // Create second method and make it primary -> legacy should now follow it.
        $this->actingAs($tenant)->post(route('profile.payment-methods.store'), [
            'type' => TenantPaymentAccount::TYPE_EWALLET,
            'bank_name' => 'tscheck-legacy-gopay',
            'account_name' => 'Studio A',
            'account_number' => '0822222222',
            'contact' => '0822222222',
            'is_active' => '1',
            'is_primary' => '1',
        ])->assertRedirect();

        $tenant->refresh();
        $secondMethod = TenantPaymentAccount::query()
            ->where('tenant_id', $tenant->id)
            ->where('bank_name', 'tscheck-legacy-gopay')
            ->firstOrFail();
        $firstMethod = TenantPaymentAccount::query()
            ->where('tenant_id', $tenant->id)
            ->where('bank_name', 'tscheck-legacy-bca')
            ->firstOrFail();

        $this->assertSame('tscheck-legacy-gopay', $tenant->payment_bank_name);
        $this->assertSame('0822222222', $tenant->payment_account_number);
        $this->assertFalse((bool) $firstMethod->is_primary);
        $this->assertTrue((bool) $secondMethod->is_primary);

        // Deactivating the current primary should reassign primary+legacy back to the remaining active method.
        $this->actingAs($tenant)
            ->patch(route('profile.payment-methods.toggle', $secondMethod))
            ->assertRedirect();

        $tenant->refresh();
        $firstMethod->refresh();
        $this->assertTrue((bool) $firstMethod->is_primary);
        $this->assertSame('tscheck-legacy-bca', $tenant->payment_bank_name);
        $this->assertSame('111222333', $tenant->payment_account_number);

        // Deactivating the last remaining active method clears legacy fields.
        $this->actingAs($tenant)
            ->patch(route('profile.payment-methods.toggle', $firstMethod))
            ->assertRedirect();

        $tenant->refresh();
        $this->assertNull($tenant->payment_bank_name);
        $this->assertNull($tenant->payment_account_number);
        $this->assertNull($tenant->payment_account_name);
    }

    private function createTenant(string $email): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'onboarding_completed_at' => now(),
        ]);

        Subscription::query()->create([
            'user_id' => $user->id,
            'plan' => Subscription::PLAN_FREE,
            'bookings_consumed_total' => 0,
            'expired_at' => null,
        ]);

        return $user;
    }
}
