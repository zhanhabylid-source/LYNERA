<?php

use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete()->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('status', [
                Payment::STATUS_PENDING,
                Payment::STATUS_PAID,
                Payment::STATUS_FAILED,
            ])->default(Payment::STATUS_PENDING);
            $table->string('payment_method', 50)->default(Payment::METHOD_MANUAL);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
