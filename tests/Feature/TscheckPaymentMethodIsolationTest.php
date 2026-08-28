<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\TenantPaymentAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TscheckPaymentMethodIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_cannot_mutate_another_tenants_payment_method(): void
    {
        $ownerTenant = $this->createTenant('tscheck-owner-'.uniqid().'@example.com');
        $otherTenant = $this->createTenant('tscheck-other-'.uniqid().'@example.com');

        $method = $ownerTenant->paymentAccounts()->create([
            'type' => TenantPaymentAccount::TYPE_BANK,
            'bank_name' => 'tscheck-isolation-bca',
            'account_name' => 'Owner Studio',
            'account_number' => '1234567890',
            'contact' => '0812000000',
            'is_active' => true,
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        // Attacker (other tenant) attempts to update
        $this->actingAs($otherTenant)
            ->put(route('profile.payment-methods.update', $method), [
                'type' => TenantPaymentAccount::TYPE_BANK,
                'bank_name' => 'HACKED',
                'account_name' => 'Hacker',
                'account_number' => '000',
                'is_active' => '1',
            ])
            ->assertNotFound();

        // Attacker attempts to toggle
        $this->actingAs($otherTenant)
            ->patch(route('profile.payment-methods.toggle', $method))
            ->assertNotFound();

        // Attacker attempts to make it primary
        $this->actingAs($otherTenant)
            ->patch(route('profile.payment-methods.primary', $method))
            ->assertNotFound();

        // Attacker attempts to delete
        $this->actingAs($otherTenant)
            ->delete(route('profile.payment-methods.destroy', $method))
            ->assertNotFound();

        $method->refresh();
        $this->assertSame('tscheck-isolation-bca', $method->bank_name);
        $this->assertTrue((bool) $method->is_active);
        $this->assertTrue((bool) $method->is_primary);
        $this->assertDatabaseHas('tenant_payment_accounts', [
            'id' => $method->id,
            'tenant_id' => $ownerTenant->id,
        ]);
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
