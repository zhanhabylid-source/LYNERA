# Resend Email Setup

LYNERA sudah memakai mail transport Resend bawaan Laravel. Konfigurasi mailer ada di `config/mail.php`, sedangkan API key dibaca dari `RESEND_API_KEY` melalui `config/services.php`.

## Aktifkan di environment

1. Buat API key di dashboard Resend: https://resend.com/api-keys.
2. Verifikasi domain pengirim di Resend. Environment LYNERA memakai sender `noreply@lynera.my.id` dari domain terverifikasi `lynera.my.id`.
3. Isi `.env` tanpa meng-commit secret:

```env
MAIL_MAILER=resend
MAIL_FROM_ADDRESS=noreply@domain-terverifikasi.example
MAIL_FROM_NAME="LYNERA"
RESEND_API_KEY=re_xxxxxxxxxxxxxxxxx
```

4. Bersihkan konfigurasi cache: `php artisan optimize:clear`.

## Alur email yang sudah terhubung

- Booking publik baru dikirim ke email MUA melalui `NotificationService::sendPublicBookingAlert`.
- Pengingat booking besok dikirim melalui `sendTomorrowBookingReminderEmail` dan command scheduler `bookings:send-tomorrow-reminders`.
- Email verifikasi akun dikirim oleh event `Registered` karena model `User` mengimplementasikan `MustVerifyEmail`.
- Reset password memakai notifikasi LYNERA khusus melalui `User::sendPasswordResetNotification`.

Semua alur di atas memakai template responsif `resources/views/emails/notification.blade.php` dengan logo LYNERA, aksen rose/gold, tombol tindakan, fallback URL, dan catatan keamanan.

## Verifikasi aman

Jalankan satu alur registrasi atau command reminder, lalu periksa delivery log Resend dan `storage/logs/laravel.log`. Secret hanya disimpan di `.env` yang diabaikan Git; `.env.example` tidak memuat API key.