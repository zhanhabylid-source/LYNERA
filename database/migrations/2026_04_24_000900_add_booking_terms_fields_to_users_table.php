<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('booking_terms_title', 120)->nullable()->after('notify_tomorrow_booking');
            $table->text('booking_terms_content')->nullable()->after('booking_terms_title');
            $table->timestamp('booking_terms_updated_at')->nullable()->after('booking_terms_content');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'booking_terms_title',
                'booking_terms_content',
                'booking_terms_updated_at',
            ]);
        });
    }
};
