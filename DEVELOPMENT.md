# DEVELOPMENT.md — Panduan Develop Lanjutan

> Semua yang perlu diketahui buat lanjutin proyek ini: arsitektur, konvensi,
> dependensi, cara jalanin & ngetes. **Tidak ada kredensial di file ini** —
> nilai asli selalu di server via `.env`, jangan pernah di-commit.

---

## 1. Gambaran arsitektur

```
┌────────────┐   HTTPS    ┌──────────────────────────┐   HTTP    ┌─────────────────┐
│ HP SPG     │ ─────────► │ attendance-backend       │ ────────► │ mito engine     │
│ (browser)  │            │ Laravel + SQLite         │           │ FastAPI + ONNX  │
└────────────┘            │  - halaman absen /       │           │  - deteksi wajah│
                          │  - admin panel /         │           │  - embedding    │
                          │  - API (blade + JSON)    │           │  - liveness     │
                          └──────────────────────────┘           └─────────────────┘
                               │ Cloudflare (produksi)              localhost:8090 saja
```

- **attendance-backend/** — Laravel. Semua logika bisnis: geofence (haversine
  server-side), anti dobel absen, sesi admin, master data (kota/toko/karyawan),
  riwayat absen. DB **SQLite** (file `database/database.sqlite`).
- **mito/** + **models_pro/** — engine AI wajah (Python, CPU-only). Deteksi
  SCRFD → embedding ArcFace → liveness MiniFASNet → atribut. Engine **tidak
  diekspos ke internet**; Laravel manggil dia via `FACE_API_BASE` (localhost).
- Wajah tersimpan sebagai **embedding di `pro_faces.json`** (engine side) +
  `face_key` di tabel `employees` — foto mentah tidak dipakai untuk matching.

## 2. Stack & dependensi

| Komponen   | Versi          | File                                       |
|------------|----------------|--------------------------------------------|
| PHP        | ≥ 8.3          | `attendance-backend/composer.json`         |
| Laravel    | framework + tinker | `attendance-backend/composer.json`     |
| Dev PHP    | phpunit, pint, faker, mockery, collision | `require-dev`   |
| Python     | 3.12+ (teruji 3.13 Windows) | `requirements.txt`             |
| fastapi + uvicorn + pydantic | exact-pin    | `requirements.txt`         |
| onnxruntime + numpy + pillow | exact-pin (CPU, tanpa torch/cv2) | `requirements.txt` |
| cryptography | buat self-signed cert HTTPS lokal (kamera HP butuh secure context) | `requirements.txt` |

> Versi pip di-pin **exact** karena engine-nya sensitif — naikin satu-satu dan
> jalankan `python smoke_pro.py` setiap kali upgrade.

## 3. Setup awal (lokal)

```powershell
# 1) Engine (folder root repo)
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
python restart.py          # HTTP :8090 + HTTPS :8443

# 2) Backend
cd attendance-backend
copy .env.example .env     # lalu isi APP_KEY: php artisan key:generate
php artisan migrate --seed
.\run-backend.bat          # HTTP :8000 + HTTPS :8444

# 3) Data master & dummy
## 4. Struktur penting backend

```
app/Http/Controllers/
  Api/                    # dipakai halaman absen SPG (token api_key / dev-open)
    AttendanceController  #   absen masuk/pulang + geofence + cooldown
    EmployeeController    #   dropdown kota→toko→nama (hanya active=true)
    FaceController        #   verifikasi wajah (proxy ke engine)
    MasterController      #   daftar kota/toko
  Admin/                  # panel admin (session login + middleware AdminAuth)
    AttendanceAdminController, AdminUserController, FeatureController,
    EmployeeController    #   halaman /admin/karyawan (biodata HRD lengkap)
  FaceManagementController # /kelola-wajah: daftar wajah, master toko/kota,
                           #   ubah/nonaktif/hapus karyawan & toko
app/Models/               # Employee, Store, City, Attendance, User, Setting
resources/views/
  absen.blade.php         # halaman absen SPG (mobile-first, Leaflet + MapLibre)
  wajah.blade.php         # /kelola-wajah
  admin/                  # dashboard, absensi, karyawan, pengguna, akun
  layouts/admin.blade.php # sidebar + style global admin + helper JS ($, toast, api)
database/migrations/      # urut — kolom HR karyawan ada di 000011
database/seeders/         # EmployeeProfileSeeder (idempotent, data contoh)
tests/Feature/            # PHPUnit — semua fitur utama ke-cover
scripts/                  # util verifikasi (check-live.cjs, cdp.cjs, mojibake.py)
```

Konvensi yang sudah dipakai di repo (ikuti):
- **Blade + vanilla JS inline** (`@push('scripts')`), tanpa bundler build step
  untuk admin/absen. Helper global dari layout: `$()`, `toast()`, `esc()`, `api()`.
- **POST AJAX pakai header `X-CSRF-TOKEN`** dari `<meta name="csrf-token">`.
- Style admin global ada di `layouts/admin.blade.php` (`.fld`, `.mgrid`,
  `.modal`, `.pill`, `.table`) — view page cukup pakai, jangan duplikat.
- Semua **jam presensi WIB** (`Asia/Jakarta` di `config/app.php`).
- Nama karyawan **unique per toko** (`(store_id, lower(name))`) — jaga di
  controller manapun yang bikin/mindah karyawan.
- Data laporan dilindungi: hapus toko/karyawan yang punya riwayat absen
  **ditolak 422** — pakai nonaktif (`active=false`) untuk resign.

## 5. Menjalankan test

```powershell
cd attendance-backend
php artisan test            # PHPUnit — sqlite :memory:, tanpa engine/engine-key
```

- Test tidak butuh engine jalan, tidak butuh secrets (user dibuat via factory/DB).
- Kalau nambah fitur: tambah Feature test baru ala `EmployeeActiveTest.php`
  (create master → panggil route → assert JSON + efek DB).
- Verifikasi visual cepat tanpa browser: `node scripts/check-live.cjs`
  (butuh `FACEID_ADMIN_PASSWORD` di `.env` lokal), atau `scripts/cdp.cjs`
  untuk render + screenshot headless Edge.

## 6. CI (GitHub Actions)

`.github/workflows/ci.yml` — jalan di setiap push/PR ke `main`:
1. **backend**: PHP 8.3 → `composer install` → migrate sqlite → `php artisan test`.
2. **engine**: Python 3.12 → `pip install -r requirements.txt` → import semua
   modul `mito.*` (cek sintaks + dependensi; inferensi asli butuh CPU/lama,
   sengaja tidak dijalankan di CI).

## 7. Deploy (ringkas)

Panduan lengkap: `deployment/README.md`. Versi singkat tiap rilis:

```bash
cd /var/www/presensi && git pull origin main
cd attendance-backend
php artisan migrate --force        # HANYA kalau ada migrasi baru
php artisan config:clear && php artisan view:clear && php artisan route:clear
sudo systemctl restart php8.4-fpm  # kalau pakai php-fpm
# kalau ada route baru wajib route:clear; kalau ada blade baru wajib view:clear
```

## 8. Ide lanjutan (backlog)

- Export rekap absen per periode (XLSX) di halaman Riwayat.
- Notifikasi akhir kontrak < 60 hari (badge sudah ada di Data Karyawan).
- Role manager dibatasi per kota (sekarang global).
- Backup otomatis `database.sqlite` + `pro_faces.json` ke object storage.
- Self-host tile peta kalau CDN publik diblokir jaringan tertentu.

php artisan stores:import  # import toko dari stores.json
php artisan spg:seed       # karyawan dummy
php artisan db:seed --class=Database\Seeders\EmployeeProfileSeeder  # profil HRD contoh
```

HTTPS lokal itu **wajib** untuk fitur kamera/GPS di HP (`getUserMedia` butuh
secure context) — makanya ada `mkcert.py` (engine) dan `run-backend.bat`
(backend, port 8444).
