# LYNERA Blueprint (Laravel)
Tagline: Smart Tools for Modern Makeup Artists

## 1. Gambaran Sistem
LYNERA adalah platform manajemen bisnis MUA berbasis web untuk:
- layanan,
- pelanggan,
- booking,
- kalender kerja,
- pembayaran,
- laporan.

## 2. Modul Inti
- Dashboard Admin
- Services
- Customers
- Bookings
- Calendar
- Payments
- Reports
- Onboarding
- Public Booking Form

## 3. Multi-Tenant
Setiap data tenant dipisahkan dengan `tenant_id` + `TenantScope`.

## 4. Arsitektur Paket SaaS
Paket tersedia:
- Free
- Pro
- Premium

Aturan paket dipusatkan di:
- [`config/plans.php`](config/plans.php)

Layanan domain paket:
- [`app/Services/PlanService.php`](app/Services/PlanService.php)
- [`app/Services/SubscriptionService.php`](app/Services/SubscriptionService.php)

## 5. Alur Paket (Source of Truth)
1. User memilih paket di halaman pricing.
2. Paket tervalidasi saat registrasi.
3. Subscription dibuat berdasarkan `config/plans.php` (tanpa masa trial).
4. Enforcement kuota booking dilakukan di `SubscriptionService`.
5. UI menampilkan status paket + kuota pada onboarding, dashboard, billing.

## 6. Aturan UX Paket
- Informasi paket harus konsisten antar halaman.
- Terminologi paket dan kuota tidak boleh berbeda.
- Error kuota harus jelas dan disertai arahan upgrade.

## 7. Uji Kualitas Paket
Wajib ada test:
- Free kena limit booking total.
- Pro/Premium tidak kena limit Free.
- Alur onboarding tetap aman setelah enforcement paket.

## 8. Kalender & Notifikasi Booking Besok
- Halaman kalender menampilkan panel `Jadwal Besok` dengan quick action:
  - Detail booking.
  - Konfirmasi booking (untuk status menunggu).
  - Hubungi pelanggan via WhatsApp.
- Notifikasi booking besok bersifat opt-in per user melalui profil (`notify_tomorrow_booking`).
- Navbar menampilkan badge notifikasi pada ikon kalender jika ada booking besok dan notifikasi aktif.
- Reminder otomatis H-1 diproses command terjadwal:
  - `bookings:send-tomorrow-reminders` (default daily `18:00`).
- Auto-settle pembayaran booking terlewat diproses command terjadwal:
  - `payments:auto-settle-past-service` (default setiap `15` menit).

## 9. Aturan Siklus Booking & Kuota
- Booking dengan tanggal layanan yang sudah berlalu menjadi read-only:
  - tidak bisa `edit`,
  - tidak bisa `hapus`,
  - hanya bisa dilihat detailnya.
- Kuota paket menggunakan perhitungan kumulatif (`bookings_consumed_total`) agar:
  - setiap booking yang pernah dibuat tetap tercatat sebagai pemakaian,
  - menghapus booking tidak mengembalikan kuota.

## 10. Arsitektur Pembayaran Booking
- CTA di halaman booking menggunakan label `Pembayaran` untuk mengarahkan user ke modul pembayaran booking terkait.
- Pembayaran booking menggunakan skema bertahap:
  - Tahap 1: DP minimum wajib dibayar.
  - Nominal DP dapat disesuaikan manual per booking.
  - Tahap 2: Pelunasan manual bisa dilakukan kapan saja selama layanan belum lewat.
- Guard proses:
  - Booking tidak boleh `confirmed` jika DP belum dibayar.
  - Pelunasan manual tidak dapat diproses jika booking `canceled` atau tanggal layanan sudah lewat.
  - Booking non-canceled yang sudah lewat tanggal layanan akan otomatis ditandai `Lunas` (scheduled command).
  - Aksi `Batal` pada modul pembayaran membatalkan booking tanpa menghapus pemasukan DP yang sudah diterima.
  - Pada detail booking hanya ada CTA `Pembayaran` (tanpa tombol `Bayar` terpisah).
  - Booking `canceled` tidak bisa membuka modul pembayaran booking terkait.
