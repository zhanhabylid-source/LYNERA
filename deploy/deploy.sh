#!/usr/bin/env bash
# =====================================================================
#  LYNERA - Skrip deploy VPS otomatis (Ubuntu/Debian + Nginx + MySQL)
#  Jalankan sebagai user yang punya akses sudo:
#     bash deploy.sh
#  ATAU tempel seluruh isi file ini ke terminal VPS.
# =====================================================================

#### ====== EDIT 6 BARIS DI BAWAH INI SAJA ====== ####
REPO_URL="https://github.com/lyneglam-lang/lynera.git"
BRANCH="main"                       # branch yang mau di-deploy
DOMAIN="lynera.my.id"               # domain aplikasi
DB_PASSWORD="GANTI_PASSWORD_KUAT"   # password MySQL untuk user 'lynera'
RESEND_KEY="GANTI_re_API_KEY"       # API key Resend (re_...)
CERTBOT_EMAIL="email@anda.com"      # email untuk sertifikat SSL
#### ============================================== ####

set -e
export DEBIAN_FRONTEND=noninteractive
export COMPOSER_ALLOW_SUPERUSER=1

echo ">> [1/11] Install paket sistem..."
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl \
  unzip git nginx mariadb-server curl
command -v composer >/dev/null 2>&1 || { curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer; }
command -v node >/dev/null 2>&1 || { curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - && sudo apt install -y nodejs; }

echo ">> [2/11] Buat database MySQL/MariaDB..."
sudo systemctl enable --now mariadb 2>/dev/null || sudo systemctl enable --now mysql 2>/dev/null || true
sudo mysql -e "CREATE DATABASE IF NOT EXISTS lynera CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'lynera'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
ALTER USER 'lynera'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON lynera.* TO 'lynera'@'localhost';
FLUSH PRIVILEGES;"

echo ">> [3/11] Clone kode dari GitHub..."
sudo rm -rf /var/www/lynera
sudo git clone -b "$BRANCH" "$REPO_URL" /var/www/lynera
cd /var/www/lynera

echo ">> [4/11] Install dependency & build aset..."
sudo -E composer install --optimize-autoloader --no-dev
sudo npm ci
sudo npm run build

echo ">> [5/11] Konfigurasi .env..."
sudo cp .env.example .env
sudo php artisan key:generate
sudo sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sudo sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sudo sed -i "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
sudo sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
sudo sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" .env
sudo sed -i "s|^DB_PORT=.*|DB_PORT=3306|" .env
sudo sed -i "s|^DB_DATABASE=.*|DB_DATABASE=lynera|" .env
sudo sed -i "s|^DB_USERNAME=.*|DB_USERNAME=lynera|" .env
sudo sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
sudo sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=file|" .env
sudo sed -i "s|^CACHE_STORE=.*|CACHE_STORE=file|" .env
sudo sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|" .env
sudo sed -i "s|^MAIL_MAILER=.*|MAIL_MAILER=resend|" .env
sudo sed -i "s|^RESEND_API_KEY=.*|RESEND_API_KEY=${RESEND_KEY}|" .env

echo ">> [6/11] Migrasi database + akun super admin + storage link..."
sudo php artisan migrate --force
sudo php artisan db:seed --class=AdminUserSeeder --force
sudo php artisan storage:link

echo ">> [7/11] Set hak akses folder..."
sudo chown -R www-data:www-data /var/www/lynera
sudo chmod -R 775 /var/www/lynera/storage /var/www/lynera/bootstrap/cache

echo ">> [8/11] Optimasi produksi (cache)..."
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache

echo ">> [9/11] Konfigurasi Nginx..."
sudo cp deploy/nginx-lynera.conf /etc/nginx/sites-available/lynera
sudo sed -i "s|lynera.my.id|${DOMAIN}|g" /etc/nginx/sites-available/lynera
sudo ln -sf /etc/nginx/sites-available/lynera /etc/nginx/sites-enabled/lynera
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx

echo ">> [10/11] Aktifkan scheduler (cron)..."
( sudo crontab -l 2>/dev/null | grep -v 'artisan schedule:run'; \
  echo "* * * * * cd /var/www/lynera && php artisan schedule:run >> /dev/null 2>&1" ) | sudo crontab -

echo ">> [11/11] Pasang SSL (Let's Encrypt)..."
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d "${DOMAIN}" -d "www.${DOMAIN}" --non-interactive --agree-tos -m "${CERTBOT_EMAIL}" \
  || echo "!! SSL gagal. Pastikan A record ${DOMAIN} sudah mengarah ke IP VPS, lalu ulang: sudo certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"

echo ""
echo "=========================================================="
echo " SELESAI! Buka: https://${DOMAIN}/login"
echo " Login sebagai Super Admin (lihat kredensial AdminUserSeeder)."
echo "=========================================================="
