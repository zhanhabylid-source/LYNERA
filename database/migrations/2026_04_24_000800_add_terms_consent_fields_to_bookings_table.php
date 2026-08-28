<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestamp('terms_accepted_at')->nullable()->after('tomorrow_reminder_sent_at');
            $table->string('terms_version', 40)->nullable()->after('terms_accepted_at');
            $table->longText('terms_snapshot')->nullable()->after('terms_version');
            $table->string('terms_acceptance_ip', 45)->nullable()->after('terms_snapshot');
            $table->text('terms_acceptance_user_agent')->nullable()->after('terms_acceptance_ip');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn([
                'terms_accepted_at',
                'terms_version',
                'terms_snapshot',
                'terms_acceptance_ip',
                'terms_acceptance_user_agent',
            ]);
        });
    }
};
