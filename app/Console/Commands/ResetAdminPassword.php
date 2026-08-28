<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ResetAdminPassword extends Command
{
    protected $signature = 'lynera:reset-admin-password
                            {--email= : Email admin yang mau di-reset (default: SEED_ADMIN_EMAIL di .env)}
                            {--password= : Password baru (default: acak 16 karakter)}';

    protected $description = 'Reset password super admin. Berguna kalau lupa password di server produksi.';

    public function handle(): int
    {
        $email = $this->option('email') ?: config('lynera.admin.email');

        if (! $email) {
            $email = $this->ask('Email admin yang mau di-reset');
        }

        $admin = User::query()
            ->where('email', $email)
            ->where('role', 'super_admin')
            ->first();

        if (! $admin) {
            $this->error("Tidak ada super admin dengan email: {$email}");
            $this->line('Jalankan: php artisan db:seed --class=AdminUserSeeder --force');
            return self::FAILURE;
        }

        $password = $this->option('password') ?: Str::password(16, symbols: false);
        $admin->forceFill(['password' => $password])->save();

        $this->newLine();
        $this->info('==============================================');
        $this->info(' Password super admin berhasil di-reset');
        $this->info('==============================================');
        $this->line(" Email    : {$email}");
        $this->line(" Password : {$password}");
        $this->newLine();
        $this->warn(' Simpan password di tempat aman. Command ini hanya menampilkannya sekali.');

        return self::SUCCESS;
    }
}
