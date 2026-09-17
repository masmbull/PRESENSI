# Deploy Produksi (VPS/EC2 + Cloudflare)

Panduan pasang backend absensi ke server + HTTPS via Cloudflare.
**Semua nilai asli pakai placeholder** (domain, kunci, path) — diisi manual di server,
jangan pernah commit nilai asli (repo ini public).

## Arsitektur

```
HP SPG (browser)
   │ https
   ▼
Cloudflare (TLS edge, DNS proxied)
   │
   ▼
Nginx ── PHP-FPM ── Laravel (attendance-backend, SQLite)
             │ proxy (server-side)
             ▼
     AI engine (uvicorn 127.0.0.1:8090, ONNX)  ← localhost only
```

- Engine wajah **tidak** di-expose ke internet (localhost 8090).
- Kamera & GPS butuh **secure context** → HTTPS wajib di edge.

## Prasyarat VPS/EC2

- Ubuntu 22.04 / 24.04 LTS (script bikin swap kalau RAM kecil)
- Open port **22, 80, 443** — **jangan buka 8090**
- PHP 8.3 + Composer + Python 3 + nginx (+ ekstensi) — script install otomatis

## Deploy pertama (auto setup)

```bash
ssh -i <key>.pem <user>@<ip-server>
curl -fsSL https://raw.githubusercontent.com/<owner>/<repo>/main/deployment/setup-server.sh -o setup.sh
bash setup.sh
```

Di akhir script ada health check (`engine` + `nginx/php` — dua-duanya harus `ok`).

## Manual: domain + TLS (Cloudflare)

1. DNS → tambah **A** record untuk subdomain → IP VPS (aktifkan **proxied**).
2. SSL/TLS → mode **Full (strict)**.
3. SSL/TLS → Origin Server → buat **Origin Certificate** → simpan `origin.pem` + `origin.key` di server (`/etc/nginx/ssl/`).
4. Edge Certificates → **Always Use HTTPS**: ON. HSTS: ON (setelah tes).
5. Speed → Optimization → **Rocket Loader: OFF** (ganggu inline JS absen).

## Daftar awal & kunci (WAJIB)

```bash
openssl rand -hex 24          # generate FACEID_API_KEY
nano /var/www/presensi/attendance-backend/.env   # isi FACEID_API_KEY + FACEID_ADMIN_PASSWORD
php artisan config:cache && sudo systemctl restart php8.3-fpm
curl -H "X-Api-Key: <key>" https://<domain>/api/healthz
```

## Update kode

```bash
ssh -i <key>.pem <user>@<ip-server>
bash /var/www/presensi/deployment/deploy.sh    # pull + composer + migrate + cache + restart + health
```

## Troubleshooting

| Gejala | Cek |
|---|---|
| 502 | `systemctl status php8.3-fpm` — pool/versi PHP |
| "Engine wajah mati" | `systemctl status faceid-engine` · `journalctl -u faceid-engine -n50` |
| Kamera/GPS gak jalan di HP | harus HTTPS (secure context) |
| 401 API key | key `.env` beda sama halaman — reload + `config:cache` |
| 413 upload | naikin `client_max_body_size` di nginx-conf |

## Keamanan

- Hanya placeholder di repo; nilai asli cuma di server.
- `pro_faces.json`, `attendance.json`, `.env`, `certs/` — **jangan pernah commit** (sudah di `.gitignore`).
- Rotasi API key SECARA BERKALA kalau repo berubah.