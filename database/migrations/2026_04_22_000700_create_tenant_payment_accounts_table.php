<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payment_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('bank_name', 120);
            $table->string('account_name', 120);
            $table->string('account_number', 80);
            $table->string('contact', 120)->nullable();
            $table->string('notes', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'is_primary']);
            $table->index(['tenant_id', 'sort_order']);
        });

        DB::table('users')
            ->select(['id', 'payment_bank_name', 'payment_account_name', 'payment_account_number', 'payment_contact'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $bankName = trim((string) ($user->payment_bank_name ?? ''));
                    $accountName = trim((string) ($user->payment_account_name ?? ''));
                    $accountNumber = trim((string) ($user->payment_account_number ?? ''));

                    if ($bankName === '' || $accountName === '' || $accountNumber === '') {
                        continue;
                    }

                    DB::table('tenant_payment_accounts')->insert([
                        'tenant_id' => (int) $user->id,
                        'bank_name' => $bankName,
                        'account_name' => $accountName,
                        'account_number' => $accountNumber,
                        'contact' => trim((string) ($user->payment_contact ?? '')) ?: null,
                        'notes' => null,
                        'is_primary' => true,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payment_accounts');
    }
};
