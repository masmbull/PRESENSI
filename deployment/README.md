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
- PHP 8.4 + Composer + Python 3 + nginx (+ ekstensi) — script install otomatis

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
php artisan config:cache && sudo systemctl restart php8.4-fpm
curl -H "X-Api-Key: <key>" https://<domain>/api/healthz
```

## Login panel admin

- URL: `https://<domain>/admin/login` (email + kata sandi).
- `ADMIN_EMAIL` di `.env` (default `admin@presensi.local`), kata sandinya = `FACEID_ADMIN_PASSWORD`.
- Akun dibuat oleh seeder: `php artisan db:seed --force` (idempotent, aman diulang).
- Role: `admin` (semua menu), `manager` (presensi + kelola wajah), `supervisor` (hanya presensi).
  Ganti role: `php artisan tinker --execute="App\Models\User::where('email','<email>')->update(['role'=>'manager']);"`
- `/kelola-wajah` + `/admin` masih menerima **HTTP Basic** (user `admin` + password yang sama)
  untuk script/uitest, tapi browser sebaiknya lewat form login.

## Update kode

```bash
ssh -i <key>.pem <user>@<ip-server>
bash /var/www/presensi/deployment/deploy.sh    # pull + composer + migrate + cache + restart + health
```

> Kalau `git pull` gagal ("local changes would be overwritten"), jalankan `git checkout -- .`
> dulu — file yang diedit manual di server (mis. `quicktest.sh`) akan ketimpa versi repo.

> Kalau `php artisan config:cache` / `route:cache` error
> `Argument #1 ($routes) must be of type RouteCollection, CompiledRouteCollection given`
> atau route baru tetap 404, penyebabnya `bootstrap/cache/*` milik `root` (sisa perintah `sudo php`).
> Benerin sekali:
> ```bash
> cd /var/www/presensi/attendance-backend
> sudo rm -rf bootstrap/cache/*
> sudo chown -R <user>:www-data bootstrap/cache storage database
> php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
> sudo systemctl restart php8.4-fpm
> ```

## Troubleshooting

| Gejala | Cek |
|---|---|
| 502 | `systemctl status php8.4-fpm` — pool/versi PHP |
| Route baru 404 setelah pull | `sudo rm -rf bootstrap/cache/*` + chown, lalu cache ulang + restart php-fpm |
| Composer "requires PHP >= 8.4" | pastikan `php -v` 8.4 & `fastcgi_pass` socket 8.4 |
| Cookie/session hilang, logout 419 | `SESSION_DRIVER`/cookie `.env` — reload + `config:cache` |
| "Engine wajah mati" | `systemctl status faceid-engine` · `journalctl -u faceid-engine -n50` |
| Kamera/GPS gak jalan di HP | harus HTTPS (secure context) |
| 401 API key | key `.env` beda sama halaman — reload + `config:cache` |
| 413 upload | naikin `client_max_body_size` di nginx-conf |

## Keamanan

- Hanya placeholder di repo; nilai asli cuma di server.
- `pro_faces.json`, `attendance.json`, `.env`, `certs/` — **jangan pernah commit** (sudah di `.gitignore`).
- Rotasi API key SECARA BERKALA kalau repo berubah.