<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('plan_key', 30)->unique();
            $table->string('name')->nullable();
            $table->string('price')->nullable();
            $table->string('billing_cycle')->nullable();
            $table->unsignedInteger('booking_limit_total')->nullable();
            $table->text('benefit')->nullable();
            $table->json('features')->nullable();
            $table->json('feature_flags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_overrides');
    }
};

