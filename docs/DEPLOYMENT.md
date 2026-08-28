# LYNERA Deployment Checklist

> Smart Tools for Modern Makeup Artists

## Environment
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Configure `APP_KEY`
- Use secure `APP_URL` (https)

## Performance
- Use Redis for cache/session/queue (`CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`)
- Run:
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`

## Queue Worker
- Run queue workers via Supervisor/systemd:
  - `php artisan queue:work --tries=3 --timeout=120`

## Database
- Run migrations:
  - `php artisan migrate --force`

## Paket SaaS
- Pastikan konfigurasi paket di `config/plans.php` sudah sesuai kebutuhan produksi.
- Jalankan cache config setelah perubahan paket:
  - `php artisan config:cache`
- Verifikasi alur berikut setelah deploy:
  - register dengan plan `free/pro/premium`,
  - onboarding,
  - enforcement limit booking total untuk plan Free (maks 10 booking),
  - akses berlanjut tanpa masa trial.

