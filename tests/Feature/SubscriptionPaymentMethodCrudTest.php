<?php

namespace Tests\Feature;

use App\Models\SubscriptionPaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPaymentMethodCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_edit_set_primary_and_delete_payment_method(): void
    {
        $suffix = uniqid();
        $superAdmin = User::factory()->create([
            'email' => 'tscheck-superadmin-'.$suffix.'@example.com',
            'role' => 'super_admin',
        ]);

        $createResponse = $this->actingAs($superAdmin)->post(route('backend.payment-methods.store'), [
            'type' => SubscriptionPaymentMethod::TYPE_BANK,
            'provider_name' => 'tscheck-bank-'.$suffix,
            'account_name' => 'TSCheck Account',
            'account_number' => '1234567890',
            'is_active' => '1',
            'is_primary' => '1',
        ]);

        $createResponse->assertSessionHasNoErrors();
        $createResponse->assertRedirect();

        $method = SubscriptionPaymentMethod::query()->where('provider_name', 'tscheck-bank-'.$suffix)->firstOrFail();
        $this->assertTrue($method->is_active);
        $this->assertTrue($method->is_primary);

        $updateResponse = $this->actingAs($superAdmin)->put(route('backend.payment-methods.update', $method), [
            'type' => SubscriptionPaymentMethod::TYPE_BANK,
            'provider_name' => 'tscheck-bank-'.$suffix.'-edited',
            'account_number' => '999888777',
            'is_active' => '1',
            'is_primary' => '1',
        ]);
        $updateResponse->assertSessionHasNoErrors();
        $method->refresh();
        $this->assertSame('tscheck-bank-'.$suffix.'-edited', $method->provider_name);

        $toggleResponse = $this->actingAs($superAdmin)->patch(route('backend.payment-methods.toggle', $method));
        $toggleResponse->assertRedirect();
        $this->assertFalse($method->fresh()->is_active);

        // Reactivate before setting primary since primary requires active.
        SubscriptionPaymentMethod::whereKey($method->id)->update(['is_active' => true]);
        $reloaded = SubscriptionPaymentMethod::query()->find($method->id);
        $this->assertTrue($reloaded->is_active, 'reactivate failed id='.$method->id.' raw='.json_encode($reloaded));
        $primaryResponse = $this->actingAs($superAdmin)->patch(route('backend.payment-methods.primary', $method));
        $primaryResponse->assertRedirect();
        $primaryResponse->assertSessionDoesntHaveErrors();
        $this->assertTrue($method->fresh()->is_primary, 'session error: '.json_encode(session('error')));

        $deleteResponse = $this->actingAs($superAdmin)->delete(route('backend.payment-methods.destroy', $method));
        $deleteResponse->assertRedirect();
        $this->assertNull(SubscriptionPaymentMethod::query()->find($method->id));
    }

    public function test_non_super_admin_cannot_access_payment_methods_backend(): void
    {
        $suffix = uniqid();
        $tenant = User::factory()->create([
            'email' => 'tscheck-tenant-'.$suffix.'@example.com',
            'role' => 'tenant',
        ]);

        $response = $this->actingAs($tenant)->get(route('backend.payment-methods.index'));

        $this->assertContains($response->status(), [403, 302]);
    }
}
