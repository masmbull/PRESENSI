#!/usr/bin/env bash
# PRESENSI — update deploy berikutnya (jalankan di server, setelah setup awal):
#   bash /var/www/presensi/deployment/deploy.sh
set -euo pipefail
APP_DIR="/var/www/presensi"

cd "$APP_DIR"
echo "==> git pull"
git pull --ff-only

echo "==> Python deps"
.venv/bin/pip install --quiet -r requirements.txt

echo "==> Laravel build"
cd "$APP_DIR/attendance-backend"
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
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
sudo systemctl restart faceid-engine php8.3-fpm

echo "==> Cek"
sleep 3
echo -n "engine    : "
curl -fsS http://127.0.0.1:8090/healthz && echo
echo -n "nginx+php : "
KEY=$(grep '^FACEID_API_KEY=' "$APP_DIR/attendance-backend/.env" | cut -d= -f2-)
curl -fsS -H "X-Api-Key: $KEY" "http://127.0.0.1/api/healthz" && echo
echo "✅ Deploy selesai."