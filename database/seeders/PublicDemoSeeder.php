<?php

namespace Database\Seeders;

use App\Models\BillingLog;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PublicBookingForm;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\TenantPaymentAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $tenant = User::query()->where('id', 4)->firstOrFail();

            $tenant->update([
                'name' => 'LYNERA Demo',
                'email' => 'demo@lynera.my.id',
                'role' => 'tenant',
                'is_suspended' => false,
                'studio_name' => 'LYNERA Demo Studio',
                'studio_location' => 'Medan, Sumatera Utara',
                'studio_maps_link' => 'https://maps.google.com/?q=Medan+Sumatera+Utara',
                'default_transport_fee' => 50000,
                'payment_bank_name' => 'BCA',
                'payment_account_name' => 'LYNERA Demo',
                'payment_account_number' => '8888888888',
                'payment_contact' => '081200000004',
                'payment_instructions' => 'PEMBAYARAN DEMO. Jangan melakukan transfer uang nyata ke akun ini.',
                'notify_tomorrow_booking' => false,
                'booking_terms_title' => 'Syarat & Ketentuan Demo',
                'booking_terms_content' => "1. Semua data pada akun ini adalah data DEMO.\n2. Tidak ada pembayaran nyata.\n3. Booking digunakan hanya untuk mencoba fitur LYNERA.\n4. Data demo dapat di-reset sewaktu-waktu.",
                'booking_terms_updated_at' => now(),
                'onboarding_completed_at' => now(),
            ]);

            /*
             * SUBSCRIPTION
             */
            Subscription::query()->updateOrCreate(
                ['user_id' => $tenant->id],
                [
                    'plan' => defined(Subscription::class . '::PLAN_PREMIUM')
                        ? Subscription::PLAN_PREMIUM
                        : 'premium',
                    'bookings_consumed_total' => 15,
                    'expired_at' => now()->addYear(),
                    'expiry_reminder_sent_at' => null,
                ]
            );

            /*
             * PAYMENT ACCOUNTS
             */
            TenantPaymentAccount::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->delete();

            TenantPaymentAccount::create([
                'tenant_id' => $tenant->id,
                'type' => TenantPaymentAccount::TYPE_QRIS,
                'bank_name' => 'QRIS DEMO',
                'account_name' => 'LYNERA Demo',
                'account_number' => null,
                'contact' => '081200000004',
                'notes' => 'QRIS DEMO. Tidak menerima pembayaran nyata.',
                'qr_code_path' => null,
                'is_primary' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]);

            TenantPaymentAccount::create([
                'tenant_id' => $tenant->id,
                'type' => TenantPaymentAccount::TYPE_BANK,
                'bank_name' => 'BCA DEMO',
                'account_name' => 'LYNERA Demo',
                'account_number' => '8888888888',
                'contact' => '081200000004',
                'notes' => 'REKENING DEMO. Jangan melakukan transfer uang nyata.',
                'qr_code_path' => null,
                'is_primary' => false,
                'is_active' => true,
                'sort_order' => 2,
            ]);

            TenantPaymentAccount::create([
                'tenant_id' => $tenant->id,
                'type' => TenantPaymentAccount::TYPE_EWALLET,
                'bank_name' => 'GoPay DEMO',
                'account_name' => 'LYNERA Demo',
                'account_number' => '081200000004',
                'contact' => '081200000004',
                'notes' => 'E-Wallet DEMO. Tidak menerima pembayaran nyata.',
                'qr_code_path' => null,
                'is_primary' => false,
                'is_active' => true,
                'sort_order' => 3,
            ]);

            /*
             * SERVICES
             */
            $services = [
                ['name' => 'Makeup Wisuda', 'price' => 350000, 'duration' => 90, 'description' => 'Makeup natural untuk acara wisuda.'],
                ['name' => 'Makeup Party', 'price' => 500000, 'duration' => 120, 'description' => 'Makeup glam untuk pesta dan acara formal.'],
                ['name' => 'Makeup Engagement', 'price' => 850000, 'duration' => 150, 'description' => 'Makeup untuk acara lamaran.'],
                ['name' => 'Makeup Bridal', 'price' => 1500000, 'duration' => 180, 'description' => 'Makeup pengantin dengan hasil elegan.'],
                ['name' => 'Hairdo', 'price' => 300000, 'duration' => 60, 'description' => 'Hairdo sederhana sampai formal.'],
                ['name' => 'Makeup + Hairdo Package', 'price' => 1750000, 'duration' => 240, 'description' => 'Paket makeup dan hairdo lengkap.'],
            ];

            $serviceModels = [];

            foreach ($services as $data) {
                $serviceModels[$data['name']] = Service::withoutGlobalScopes()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'name' => $data['name'],
                    ],
                    $data + ['tenant_id' => $tenant->id]
                );
            }

            /*
             * CUSTOMERS
             */
            $customers = [
                ['name' => 'Alya Putri', 'phone' => '081300000401', 'email' => 'alya.demo@example.com', 'instagram' => '@alya_demo'],
                ['name' => 'Nadia Salsabila', 'phone' => '081300000402', 'email' => 'nadia.demo@example.com', 'instagram' => '@nadia_demo'],
                ['name' => 'Citra Maharani', 'phone' => '081300000403', 'email' => 'citra.demo@example.com', 'instagram' => '@citra_demo'],
                ['name' => 'Dinda Permata', 'phone' => '081300000404', 'email' => 'dinda.demo@example.com', 'instagram' => '@dinda_demo'],
                ['name' => 'Fani Amelia', 'phone' => '081300000405', 'email' => 'fani.demo@example.com', 'instagram' => '@fani_demo'],
                ['name' => 'Maya Anggraini', 'phone' => '081300000406', 'email' => 'maya.demo@example.com', 'instagram' => '@maya_demo'],
                ['name' => 'Rani Oktavia', 'phone' => '081300000407', 'email' => 'rani.demo@example.com', 'instagram' => '@rani_demo'],
                ['name' => 'Salsa Aulia', 'phone' => '081300000408', 'email' => 'salsa.demo@example.com', 'instagram' => '@salsa_demo'],
                ['name' => 'Tiara Ananda', 'phone' => '081300000409', 'email' => 'tiara.demo@example.com', 'instagram' => '@tiara_demo'],
                ['name' => 'Vina Lestari', 'phone' => '081300000410', 'email' => 'vina.demo@example.com', 'instagram' => '@vina_demo'],
            ];

            $customerModels = [];

            foreach ($customers as $data) {
                $customerModels[$data['name']] = Customer::withoutGlobalScopes()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'email' => $data['email'],
                    ],
                    $data + ['tenant_id' => $tenant->id]
                );
            }

            /*
             * BOOKINGS
             *
             * Dibuat deterministic supaya seeder aman dijalankan ulang.
             * Tidak ada google_event_id.
             */
            Booking::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->get()
                ->each(function (Booking $booking) {
                    $booking->bookingItems()->withoutGlobalScopes()->delete();
                    $booking->payment()->withoutGlobalScopes()->delete();
                    BillingLog::withoutGlobalScopes()
                        ->where('tenant_id', $booking->tenant_id)
                        ->where('payment_id', $booking->id)
                        ->delete();
                    $booking->delete();
                });

            $bookingDefinitions = [
                ['customer' => 'Alya Putri', 'days' => -18, 'time' => '09:00', 'status' => Booking::STATUS_COMPLETED, 'service' => 'Makeup Wisuda', 'people' => 1, 'payment' => 'paid'],
                ['customer' => 'Nadia Salsabila', 'days' => -12, 'time' => '10:00', 'status' => Booking::STATUS_COMPLETED, 'service' => 'Makeup Party', 'people' => 1, 'payment' => 'paid'],
                ['customer' => 'Citra Maharani', 'days' => -7, 'time' => '13:00', 'status' => Booking::STATUS_COMPLETED, 'service' => 'Makeup Engagement', 'people' => 2, 'payment' => 'paid'],
                ['customer' => 'Dinda Permata', 'days' => -3, 'time' => '09:00', 'status' => Booking::STATUS_COMPLETED, 'service' => 'Makeup Bridal', 'people' => 1, 'payment' => 'paid'],
                ['customer' => 'Fani Amelia', 'days' => 2, 'time' => '10:00', 'status' => Booking::STATUS_CONFIRMED, 'service' => 'Makeup Bridal', 'people' => 1, 'payment' => 'dp'],
                ['customer' => 'Maya Anggraini', 'days' => 4, 'time' => '13:00', 'status' => Booking::STATUS_CONFIRMED, 'service' => 'Makeup + Hairdo Package', 'people' => 1, 'payment' => 'paid'],
                ['customer' => 'Rani Oktavia', 'days' => 6, 'time' => '09:00', 'status' => Booking::STATUS_CONFIRMED, 'service' => 'Makeup Party', 'people' => 2, 'payment' => 'dp'],
                ['customer' => 'Salsa Aulia', 'days' => 8, 'time' => '11:00', 'status' => Booking::STATUS_PENDING, 'service' => 'Makeup Wisuda', 'people' => 1, 'payment' => 'pending'],
                ['customer' => 'Tiara Ananda', 'days' => 12, 'time' => '14:00', 'status' => Booking::STATUS_PENDING, 'service' => 'Hairdo', 'people' => 1, 'payment' => 'pending'],
                ['customer' => 'Vina Lestari', 'days' => 15, 'time' => '09:00', 'status' => Booking::STATUS_CONFIRMED, 'service' => 'Makeup Party', 'people' => 1, 'payment' => 'paid'],
                ['customer' => 'Alya Putri', 'days' => 20, 'time' => '10:00', 'status' => Booking::STATUS_PENDING, 'service' => 'Makeup Engagement', 'people' => 1, 'payment' => 'pending'],
                ['customer' => 'Nadia Salsabila', 'days' => 25, 'time' => '09:00', 'status' => Booking::STATUS_CANCELED, 'service' => 'Makeup Wisuda', 'people' => 1, 'payment' => 'pending'],
            ];

            foreach ($bookingDefinitions as $index => $definition) {
                $service = $serviceModels[$definition['service']];
                $customer = $customerModels[$definition['customer']];

                $start = Carbon::now()
                    ->addDays($definition['days'])
                    ->setTimeFromTimeString($definition['time']);

                $end = $start->copy()->addMinutes((int) $service->duration);

                /*
                 * Contoh multi-service pada booking ke-6.
                 */
                $items = [
                    [
                        'service' => $service,
                        'people' => $definition['people'],
                    ],
                ];

                if ($index === 5) {
                    $items[] = [
                        'service' => $serviceModels['Hairdo'],
                        'people' => 1,
                    ];
                }

                $totalPeople = collect($items)->sum('people');
                $totalDuration = collect($items)->sum(
                    fn ($item) => ((int) $item['service']->duration * (int) $item['people'])
                );

                $end = $start->copy()->addMinutes($totalDuration);

                $subtotal = collect($items)->sum(
                    fn ($item) => (float) $item['service']->price * (int) $item['people']
                );

                $transportFee = 50000;
                $amount = $subtotal + $transportFee;

                $booking = Booking::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'service_id' => $items[0]['service']->id,
                    'total_people' => $totalPeople,
                    'booking_date' => $start->toDateString(),
                    'booking_time' => $start->format('H:i:s'),
                    'end_time' => $end->format('H:i:s'),
                    'google_event_id' => null,
                    'tomorrow_reminder_sent_at' => null,
                    'terms_accepted_at' => now(),
                    'terms_version' => 'demo-2026',
                    'terms_snapshot' => 'Data booking DEMO LYNERA. Tidak berlaku sebagai transaksi nyata.',
                    'terms_acceptance_ip' => '127.0.0.1',
                    'terms_acceptance_user_agent' => 'LYNERA Demo Seeder',
                    'location' => 'Lokasi Demo LYNERA',
                    'transport_fee' => $transportFee,
                    'status' => $definition['status'],
                    'notes' => 'BOOKING DEMO — hanya untuk testing fitur LYNERA.',
                ]);

                foreach ($items as $item) {
                    $itemSubtotal = (float) $item['service']->price * (int) $item['people'];

                    BookingItem::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'booking_id' => $booking->id,
                        'service_id' => $item['service']->id,
                        'people_count' => $item['people'],
                        'unit_price' => $item['service']->price,
                        'duration_minutes' => $item['service']->duration,
                        'subtotal' => $itemSubtotal,
                    ]);
                }

                $dpAmount = round($amount * 0.30, 2);

                if ($definition['payment'] === 'paid') {
                    $paymentData = [
                        'amount' => $amount,
                        'discount_amount' => 0,
                        'dp_amount' => $dpAmount,
                        'paid_amount' => $amount,
                        'status' => Payment::STATUS_PAID,
                        'payment_method' => Payment::METHOD_MANUAL,
                        'dp_paid_at' => $start->copy()->subDays(3),
                        'paid_at' => $start->copy()->subDays(1),
                    ];
                } elseif ($definition['payment'] === 'dp') {
                    $paymentData = [
                        'amount' => $amount,
                        'discount_amount' => 0,
                        'dp_amount' => $dpAmount,
                        'paid_amount' => $dpAmount,
                        'status' => Payment::STATUS_PAID,
                        'payment_method' => Payment::METHOD_MANUAL,
                        'dp_paid_at' => now()->subDay(),
                        'paid_at' => null,
                    ];
                } else {
                    $paymentData = [
                        'amount' => $amount,
                        'discount_amount' => 0,
                        'dp_amount' => $dpAmount,
                        'paid_amount' => 0,
                        'status' => Payment::STATUS_PENDING,
                        'payment_method' => Payment::METHOD_MANUAL,
                        'dp_paid_at' => null,
                        'paid_at' => null,
                    ];
                }

                $payment = Payment::withoutGlobalScopes()->create(
                    $paymentData + [
                        'tenant_id' => $tenant->id,
                        'booking_id' => $booking->id,
                    ]
                );

                BillingLog::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'payment_id' => $payment->id,
                    'event_type' => match ($definition['payment']) {
                        'paid' => 'payment_paid',
                        'dp' => 'payment_dp_paid',
                        default => 'payment_pending',
                    },
                    'amount' => $payment->amount,
                    'metadata' => [
                        'booking_id' => $booking->id,
                        'status' => $payment->status,
                        'payment_method' => $payment->payment_method,
                        'demo' => true,
                    ],
                ]);
            }

            /*
             * PUBLIC BOOKING
             */
            PublicBookingForm::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->delete();

            PublicBookingForm::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'token' => Str::random(48),
                'expires_at' => now()->addYear(),
                'is_active' => true,
                'settings' => [
                    'service_ids' => collect($serviceModels)->pluck('id')->values()->all(),
                    'transport_fee' => 50000,
                    'terms' => [
                        'title' => 'Syarat & Ketentuan Booking DEMO',
                        'content' => "Ini adalah formulir booking DEMO LYNERA.\n\nTidak ada transaksi nyata. Data yang dimasukkan hanya untuk pengujian.",
                    ],
                ],
                'max_submissions' => 100,
                'submission_count' => 0,
            ]);

            /*
             * Billing subscription
             */
            BillingLog::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'payment_id' => null,
                'event_type' => 'subscription_upgrade',
                'amount' => null,
                'metadata' => [
                    'plan' => defined(Subscription::class . '::PLAN_PREMIUM')
                        ? Subscription::PLAN_PREMIUM
                        : 'premium',
                    'expired_at' => now()->addYear()->toIso8601String(),
                    'demo' => true,
                ],
            ]);

            $this->command?->info('LYNERA Demo berhasil diisi lengkap untuk tenant ID 4.');
            $this->command?->info('Email    : demo@lynera.my.id');
            $this->command?->info('Password : LyneraDemo2026!');
        });
    }
}
