<?php

use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')
            ->leftJoin('payments', 'payments.booking_id', '=', 'bookings.id')
            ->join('services', 'services.id', '=', 'bookings.service_id')
            ->whereNull('payments.id')
            ->select([
                'bookings.id as booking_id',
                'services.price as amount',
            ])
            ->orderBy('bookings.id')
            ->chunk(200, function ($rows) {
                $payload = [];

                foreach ($rows as $row) {
                    $payload[] = [
                        'booking_id' => $row->booking_id,
                        'amount' => $row->amount,
                        'status' => Payment::STATUS_PENDING,
                        'payment_method' => Payment::METHOD_MANUAL,
                        'paid_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($payload !== []) {
                    DB::table('payments')->insert($payload);
                }
            });
    }

    public function down(): void
    {
        // no-op
    }
};
