<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('total_people')->default(1)->after('service_id');
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('people_count')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'booking_id']);
            $table->index(['booking_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('total_people');
        });
    }
};
