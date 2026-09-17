#!/usr/bin/env bash
# quicktest.sh — test engine & API key hanya
# Usage: bash /var/www/presensi/deployment/quicktest.sh [api_key]
# Kalau ga punya api_key, passing saja (dia auto ambil dari .env)
set -e

cd /var/www/presensi/attendance-backend
APP_DIR=$(pwd)

# Ambil API key dari argumen atau .env
API_KEY="${1:-$(grep '^FACEID_API_KEY=' .env | cut -d= -f2-)}"
[ -z "$API_KEY" ] && echo "[SKIP] FACEID_API_KEY kosong" && exit 1

echo "=== FaceID Engine Test ==="
echo -n "engine healthz  : "
if curl -sf http://127.0.0.1:8090/healthz > /dev/null 2>&1; then
  echo "OK"
else
  echo "FAIL"
  exit 1
fi

echo ""
echo "=== Laravel API Test ==="
echo -n "api key valid   : "
if curl -sf -H "X-Api-Key: $API_KEY" http://127.0.0.1/api/healthz > /dev/null 2>&1; then
  echo "OK"
else
  echo "FAIL"
  exit 1
fi

echo ""
echo "Hasil cepat: engine & API jalan."
exit 0
