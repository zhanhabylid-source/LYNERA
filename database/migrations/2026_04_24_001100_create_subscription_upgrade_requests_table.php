<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_upgrade_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('current_plan', 30);
            $table->string('requested_plan', 30);
            $table->string('requested_price', 120)->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('payment_method', 120);
            $table->string('payer_name', 120);
            $table->string('payer_account_number', 80)->nullable();
            $table->text('payment_note')->nullable();
            $table->string('proof_path', 255);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['requested_plan', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_upgrade_requests');
    }
};
