<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->decimal('dp_amount', 12, 2)->default(0)->after('amount');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('dp_amount');
            $table->timestamp('dp_paid_at')->nullable()->after('payment_method');
        });

        $percent = (float) config('payment.dp_min_percent', 30);
        $ratio = max(0, min(100, $percent)) / 100;

        DB::table('payments')->orderBy('id')->chunkById(100, function ($rows) use ($ratio): void {
            foreach ($rows as $row) {
                $amount = (float) $row->amount;
                $dpAmount = round($amount * $ratio, 2);
                $isPaid = (string) $row->status === 'paid';
                DB::table('payments')
                    ->where('id', $row->id)
                    ->update([
                        'dp_amount' => $dpAmount,
                        'paid_amount' => $isPaid ? $amount : 0,
                        'dp_paid_at' => $isPaid ? $row->paid_at : null,
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn(['dp_paid_at', 'paid_amount', 'dp_amount']);
        });
    }
};

