# =====================================================================
# PRESENSI — setup satu-kali server (AWS EC2 Ubuntu 22.04/24.04 / VPS)
#
# Yang dipasang: nginx + PHP 8.3 FPM + Composer + Python venv + SQLite,
# clone repo, migrate, systemd service engine wajah, nginx site.
#
# Pakai:
#   bash setup-server.sh                 # tanpa data dummy
#   SEED_DUMMY=1 bash setup-server.sh    # + seed kota/toko/karyawan dummy
#
# Sebelum jalan, pastikan:
#   - security group udah buka 22 (SSH), 80 & 443 (HTTP/HTTPS)
#   - port 8090 JANGAN dibuka (engine cuma denger localhost)
# =====================================================================
set -euo pipefail

REPO_URL="https://github.com/masmbull/PRESENSI.git"
APP_DIR="/var/www/presensi"
DOMAIN="attendance.example.com"
SEED_DUMMY="${SEED_DUMMY:-0}"

if [ "$(id -u)" -eq 0 ]; then
  echo "Jangan jalan sebagai root — pakai user biasa (ubuntu/ec2-user) yang punya sudo." >&2
  exit 1
fi

echo "==> [1/8] Paket sistem (nginx, PHP 8.3, Composer, Python)"
sudo apt-get update -y
if ! apt-cache show php8.4-fpm >/dev/null 2>&1; then
  echo "    PHP 8.3 gak ada di repo default (Ubuntu 22.04) — nambahin PPA ondrej/php"
  sudo apt-get install -y software-properties-common
  sudo add-apt-repository -y ppa:ondrej/php
  sudo apt-get update -y
fi
sudo apt-get install -y \
  ca-certificates curl git unzip nginx sqlite3 \
  python3 python3-venv python3-pip \
  composer \
  php8.4-fpm php8.4-cli php8.4-mbstring php8.4-xml php8.4-curl \
  php8.4-sqlite3 php8.4-gd php8.4-zip php8.4-bcmath php8.4-intl

echo "==> [2/8] Swap (instance RAM kecil butuh ini buat ONNX)"
if [ "$(free -m | awk '/^Mem:/{print $2}')" -lt 3500 ] && [ -z "$(swapon --show)" ]; then
  sudo fallocate -l 2G /swapfile
  sudo chmod 600 /swapfile
  sudo mkswap /swapfile
  sudo swapon /swapfile
  echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab >/dev/null
  echo "    swap 2GB aktif"
else
  echo "    skip (RAM cukup / swap udah ada)"
fi

echo "==> [3/8] Clone repo ke $APP_DIR"
sudo mkdir -p "$APP_DIR"
sudo chown "$USER":"$USER" "$APP_DIR"
if [ ! -d "$APP_DIR/.git" ]; then
  git clone "$REPO_URL" "$APP_DIR"
else
  git -C "$APP_DIR" pull --ff-only
fi

echo "==> [4/8] Python venv + dependency engine (onnxruntime dll)"
PY=python3
if ! python3 -c 'import sys; raise SystemExit(0 if sys.version_info >= (3,11) else 1)' 2>/dev/null; then
  echo "    Python bawaan < 3.11 (butuh >=3.11 buat numpy/onnxruntime) — nambahin PPA deadsnakes"
  sudo add-apt-repository -y ppa:deadsnakes/ppa
  sudo apt-get update -y
  sudo apt-get install -y python3.12 python3.12-venv
  PY=python3.12
fi
cd "$APP_DIR"
$PY -m venv .venv
.venv/bin/pip install --quiet --upgrade pip
.venv/bin/pip install --quiet -r requirements.txt

echo "==> [5/8] Laravel: composer, key, migrate"
cd "$APP_DIR/attendance-backend"
[ -f .env ] || cp "$APP_DIR/deployment/.env.production" .env
[ -f database/database.sqlite ] || touch database/database.sqlite
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate --force
php artisan migrate --force
if [ "$SEED_DUMMY" = "1" ]; then
  echo "    seed data dummy (kota/toko/karyawan + foto tes)"
  php artisan db:seed --force
fi
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [6/8] Permission (www-data = php-fpm & engine, user ini buat deploy)"
cd "$APP_DIR"
sudo chown -R "$USER":www-data "$APP_DIR"
sudo chmod -R g+rX "$APP_DIR"
sudo chmod -R g+rwX \
  "$APP_DIR/attendance-backend/storage" \
  "$APP_DIR/attendance-backend/bootstrap/cache" \
  "$APP_DIR/attendance-backend/database" \
  "$APP_DIR"

echo "==> [7/8] Systemd: faceid-engine (uvicorn 127.0.0.1:8090)"
sudo cp "$APP_DIR/deployment/faceid-engine.service" /etc/systemd/system/faceid-engine.service
sudo systemctl daemon-reload
sudo systemctl enable --now faceid-engine

echo "==> [8/8] Nginx site: $DOMAIN"
sudo mkdir -p /etc/nginx/ssl
if [ ! -s /etc/nginx/ssl/origin.key ]; then
  echo "    Sertifikat origin Cloudflare belum ada — bikin self-signed sementara (30 hari)."
  echo "    GANTI dengan Origin Certificate Cloudflare (panduan: deployment/README.md bagian B3)."
  sudo openssl req -x509 -nodes -newkey rsa:2048 -days 30 \
    -keyout /etc/nginx/ssl/origin.key -out /etc/nginx/ssl/origin.pem \
    -subj "/CN=$DOMAIN" 2>/dev/null
fi
sudo cp "$APP_DIR/deployment/nginx-presensi.conf" /etc/nginx/sites-available/presensi.conf
sudo ln -sf /etc/nginx/sites-available/presensi.conf /etc/nginx/sites-enabled/presensi.conf
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx

echo
echo "==> Cek kesehatan"
sleep 4
echo -n "engine    : "
curl -fsS http://127.0.0.1:8090/healthz && echo
echo -n "nginx+php : "
KEY=$(grep '^FACEID_API_KEY=' "$APP_DIR/attendance-backend/.env" | cut -d= -f2-)
curl -fsS -H "X-Api-Key: $KEY" "http://127.0.0.1/api/healthz" && echo
echo
echo "✅ Server siap. Lanjutan manual:"
echo "   1) Cloudflare DNS : A  attendance  ->  <IP-EC2>  (Proxied)"
echo "   2) Cloudflare SSL : mode Full (strict) + pasang Origin Certificate ke /etc/nginx/ssl/"
echo "   3) Ganti kunci    : FACEID_API_KEY di $APP_DIR/attendance-backend/.env, lalu:"
echo "                       cd $APP_DIR/attendance-backend && php artisan config:cache && sudo systemctl restart php8.4-fpm"
echo "   Panduan lengkap   : $APP_DIR/deployment/README.md"