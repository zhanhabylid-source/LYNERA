<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->unsignedInteger('bookings_consumed_total')
                ->default(0)
                ->after('plan');
        });

        $usageByTenant = DB::table('bookings')
            ->selectRaw('tenant_id, COUNT(*) AS total_bookings')
            ->whereNotNull('tenant_id')
            ->groupBy('tenant_id')
            ->get();

        foreach ($usageByTenant as $row) {
            DB::table('subscriptions')
                ->where('user_id', (int) $row->tenant_id)
                ->update([
                    'bookings_consumed_total' => (int) $row->total_bookings,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropColumn('bookings_consumed_total');
        });
    }
};
