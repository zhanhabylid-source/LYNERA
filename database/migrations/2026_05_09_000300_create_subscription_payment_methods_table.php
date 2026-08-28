<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payment_methods', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 30);
            $table->string('provider_name', 120);
            $table->string('account_name', 120)->nullable();
            $table->string('account_number', 120)->nullable();
            $table->string('contact', 120)->nullable();
            $table->text('instructions')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'is_primary', 'sort_order'], 'subscription_payment_methods_display_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payment_methods');
    }
};