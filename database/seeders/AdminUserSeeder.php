<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the super admin user.
     *
     * Credentials are ALWAYS read from env so the plaintext password is never
     * committed to the repository. In production ALWAYS set these two vars:
     *
     *   SEED_ADMIN_EMAIL=you@example.com
     *   SEED_ADMIN_PASSWORD=SomethingStrong123!
     *
     * When SEED_ADMIN_PASSWORD is missing (fresh clone, local dev), we generate
     * a random password and print it to the console + log so you can copy it
     * once. Rotate later via `php artisan lynera:reset-admin-password`.
     */
    public function run(): void
    {
        $email = config('lynera.admin.email', 'admin@lynera.local');
        $name = config('lynera.admin.name', 'Super Admin');
        $password = config('lynera.admin.password');
        $generated = false;

        if (! $password) {
            $password = Str::password(16, symbols: false);
            $generated = true;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        if ($generated) {
            $notice = <<<TXT

============================================================
[LYNERA] SEED_ADMIN_PASSWORD tidak diisi di .env.
Password acak dibuat untuk email: {$email}
Password sementara: {$password}

Simpan sekarang. Ubah setelah login via /admin/profile
atau jalankan: php artisan lynera:reset-admin-password
============================================================

TXT;

            $this->command?->warn($notice);
            Log::warning('AdminUserSeeder generated a random password. Set SEED_ADMIN_PASSWORD in .env to control it.', [
                'email' => $email,
            ]);
        } else {
            $this->command?->info("Super admin siap login: {$email}");
        }
    }
}
