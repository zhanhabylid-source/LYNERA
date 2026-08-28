<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Super Admin Seed Credentials
    |--------------------------------------------------------------------------
    |
    | Nilai ini dipakai oleh AdminUserSeeder dan Artisan command
    | `lynera:reset-admin-password`. Karena dibaca lewat config(), nilai
    | tetap tersedia bahkan setelah `php artisan config:cache`.
    |
    */
    'admin' => [
        'email' => env('SEED_ADMIN_EMAIL', 'admin@lynera.local'),
        'name' => env('SEED_ADMIN_NAME', 'Super Admin'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],
];
