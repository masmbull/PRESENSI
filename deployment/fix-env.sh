#!/usr/bin/env bash
# FIX .env produksi: isi APP_KEY + secrets, set production, re-cache, restart FPM.
set -euo pipefail
APP_DIR="/var/www/presensi/attendance-backend"
DOMAIN="attendance.hrismitogroup.web.id"

cd "$APP_DIR"
cp .env ".env.bak.$(date +%Y%m%d%H%M%S)"

NEWKEY=$(openssl rand -hex 24)
NEWPASS=$(openssl rand -base64 24 | tr -d '\n')

sed -i \
  -e "s|^APP_NAME=.*|APP_NAME=\"Presensi SPG\"|" \
  -e "s|^APP_ENV=.*|APP_ENV=production|" \
  -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
  -e "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" \
  -e "s|^LOG_LEVEL=.*|LOG_LEVEL=warning|" \
  -e "s|^FACEID_ENABLED=.*|FACEID_ENABLED=false|" \
  -e "s|^FACE_API_BASE=.*|FACE_API_BASE=http://127.0.0.1:8090|" \
  -e "s|^FACEID_API_KEY=.*|FACEID_API_KEY=$NEWKEY|" \
  -e "s|^FACEID_ADMIN_PASSWORD=.*|FACEID_ADMIN_PASSWORD=$NEWPASS|" \
  .env

php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.3-fpm php8.4-fpm
sleep 2

echo "=== SECRETS BARU (simpan!) ==="
echo "FACEID_API_KEY=$NEWKEY"
echo "FACEID_ADMIN_PASSWORD=$NEWPASS"
echo "=== health ==="
curl -s -o /dev/null -w "home: %{http_code}\n" -H "Host: $DOMAIN" http://127.0.0.1/
curl -s -o /dev/null -w "api-healthz: %{http_code}\n" -H "Host: $DOMAIN" -H "X-Api-Key: $NEWKEY" http://127.0.0.1/api/healthz
curl -s -H "Host: $DOMAIN" -H "X-Api-Key: $NEWKEY" http://127.0.0.1/api/my-ip
echo
curl -s -o /dev/null -w "kelola-wajah: %{http_code}\n" -u "admin:$NEWPASS" -H "Host: $DOMAIN" http://127.0.0.1/kelola-wajah
echo "✅ .env fix selesai."
