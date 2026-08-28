<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('plan_activation_notice_until')->nullable()->after('booking_terms_updated_at');
            $table->string('plan_activation_notice_plan', 30)->nullable()->after('plan_activation_notice_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'plan_activation_notice_until',
                'plan_activation_notice_plan',
            ]);
        });
    }
};
