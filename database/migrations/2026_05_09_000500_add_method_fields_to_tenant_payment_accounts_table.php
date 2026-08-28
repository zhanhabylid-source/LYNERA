<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_payment_accounts', function (Blueprint $table): void {
            $table->string('type', 30)->default('bank')->after('tenant_id');
            $table->string('qr_code_path')->nullable()->after('notes');
            $table->boolean('is_active')->default(true)->after('is_primary');
            $table->string('account_name', 120)->nullable()->change();
            $table->string('account_number', 120)->nullable()->change();
            $table->index(['tenant_id', 'is_active', 'is_primary'], 'tenant_payment_accounts_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_payment_accounts', function (Blueprint $table): void {
            $table->dropIndex('tenant_payment_accounts_active_index');
            $table->dropColumn(['type', 'qr_code_path', 'is_active']);
            $table->string('account_name', 120)->nullable(false)->change();
            $table->string('account_number', 80)->nullable(false)->change();
        });
    }
};