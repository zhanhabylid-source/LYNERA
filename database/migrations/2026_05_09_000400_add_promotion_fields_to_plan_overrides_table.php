<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_overrides', function (Blueprint $table): void {
            $table->string('promo_price', 120)->nullable()->after('price');
            $table->string('promo_label', 120)->nullable()->after('promo_price');
            $table->boolean('promo_is_active')->default(false)->after('promo_label');
            $table->timestamp('promo_starts_at')->nullable()->after('promo_is_active');
            $table->timestamp('promo_ends_at')->nullable()->after('promo_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('plan_overrides', function (Blueprint $table): void {
            $table->dropColumn([
                'promo_price',
                'promo_label',
                'promo_is_active',
                'promo_starts_at',
                'promo_ends_at',
            ]);
        });
    }
};