<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('bookings')
            ->join('services', 'services.id', '=', 'bookings.service_id')
            ->whereNull('bookings.end_time')
            ->orderBy('bookings.id')
            ->select([
                'bookings.id',
                'bookings.booking_time',
                'services.duration',
            ])
            ->get();

        foreach ($rows as $row) {
            $rawTime = (string) $row->booking_time;
            $bookingTime = preg_match('/^\d{2}:\d{2}$/', $rawTime) === 1
                ? $rawTime.':00'
                : $rawTime;

            try {
                $start = Carbon::createFromFormat('H:i:s', $bookingTime);
            } catch (\Throwable) {
                continue;
            }

            $duration = max(1, (int) $row->duration);
            $endTime = $start->copy()->addMinutes($duration)->format('H:i:s');

            DB::table('bookings')
                ->where('id', $row->id)
                ->update(['end_time' => $endTime]);
        }
    }

    public function down(): void
    {
        // no-op
    }
};
