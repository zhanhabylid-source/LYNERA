<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'booking_terms_version')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('booking_terms_version');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'booking_terms_version')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('booking_terms_version', 40)->nullable()->after('booking_terms_title');
        });
    }
};
