# Panduan Deploy LYNERA ke VPS (Ubuntu/Debian + Nginx + MySQL + PHP 8.2)

Domain: **lynera.my.id** · Stack: Laravel 12 + Blade/Vite · Email: Resend

> Catatan: `.env` TIDAK ikut ke GitHub (berisi rahasia). Jadi di VPS Anda membuat `.env` sendiri dari `.env.example`.

---

## 0. Prasyarat DNS
Arahkan domain ke IP VPS Anda di panel DNS (Rumahweb):
- Record **A** `lynera.my.id` → `IP_VPS_ANDA`
- (opsional) Record **A** `www` → `IP_VPS_ANDA`

> Record email Resend (`resend._domainkey`, `send` MX/TXT, `_dmarc`) JANGAN dihapus — biarkan apa adanya.

---

## 1. Install kebutuhan di VPS
```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.2 + ekstensi Laravel
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl unzip git

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20 (untuk build aset Vite)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Nginx + Database (Debian: mariadb-server | Ubuntu: boleh mysql-server)
sudo apt install -y nginx mariadb-server
```

---

## 2. Buat database MySQL
```bash
sudo mysql
```
```sql
CREATE DATABASE lynera CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lynera'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT_ANDA';
GRANT ALL PRIVILEGES ON lynera.* TO 'lynera'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 3. Ambil kode dari GitHub
```bash
sudo mkdir -p /var/www && cd /var/www
sudo git clone -b BRANCH_ANDA https://github.com/USER/REPO.git lynera
cd lynera
```
> Ganti `BRANCH_ANDA`, `USER/REPO` sesuai repo/branch hasil "Save to GitHub".
> Jika kode berada di subfolder `lynera/` dalam repo, sesuaikan path root.

---

## 4. Install dependency & build aset
```bash
composer install --optimize-autoloader --no-dev
npm ci
npm run build
```

---

## 5. Konfigurasi `.env`
```bash
cp .env.example .env
php artisan key:generate
nano .env
```
Isi/ubah nilai berikut di `.env`:
```env
APP_NAME="LYNERA"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lynera.my.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lynera
DB_USERNAME=lynera
DB_PASSWORD=PASSWORD_KUAT_ANDA

# Sesi & cache sederhana (tanpa Redis) — aman untuk skala awal
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

# Email (Resend)
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="noreply@lynera.my.id"
MAIL_FROM_NAME="LYNERA"
RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxx   # <- API key Resend Anda
```

---

## 6. Migrasi database & data awal
```bash
php artisan migrate --force

# Buat akun Super Admin (WAJIB)
php artisan db:seed --class=AdminUserSeeder --force

# (OPSIONAL) data demo tenant/booking — JANGAN dipakai kalau ini produksi asli
# php artisan db:seed --class=DemoDataSeeder --force

php artisan storage:link
```

---

## 7. Hak akses folder
```bash
sudo chown -R www-data:www-data /var/www/lynera
sudo chmod -R 775 /var/www/lynera/storage /var/www/lynera/bootstrap/cache
```

---

## 8. Optimasi produksi (cache)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> Jika nanti mengubah `.env`, jalankan ulang: `php artisan config:clear && php artisan config:cache`.

---

## 9. Konfigurasi Nginx
```bash
sudo cp deploy/nginx-lynera.conf /etc/nginx/sites-available/lynera.my.id
sudo ln -s /etc/nginx/sites-available/lynera.my.id /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

---

## 10. SSL / HTTPS (Let's Encrypt)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d lynera.my.id -d www.lynera.my.id
```

---

## 11. Scheduler (pengingat H-1 & auto-settle)
Aplikasi punya tugas terjadwal. Tambah cron:
```bash
sudo crontab -e
```
Tambahkan baris:
```
* * * * * cd /var/www/lynera && php artisan schedule:run >> /dev/null 2>&1
```
Ini menjalankan otomatis:
- `bookings:send-tomorrow-reminders` (setiap hari 18:00)
- `payments:auto-settle-past-service` (tiap 15 menit)

---

## 12. Verifikasi
- Buka `https://lynera.my.id/login` → login Super Admin.
- Uji daftar tenant baru + booking publik.
- Tes email: pastikan notifikasi "Booking Publik Baru" masuk inbox.

---

## Update kode di kemudian hari
```bash
cd /var/www/lynera
git pull
composer install --optimize-autoloader --no-dev
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo systemctl reload nginx
```

## Troubleshooting cepat
- **500 / halaman putih:** cek `storage/logs/laravel.log`; pastikan permission folder `storage` benar & `APP_KEY` terisi.
- **Aset (CSS/JS) tidak muncul:** pastikan `npm run build` sukses & folder `public/build` ada.
- **Gambar bukti bayar tidak tampil:** pastikan `php artisan storage:link` sudah dijalankan & `APP_URL` benar.
- **Email tidak terkirim:** cek `RESEND_API_KEY` benar & domain `lynera.my.id` berstatus Verified di Resend.
