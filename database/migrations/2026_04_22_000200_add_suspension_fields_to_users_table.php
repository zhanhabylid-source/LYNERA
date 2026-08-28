<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_suspended')->default(false)->after('role');
            $table->timestamp('suspended_at')->nullable()->after('is_suspended');
            $table->string('suspended_reason', 255)->nullable()->after('suspended_at');
            $table->index(['is_suspended', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['is_suspended', 'role']);
            $table->dropColumn(['is_suspended', 'suspended_at', 'suspended_reason']);
        });
    }
};

