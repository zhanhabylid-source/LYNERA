# LYNERA Launch Checklist

Target produksi: `https://lynera.my.id` pada server yang mendukung PHP 8.2+, Composer, Node.js build tooling, MySQL/MariaDB, Nginx, dan PHP-FPM.

## Environment

- Salin `deploy/.env.production.example` menjadi `.env` di server dan isi semua secret melalui environment server.
- Jalankan `php artisan key:generate` hanya jika belum memiliki `APP_KEY` produksi.
- Pastikan `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL=https://lynera.my.id`.
- Isi kredensial MySQL/MariaDB produksi dan `RESEND_API_KEY`; jangan commit `.env`.

## Build dan database

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Jalankan `AdminUserSeeder` untuk akun Super Admin. `DemoDataSeeder` hanya berjalan otomatis pada environment `local`/`testing` dan tidak boleh dijalankan pada produksi.

## Runtime

- Arahkan Nginx document root ke `/public` dan gunakan PHP-FPM sesuai `DEPLOYMENT.md`.
- Jalankan `php artisan schedule:run` setiap menit agar reminder H-1 dan auto-settle berjalan.
- Pastikan `storage` dan `bootstrap/cache` dapat ditulis oleh user web server.
- Tambahkan minimal satu metode pembayaran upgrade aktif melalui **Backend → Pembayaran** sebelum menerima upgrade paket.
- Atur promo launching melalui **Backend → Paket**; pastikan periode, harga promo, dan zona waktu server sudah benar sebelum mengaktifkannya.

## Verifikasi setelah deploy

- `GET https://lynera.my.id/api/v1/health` harus mengembalikan `{"status":"ok"}`.
- Login Super Admin, buka **Pembayaran**, dan uji tambah/edit/utama/nonaktif/hapus metode.
- Login tenant, buat request upgrade, lalu pastikan bank/e-wallet/QRIS aktif tampil.
- Kirim tes booking publik, reminder H-1, verifikasi email, dan reset password; seluruhnya harus terlihat pada delivery log Resend.
- Pastikan logo dan bukti pembayaran dapat dimuat melalui HTTPS tanpa mixed content.