<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('payment_bank_name', 120)->nullable()->after('logo_path');
            $table->string('payment_account_name', 120)->nullable()->after('payment_bank_name');
            $table->string('payment_account_number', 80)->nullable()->after('payment_account_name');
            $table->string('payment_contact', 120)->nullable()->after('payment_account_number');
            $table->text('payment_instructions')->nullable()->after('payment_contact');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_bank_name',
                'payment_account_name',
                'payment_account_number',
                'payment_contact',
                'payment_instructions',
            ]);
        });
    }
};

