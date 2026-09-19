#!/usr/bin/env bash
# PRESENSI — update deploy berikutnya (jalankan di server, setelah setup awal):
#   bash /var/www/presensi/deployment/deploy.sh
set -euo pipefail
APP_DIR="/var/www/presensi"

cd "$APP_DIR"
echo "==> git pull"
# file yang diedit manual di server (mis. quicktest.sh) bisa bikin pull gagal → kembalikan ke versi repo
git checkout -- . 2>/dev/null || true
git pull --ff-only

echo "==> Python deps"
.venv/bin/pip install --quiet -r requirements.txt

echo "==> Laravel build"
cd "$APP_DIR/attendance-backend"
# bersihin cache (termasuk sisa bootstrap/cache milik root yang bikin route:cache error/route baru 404)
sudo rm -rf bootstrap/cache/* 2>/dev/null || true
sudo chown -R "$USER":www-data bootstrap/cache storage database 2>/dev/null || true
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan db:seed --force                                   # idempotent: pastikan akun admin ada
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Permission & restart service"
cd "$APP_DIR"
sudo chown -R "$USER":www-data "$APP_DIR"
sudo chmod -R g+rwX "$APP_DIR/attendance-backend/storage" \
                    "$APP_DIR/attendance-backend/bootstrap/cache" \
                    "$APP_DIR/attendance-backend/database" \
                    "$APP_DIR"
sudo systemctl restart faceid-engine php8.4-fpm

echo "==> Cek"
sleep 3
echo -n "engine    : "
curl -fsS http://127.0.0.1:8090/healthz && echo
echo -n "nginx+php : "
KEY=$(grep '^FACEID_API_KEY=' "$APP_DIR/attendance-backend/.env" | cut -d= -f2-)
curl -fsS -H "X-Api-Key: $KEY" "http://127.0.0.1/api/healthz" && echo
echo -n "login page: "
curl -fsS -o /dev/null -w "HTTP %{http_code}\n" "http://127.0.0.1/admin/login"
echo "✅ Deploy selesai."