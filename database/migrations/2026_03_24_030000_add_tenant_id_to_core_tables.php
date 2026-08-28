<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index('tenant_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index('tenant_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index('tenant_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index('tenant_id');
        });

        $defaultTenantId = DB::table('users')->min('id');

        if ($defaultTenantId !== null) {
            DB::table('services')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
            DB::table('customers')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
            DB::table('bookings')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
            DB::table('payments')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
