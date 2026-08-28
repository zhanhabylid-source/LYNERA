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
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Tests\TestCase;

class TscheckInvoiceQrisDataUriTest extends TestCase
{
    use RefreshDatabase;

    private ?string $writtenQrPath = null;

    protected function tearDown(): void
    {
        if ($this->writtenQrPath) {
            Storage::disk('public')->delete($this->writtenQrPath);
        }
        parent::tearDown();
    }

    public function test_active_qris_method_is_embedded_as_data_uri_on_invoice(): void
    {
        $suffix = uniqid();
        $tenant = User::factory()->create([
            'email' => "tscheck-invoice-qris-{$suffix}@example.com",
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
            'name' => 'tscheck-invoice-qris-service',
            'price' => 300000,
            'duration' => 60,
            'description' => 'Test',
        ]);

        $customer = Customer::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'tscheck-invoice-qris-customer',
            'phone' => '08123456781',
            'email' => "tscheck-invoice-qris-customer-{$suffix}@example.com",
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

        $qrPath = "tenants/{$tenant->id}/payment-methods/tscheck-qr-{$suffix}.png";
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        Storage::disk('public')->put($qrPath, $pngBytes);
        $this->writtenQrPath = $qrPath;

        $qris = $tenant->paymentAccounts()->create([
            'type' => TenantPaymentAccount::TYPE_QRIS,
            'bank_name' => 'tscheck-qris-studio',
            'account_name' => null,
            'account_number' => null,
            'is_active' => true,
            'is_primary' => true,
            'sort_order' => 0,
            'qr_code_path' => $qrPath,
        ]);

        $capturedQrDataUris = null;

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data) use (&$capturedQrDataUris): bool {
                $capturedQrDataUris = $data['tenantPaymentQrDataUris'] ?? null;

                return $view === 'admin.invoices.booking';
            })
            ->andReturn((function (): DomPdfWrapper {
                $mock = \Mockery::mock(DomPdfWrapper::class);
                $mock->shouldReceive('stream')->andReturn(new Response('%PDF-fake', 200, ['Content-Type' => 'application/pdf']));

                return $mock;
            })());

        (new InvoiceService())->previewBookingInvoice($booking);

        $this->assertIsArray($capturedQrDataUris);
        $this->assertArrayHasKey($qris->id, $capturedQrDataUris);
        $this->assertStringStartsWith('data:image/', $capturedQrDataUris[$qris->id]);
        $this->assertStringContainsString('base64,', $capturedQrDataUris[$qris->id]);
    }
}
