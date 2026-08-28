<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\TenantPaymentAccount;
use App\Models\User;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Http\Response;
use Tests\TestCase;

class TscheckInvoiceActivePaymentMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_lists_only_active_methods_with_primary_first(): void
    {
        [$tenant, $booking] = $this->createTenantWithBooking('tscheck-invoice-active-'.uniqid().'@example.com');

        $secondary = $tenant->paymentAccounts()->create([
            'type' => TenantPaymentAccount::TYPE_EWALLET,
            'bank_name' => 'tscheck-secondary-gopay',
            'account_name' => 'Studio',
            'account_number' => '0899999999',
            'is_active' => true,
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        $primary = $tenant->paymentAccounts()->create([
            'type' => TenantPaymentAccount::TYPE_BANK,
            'bank_name' => 'tscheck-primary-bca',
            'account_name' => 'Studio',
            'account_number' => '0800000000',
            'is_active' => true,
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $inactive = $tenant->paymentAccounts()->create([
            'type' => TenantPaymentAccount::TYPE_BANK,
            'bank_name' => 'tscheck-inactive-mandiri',
            'account_name' => 'Studio',
            'account_number' => '0811111111',
            'is_active' => false,
            'is_primary' => false,
            'sort_order' => 2,
        ]);

        $capturedAccounts = null;

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data) use (&$capturedAccounts): bool {
                $capturedAccounts = $data['tenantPaymentAccounts'] ?? null;

                return $view === 'admin.invoices.booking';
            })
            ->andReturn($this->fakePdf());

        (new InvoiceService())->previewBookingInvoice($booking);

        $this->assertInstanceOf(Collection::class, $capturedAccounts);
        $this->assertCount(2, $capturedAccounts, 'inactive method must be excluded');
        $this->assertFalse($capturedAccounts->contains('id', $inactive->id));

        $ids = $capturedAccounts->pluck('id')->values()->all();
        $this->assertSame($primary->id, $ids[0], 'primary active method must be listed first');
        $this->assertSame($secondary->id, $ids[1]);
    }

    /**
     * @return array{User, Booking}
     */
    private function createTenantWithBooking(string $email): array
    {
        $tenant = User::factory()->create([
            'email' => $email,
            'onboarding_completed_at' => now(),
        ]);

        Subscription::query()->create([
            'user_id' => $tenant->id,
            'plan' => Subscription::PLAN_FREE,
            'bookings_consumed_total' => 0,
            'expired_at' => null,
        ]);

        $service = Service::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'tscheck-invoice-service',
            'price' => 300000,
            'duration' => 60,
            'description' => 'Test',
        ]);

        $customer = Customer::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'tscheck-invoice-customer',
            'phone' => '08123456780',
            'email' => 'tscheck-invoice-customer@example.com',
        ]);

        $booking = Booking::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'total_people' => 1,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => Booking::STATUS_CONFIRMED,
            'location' => 'Lokasi Test',
        ]);

        return [$tenant, $booking];
    }

    private function fakePdf(): DomPdfWrapper
    {
        $mock = \Mockery::mock(DomPdfWrapper::class);
        $mock->shouldReceive('stream')->andReturn(new Response('%PDF-fake', 200, ['Content-Type' => 'application/pdf']));
        $mock->shouldReceive('download')->andReturn(new Response('%PDF-fake', 200, ['Content-Type' => 'application/pdf']));

        return $mock;
    }
}
