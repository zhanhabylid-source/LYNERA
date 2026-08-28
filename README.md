<<<<<<< HEAD
# LYNERA (Laravel SaaS)

> Smart Tools for Modern Makeup Artists

LYNERA adalah sistem manajemen bisnis MUA berbasis Laravel untuk mengelola layanan, booking, pelanggan, pembayaran, dan jadwal dalam satu tempat.

## Fitur Utama
- Manajemen layanan (harga, durasi, deskripsi).
- Booking dan kalender (FullCalendar).
- Cek bentrok jadwal otomatis.
- Panel `Jadwal Besok` + quick actions (`Detail`, `Konfirmasi`, `Hubungi WA`).
- Reminder otomatis H-1 (email + WhatsApp placeholder log) untuk booking besok.
- Manajemen pelanggan.
- Pembayaran dan invoice.
- Alur pembayaran 2 tahap: DP wajib + pelunasan fleksibel sebelum tanggal layanan lewat.
- Public booking link/form.
- Onboarding wizard.
- Paket SaaS `Free`, `Pro`, `Premium`.

## Arsitektur Paket Layanan (Single Source of Truth)
Semua aturan paket dipusatkan di:

- [`config/plans.php`](config/plans.php)

Yang diatur di sana:
- Nama paket, harga, siklus billing.
- Batas booking total (`booking_limit_total`).
- Benefit paket.
- Daftar fitur.
- Feature flags.

## Implementasi Paket di Kode
- Normalisasi dan katalog paket: [`app/Services/PlanService.php`](app/Services/PlanService.php)
- Enforcement kuota booking + akses fitur: [`app/Services/SubscriptionService.php`](app/Services/SubscriptionService.php)
- Paket Free aktif tanpa batas waktu, dengan kuota maksimal 10 booking total.
- Register plan-aware: [`app/Http/Controllers/Auth/RegisteredUserController.php`](app/Http/Controllers/Auth/RegisteredUserController.php)
- Billing, onboarding, dashboard membaca data paket dari source yang sama.

## Notifikasi Booking Besok
- User bisa mengaktifkan/menonaktifkan notifikasi dari halaman profil (`Notifikasi booking besok`).
- Jika aktif, ikon kalender di navbar menampilkan badge jumlah booking besok.
- Scheduler menjalankan command reminder H-1 setiap hari pukul `18:00`:
  - `php artisan bookings:send-tomorrow-reminders`
- Scheduler juga menjalankan auto-settle pembayaran setiap `15` menit:
  - `php artisan payments:auto-settle-past-service`
- Untuk cron server Laravel:
  - `* * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1`

## Aturan Pembayaran Booking
- Tombol aksi pada halaman booking menggunakan label `Pembayaran`.
- Setiap booking wajib DP minimum (default 30%, dapat diatur via `PAYMENT_DP_MIN_PERCENT`).
- Nominal DP dapat diinput manual per booking pada halaman pembayaran (sesuai kesepakatan customer).
- Booking tidak bisa dikonfirmasi jika DP belum dibayar.
- Pelunasan manual dapat dilakukan kapan saja selama tanggal layanan belum lewat.
- Jika tanggal layanan sudah lewat, pembayaran booking (selain booking `canceled`) otomatis ditandai `Lunas`.
- Aksi `Batal` pada halaman pembayaran akan membatalkan booking (status booking `canceled`).
- Jika booking dibatalkan oleh customer, DP yang sudah dibayar tetap tercatat sebagai pendapatan (tanpa refund).
- Tombol `Bayar` pada detail booking dihapus, hanya menggunakan CTA `Pembayaran`.
- Booking berstatus `canceled` tidak dapat membuka halaman pembayaran booking terkait.

## UX Anti-Kebingungan Paket
- Status paket + aturan paket tampil di onboarding, dashboard, dan billing.
- Pemakaian kuota booking total ditampilkan jelas.
- Saat kuota Free habis, user dapat pesan error yang jelas + arahan upgrade.
- Kuota booking dihitung sebagai kuota terpakai kumulatif (`bookings_consumed_total`) dan tidak berkurang saat booking dihapus.

## Aturan Siklus Booking
- Booking yang tanggal/jam layanannya sudah berlalu bersifat read-only:
  - Tidak bisa diubah.
  - Tidak bisa dihapus.
  - Tetap bisa dilihat detailnya.

## Instalasi
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

npm install
npm run dev

php artisan serve
```

## Testing
```bash
php artisan test
```

Test matriks paket mencakup:
- Free dibatasi kuota booking total.
- Pro dapat booking di atas kuota Free.

## Catatan Konfigurasi
- Tagline: `APP_TAGLINE="Smart Tools for Modern Makeup Artists"`
- Locale default: Indonesia (`id`).

## License
MIT
=======
Google PHP API Client Services
==============================

[Reference Documentation](https://googleapis.github.io/google-api-php-client-services)

**NOTE**: please check to see if the package you'd like to install is available in our
list of [Google cloud packages](https://cloud.google.com/php/docs/reference) first, as
these are the recommended libraries.

## Requirements

[Google API PHP Client](https://github.com/googleapis/google-api-php-client/releases)

## Usage

This library is automatically updated daily with new API changes, and tagged weekly.
It is installed as part of the
[Google API PHP Client](https://github.com/googleapis/google-api-php-client/releases)
library via Composer, which will pull down the most recent tag.
>>>>>>> 92e4b34af565e0598fa90036909c400e86aff91c
