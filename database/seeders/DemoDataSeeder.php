<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PlanOverride;
use App\Models\PublicBookingForm;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\SubscriptionPaymentMethod;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\TenantPaymentAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed 3 demo MUA tenants with services, customers, bookings and payments.
     */
    public function run(): void
    {
        $this->ensureProofFile();
        $this->ensureTenantPaymentQrisFile();
        $this->seedSubscriptionPaymentMethods();
        $this->seedLaunchPromotions();

        $tenants = [
            [
                'name' => 'Rina Makeup Studio',
                'email' => 'rina@example.com',
                'plan' => Subscription::PLAN_PRO,
                'studio_name' => 'Rina Beauty Studio',
                'studio_location' => 'Jl. Melati No. 12, Jakarta Selatan',
                'transport_fee' => 50000,
                'payment_account_number' => '12345678901',
                'payment_contact' => '081234567891',
                'with_upgrade_request' => false,
            ],
            [
                'name' => 'Dewi Glam Artist',
                'email' => 'dewi@example.com',
                'plan' => Subscription::PLAN_FREE,
                'studio_name' => 'Dewi Glam House',
                'studio_location' => 'Jl. Kenanga No. 5, Bandung',
                'transport_fee' => 40000,
                'payment_account_number' => '12345678902',
                'payment_contact' => '081234567892',
                'with_upgrade_request' => true,
            ],
            [
                'name' => 'Sari Bridal MUA',
                'email' => 'sari@example.com',
                'plan' => Subscription::PLAN_PREMIUM,
                'studio_name' => 'Sari Bridal Atelier',
                'studio_location' => 'Jl. Anggrek No. 8, Surabaya',
                'transport_fee' => 75000,
                'payment_account_number' => '12345678903',
                'payment_contact' => '081234567893',
                'with_upgrade_request' => false,
            ],
        ];

        foreach ($tenants as $data) {
            $this->seedTenant($data);
        }
    }

    private function seedTenant(array $data): void
    {
        $tenant = User::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => 'Password123!',
                'role' => 'tenant',
                'email_verified_at' => now(),
                'studio_name' => $data['studio_name'],
                'studio_location' => $data['studio_location'],
                'studio_maps_link' => 'https://maps.google.com/?q='.urlencode($data['studio_location']),
                'default_transport_fee' => $data['transport_fee'],
                'payment_bank_name' => 'BCA',
                'payment_account_name' => $data['name'],
                'payment_account_number' => $data['payment_account_number'],
                'payment_contact' => $data['payment_contact'],
                'payment_instructions' => 'Transfer DP minimal 30% lalu kirim bukti via WhatsApp.',
                'notify_tomorrow_booking' => true,
                'onboarding_completed_at' => now(),
            ]
        );

        $tenantId = (int) $tenant->id;

        // Subscription
        Subscription::query()->updateOrCreate(
            ['user_id' => $tenantId],
            [
                'plan' => $data['plan'],
                'expired_at' => $data['plan'] === Subscription::PLAN_FREE ? null : now()->addMonth(),
            ]
        );

        // Payment account
        TenantPaymentAccount::query()->updateOrCreate(
            ['tenant_id' => $tenantId, 'account_number' => $data['payment_account_number']],
            [
                'type' => TenantPaymentAccount::TYPE_BANK,
                'bank_name' => 'BCA',
                'account_name' => $data['name'],
                'contact' => $tenant->payment_contact,
                'notes' => 'Rekening utama',
                'qr_code_path' => null,
                'is_primary' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        if ($data['email'] === 'rina@example.com') {
            TenantPaymentAccount::query()->updateOrCreate(
                ['tenant_id' => $tenantId, 'type' => TenantPaymentAccount::TYPE_EWALLET, 'bank_name' => 'GoPay Demo'],
                [
                    'account_name' => $data['name'],
                    'account_number' => '081234567891',
                    'contact' => $tenant->payment_contact,
                    'notes' => 'Opsi pembayaran e-wallet demo',
                    'qr_code_path' => null,
                    'is_primary' => false,
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );
            TenantPaymentAccount::query()->updateOrCreate(
                ['tenant_id' => $tenantId, 'type' => TenantPaymentAccount::TYPE_QRIS, 'bank_name' => 'QRIS Studio Demo'],
                [
                    'account_name' => $data['name'],
                    'account_number' => null,
                    'contact' => $tenant->payment_contact,
                    'notes' => 'QRIS demo untuk preview invoice',
                    'qr_code_path' => 'tenant-payment-methods/demo-qris.svg',
                    'is_primary' => false,
                    'is_active' => true,
                    'sort_order' => 3,
                ]
            );
        }

        // Skip child data if bookings already seeded for this tenant.
        $alreadySeeded = Booking::withoutGlobalScopes()->where('tenant_id', $tenantId)->exists();

        if (! $alreadySeeded) {
            $services = $this->seedServices($tenantId);
            $customers = $this->seedCustomers($tenantId);
            $this->seedBookings($tenantId, $services, $customers);
            $this->seedPublicForm($tenantId, $services);
        }

        if ($data['with_upgrade_request']) {
            $this->seedUpgradeRequest($tenantId, $data['plan']);
        }
    }

    /** @return array<int, Service> */
    private function seedServices(int $tenantId): array
    {
        $defs = [
            ['name' => 'Makeup Wisuda', 'price' => 350000, 'duration' => 90, 'description' => 'Makeup natural untuk wisuda.'],
            ['name' => 'Makeup Bridal', 'price' => 1500000, 'duration' => 180, 'description' => 'Paket makeup pengantin lengkap.'],
            ['name' => 'Makeup Party', 'price' => 500000, 'duration' => 120, 'description' => 'Makeup glamour untuk pesta.'],
        ];

        $services = [];
        foreach ($defs as $def) {
            $services[] = Service::withoutGlobalScopes()->create(array_merge($def, ['tenant_id' => $tenantId]));
        }

        return $services;
    }

    /** @return array<int, Customer> */
    private function seedCustomers(int $tenantId): array
    {
        $defs = [
            ['name' => 'Ayu Lestari', 'phone' => '081234567001', 'email' => 'ayu@example.com', 'instagram' => '@ayulestari'],
            ['name' => 'Bella Kusuma', 'phone' => '081234567002', 'email' => 'bella@example.com', 'instagram' => '@bellak'],
            ['name' => 'Citra Dewanti', 'phone' => '081234567003', 'email' => 'citra@example.com', 'instagram' => '@citrad'],
        ];

        $customers = [];
        foreach ($defs as $def) {
            $customers[] = Customer::withoutGlobalScopes()->create(array_merge($def, ['tenant_id' => $tenantId]));
        }

        return $customers;
    }

    /**
     * @param array<int, Service> $services
     * @param array<int, Customer> $customers
     */
    private function seedBookings(int $tenantId, array $services, array $customers): void
    {
        $plan = [
            // [dayOffset, serviceIdx, customerIdx, status, paymentStatus]
            [-20, 1, 0, Booking::STATUS_COMPLETED, 'settled'],
            [-7, 0, 1, Booking::STATUS_COMPLETED, 'settled'],
            [-2, 2, 2, Booking::STATUS_CONFIRMED, 'dp_paid'],
            [3, 0, 0, Booking::STATUS_CONFIRMED, 'dp_paid'],
            [7, 1, 1, Booking::STATUS_PENDING, 'pending'],
            [14, 2, 2, Booking::STATUS_PENDING, 'pending'],
        ];

        foreach ($plan as [$offset, $sIdx, $cIdx, $status, $payState]) {
            $service = $services[$sIdx];
            $customer = $customers[$cIdx];
            $date = Carbon::now()->addDays($offset)->format('Y-m-d');
            $people = random_int(1, 2);
            $start = sprintf('%02d:00:00', random_int(8, 15));
            $end = Carbon::parse($start)->addMinutes(((int) $service->duration) * $people)->format('H:i:s');

            $booking = Booking::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'total_people' => $people,
                'booking_date' => $date,
                'booking_time' => $start,
                'end_time' => $end,
                'location' => 'Jl. Contoh No. '.random_int(1, 99),
                'transport_fee' => 50000,
                'status' => $status,
                'notes' => 'Booking demo untuk pengujian.',
            ]);

            BookingItem::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'booking_id' => $booking->id,
                'service_id' => $service->id,
                'people_count' => $people,
                'unit_price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration * $people,
                'subtotal' => (float) $service->price * $people,
            ]);

            $amount = ((float) $service->price * $people) + 50000;
            $dp = round($amount * 0.30, 2);

            $paymentAttrs = [
                'tenant_id' => $tenantId,
                'booking_id' => $booking->id,
                'amount' => $amount,
                'discount_amount' => 0,
                'dp_amount' => $dp,
                'payment_method' => Payment::METHOD_MANUAL,
            ];

            if ($payState === 'settled') {
                $paymentAttrs += [
                    'paid_amount' => $amount,
                    'status' => Payment::STATUS_PAID,
                    'dp_paid_at' => Carbon::parse($date)->subDays(5),
                    'paid_at' => Carbon::parse($date),
                ];
            } elseif ($payState === 'dp_paid') {
                $paymentAttrs += [
                    'paid_amount' => $dp,
                    'status' => Payment::STATUS_PENDING,
                    'dp_paid_at' => now()->subDays(1),
                    'paid_at' => null,
                ];
            } else {
                $paymentAttrs += [
                    'paid_amount' => 0,
                    'status' => Payment::STATUS_PENDING,
                    'dp_paid_at' => null,
                    'paid_at' => null,
                ];
            }

            Payment::withoutGlobalScopes()->create($paymentAttrs);
        }

        // Keep subscription usage counter in sync.
        Subscription::query()->where('user_id', $tenantId)->update([
            'bookings_consumed_total' => count($plan),
        ]);
    }

    /** @param array<int, Service> $services */
    private function seedPublicForm(int $tenantId, array $services): void
    {
        PublicBookingForm::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'token' => Str::random(48),
                'expires_at' => now()->addDays(30),
                'is_active' => true,
                'settings' => [
                    'service_ids' => array_map(fn (Service $s) => $s->id, $services),
                    'transport_fee' => 50000,
                    'terms' => [
                        'title' => 'Syarat & Ketentuan Booking',
                        'content' => 'DP minimal 30%, pelunasan di hari acara. Reschedule maksimal H-3.',
                    ],
                ],
                'max_submissions' => null,
                'submission_count' => 0,
            ]
        );
    }

    private function seedUpgradeRequest(int $tenantId, string $currentPlan): void
    {
        $existing = SubscriptionUpgradeRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('requested_plan', Subscription::PLAN_PRO)
            ->first();

        if ($existing) {
            if ($existing->proof_path === 'upgrade-proofs/demo-proof.png') {
                $existing->update(['proof_path' => 'upgrade-proofs/demo-proof.svg']);
            }

            return;
        }

        SubscriptionUpgradeRequest::query()->create([
            'tenant_id' => $tenantId,
            'current_plan' => $currentPlan,
            'requested_plan' => Subscription::PLAN_PRO,
            'requested_price' => 'Rp 199.000',
            'status' => SubscriptionUpgradeRequest::STATUS_PENDING_VERIFICATION,
            'payment_method' => 'Transfer BCA',
            'payer_name' => 'Dewi Glam Artist',
            'payer_account_number' => '1234567890',
            'payment_note' => 'Sudah transfer, mohon diverifikasi.',
            'proof_path' => 'upgrade-proofs/demo-proof.svg',
        ]);
    }

    private function ensureProofFile(): void
    {
        if (Storage::disk('public')->exists('upgrade-proofs/demo-proof.svg')) {
            return;
        }

        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="900" height="560" viewBox="0 0 900 560">
  <rect width="900" height="560" rx="32" fill="#fff8f5"/>
  <rect x="40" y="40" width="820" height="480" rx="24" fill="#ffffff" stroke="#f2c6cf" stroke-width="4"/>
  <text x="80" y="120" fill="#8f2d43" font-family="Arial, sans-serif" font-size="32" font-weight="700">BUKTI TRANSFER</text>
  <text x="80" y="165" fill="#6b5560" font-family="Arial, sans-serif" font-size="21">LYNERA Demo Upgrade Request</text>
  <line x1="80" y1="205" x2="820" y2="205" stroke="#f2c6cf" stroke-width="3"/>
  <text x="80" y="260" fill="#6b5560" font-family="Arial, sans-serif" font-size="22">Pengirim</text>
  <text x="820" y="260" text-anchor="end" fill="#33252b" font-family="Arial, sans-serif" font-size="22" font-weight="700">Dewi Glam Artist</text>
  <text x="80" y="315" fill="#6b5560" font-family="Arial, sans-serif" font-size="22">Tujuan</text>
  <text x="820" y="315" text-anchor="end" fill="#33252b" font-family="Arial, sans-serif" font-size="22" font-weight="700">LYNERA Subscription</text>
  <text x="80" y="370" fill="#6b5560" font-family="Arial, sans-serif" font-size="22">Nominal</text>
  <text x="820" y="370" text-anchor="end" fill="#8f2d43" font-family="Arial, sans-serif" font-size="28" font-weight="700">Rp 199.000</text>
  <rect x="80" y="420" width="260" height="52" rx="26" fill="#ffe4e8"/>
  <text x="210" y="454" text-anchor="middle" fill="#8f2d43" font-family="Arial, sans-serif" font-size="20" font-weight="700">TERVERIFIKASI DEMO</text>
</svg>
SVG;
        Storage::disk('public')->put('upgrade-proofs/demo-proof.svg', $svg);
    }

    private function ensureTenantPaymentQrisFile(): void
    {
        $source = 'subscription-payment-methods/demo-qris.svg';
        $target = 'tenant-payment-methods/demo-qris.svg';

        $this->ensureDemoQrisFile();
        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, Storage::disk('public')->get($source));
        }
    }

    private function seedSubscriptionPaymentMethods(): void
    {
        $this->ensureDemoQrisFile();

        $methods = [
            [
                'type' => SubscriptionPaymentMethod::TYPE_BANK,
                'provider_name' => 'BCA Demo',
                'account_name' => 'LYNERA Demo',
                'account_number' => '8888888888',
                'contact' => 'support@lynera.my.id',
                'instructions' => 'Rekening demo untuk pengujian preview. Ganti dengan rekening resmi sebelum menerima pembayaran produksi.',
                'qr_code_path' => null,
                'is_active' => true,
                'is_primary' => true,
                'sort_order' => 1,
            ],
            [
                'type' => SubscriptionPaymentMethod::TYPE_EWALLET,
                'provider_name' => 'GoPay Demo',
                'account_name' => 'LYNERA Demo',
                'account_number' => '081234567890',
                'contact' => 'support@lynera.my.id',
                'instructions' => 'Pastikan nama penerima LYNERA Demo sebelum mengirim pembayaran uji.',
                'qr_code_path' => null,
                'is_active' => true,
                'is_primary' => false,
                'sort_order' => 2,
            ],
            [
                'type' => SubscriptionPaymentMethod::TYPE_QRIS,
                'provider_name' => 'QRIS Demo',
                'account_name' => 'LYNERA Demo',
                'account_number' => null,
                'contact' => 'support@lynera.my.id',
                'instructions' => 'QRIS ini hanya untuk demonstrasi tampilan dan tidak menerima pembayaran nyata.',
                'qr_code_path' => 'subscription-payment-methods/demo-qris.svg',
                'is_active' => true,
                'is_primary' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($methods as $method) {
            SubscriptionPaymentMethod::query()->updateOrCreate(
                ['type' => $method['type'], 'provider_name' => $method['provider_name']],
                $method
            );
        }
    }

    private function ensureDemoQrisFile(): void
    {
        $path = 'subscription-payment-methods/demo-qris.svg';
        if (Storage::disk('public')->exists($path)) {
            return;
        }

        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
  <rect width="600" height="600" rx="36" fill="#ffffff"/>
  <rect x="28" y="28" width="544" height="544" rx="28" fill="#fff7ed" stroke="#e11d48" stroke-width="8"/>
  <g fill="#1c1917">
    <rect x="80" y="80" width="130" height="130" rx="10"/><rect x="105" y="105" width="80" height="80" rx="8" fill="#fff7ed"/><rect x="125" y="125" width="40" height="40" rx="4"/>
    <rect x="390" y="80" width="130" height="130" rx="10"/><rect x="415" y="105" width="80" height="80" rx="8" fill="#fff7ed"/><rect x="435" y="125" width="40" height="40" rx="4"/>
    <rect x="80" y="390" width="130" height="130" rx="10"/><rect x="105" y="415" width="80" height="80" rx="8" fill="#fff7ed"/><rect x="125" y="435" width="40" height="40" rx="4"/>
    <rect x="250" y="90" width="42" height="42"/><rect x="310" y="90" width="42" height="100"/><rect x="250" y="160" width="42" height="42"/>
    <rect x="245" y="245" width="55" height="55"/><rect x="325" y="235" width="55" height="125"/><rect x="405" y="250" width="45" height="45"/><rect x="475" y="250" width="45" height="105"/>
    <rect x="235" y="335" width="55" height="55"/><rect x="255" y="425" width="45" height="95"/><rect x="330" y="400" width="55" height="55"/><rect x="410" y="390" width="45" height="130"/><rect x="480" y="420" width="40" height="40"/>
  </g>
  <rect x="190" y="270" width="220" height="62" rx="31" fill="#e11d48"/>
  <text x="300" y="310" text-anchor="middle" fill="#ffffff" font-family="Arial, sans-serif" font-size="28" font-weight="700">QRIS DEMO</text>
</svg>
SVG;
        Storage::disk('public')->put($path, $svg);
    }

    private function seedLaunchPromotions(): void
    {
        $period = [
            'promo_label' => 'Promo Launching',
            'promo_is_active' => true,
            'promo_starts_at' => now()->subMinute(),
            'promo_ends_at' => now()->addMonths(3),
        ];

        PlanOverride::query()->updateOrCreate(
            ['plan_key' => Subscription::PLAN_PRO],
            [...$period, 'promo_price' => 'Rp 99.000']
        );

        PlanOverride::query()->updateOrCreate(
            ['plan_key' => Subscription::PLAN_PREMIUM],
            [...$period, 'promo_price' => 'Rp 199.000']
        );
    }
}
