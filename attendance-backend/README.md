# Attendance Backend — Laravel 13

Web absensi SPG + API. Absen hanya bisa dari dalam **radius pin toko** (default 150 m,
diatur per toko via `stores.radius_m`). **Face ID default mati** (`FACEID_ENABLED=false`).

## Nyalain

```powershell
cd attendance-backend
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
.\run-backend.bat        # HTTP :8000 + HTTPS :8444 (via tls-proxy.py)
```

- Web SPG (laptop): `http://localhost:8000`
- HP: wajib **HTTPS** (GPS cuma jalan di secure context) — port `:8444`, cert self-signed (bikin di folder induk: `python mkcert.py`)

**Login admin default:** `admin@presensi.local` / `admin123`
(`ADMIN_EMAIL` + `FACEID_ADMIN_PASSWORD` di `.env`; `php artisan db:seed` bikin ulang
akunnya idempotent). Ganti passwordnya sebelum dipakai di internet.

> Face ID butuh **ai-service Python jalan** (folder induk, `python restart.py`, port 8090) —
> Laravel cuma proxy. Kalau engine mati, scan wajah bilang "Engine wajah lagi mati".

## Alur absen

1. Pilih **Kota** → **Toko** → **Nama Karyawan (SPG)** → **ID Karyawan muncul**
2. (opsional) Verifikasi wajah: selfie + tombol **📷 Scan Wajah**
3. Peta: bulatan zona radius + posisi GPS
4. Tombol **Absen Masuk / Absen Pulang**

Server nolak (`422`) kalau jarak > radius, atau cooldown `ATTENDANCE_COOLDOWN` (60 dtk)
belum lewat. Dist/radius dibales di response buat debugging.

## Data dummy & import

```powershell
php artisan stores:import      # master toko dari ../stores.json (upsert, bikin kota otomatis)
php artisan spg:seed           # SPG dummy: nama + employee_code SPG-XXX per toko
php artisan spg:seed --force   # hapus karyawan existing dulu, bikin ulang
php artisan migrate:fresh --seed   # reset semua data + build ulang
```

## API (base `http://localhost:8000`)

| Method | Path | Fungsi |
|---|---|---|
| GET | `/api/healthz` | health + status face_id/geofence + radius + timezone |
| GET | `/api/cities` | daftar kota |
| GET | `/api/stores?city_id=1` | toko di kota + jumlah SPG |
| GET | `/api/employees?store_id=1` | SPG + ID karyawan |
| POST | `/api/face/verify` | verify wajah (body = bytes foto) → `{ok, face_key, name, cosine}` |
| POST | `/api/attendances` | catat absen `{employee_id, type: masuk\|pulang, lat, lon, acc}` |
| GET | `/api/attendances` | riwayat (`?limit&date&type&store_id&employee_id&q`) |
| GET | `/api/attendances/summary` | rekap harian |
| DELETE | `/api/attendances/{id}` | hapus record |
| DELETE | `/api/attendances/clear` | kosongkan riwayat |
| POST | `/api/employees` | tambah karyawan |
| POST | `/api/employees/sync` | sinkron id wajah engine → employees |
| DELETE | `/api/employees/{id}` | hapus karyawan |

Semua API bisa diminta mewajibkan header `X-Api-Key` kalau `FACEID_API_KEY` diisi.

## Config `.env`

| Key | Default | Fungsi |
|---|---|---|
| `FACEID_ENABLED` | `false` | wajib verifikasi wajah engine sebelum absen |
| `FACE_API_BASE` | `http://127.0.0.1:8090` | alamat ai-service |
| `GEO_RADIUS_DEFAULT` | `150` | radius geofence default (m) |
| `FACEID_API_KEY` | kosong | diisi → semua endpoint wajib `X-Api-Key` |
| `ATTENDANCE_COOLDOWN` | `60` | jeda absen per karyawan per jenis (dtk) |
| `APP_TIMEZONE` | `Asia/Jakarta` | zona waktu presensi (WIB) |
| `GEO_ENABLED` | `true` | default saklar geo location di sidebar admin |
| `ATTENDANCE_COOLDOWN_ON` | `true` | default saklar anti dobel-klik |

## Zona waktu

Semua jam presensi pakai **WIB (Asia/Jakarta, UTC+7)** — diatur `APP_TIMEZONE`
(default `Asia/Jakarta`). Timestamp absen disimpan server dalam WIB dan tampilan
jam di halaman absen/riwayat ikut zona ini, apa pun zona device penggunanya.

## Saklar fitur (sidebar admin)

Admin bisa nyalain/matiin fitur langsung dari sidebar panel — gak perlu deploy.
Statusnya kesimpan di tabel `settings` dan **menang** atas nilai default di `.env`:

| Saklar | Efek kalau mati |
|---|---|
| Face ID | absen tanpa verifikasi wajah |
| Geo location | absen gak dibatasi radius toko |
| Anti dobel-klik | cooldown antar-absen dilewati |

Default awal diambil dari `FACEID_ENABLED`, `GEO_ENABLED`, `ATTENDANCE_COOLDOWN_ON`.
Status juga dibales `/api/healthz` (`face_id`, `geofence`).

## Catatan perbaikan

- **Sesi admin gak dimigrasi ulang tiap request.** Middleware `AdminAuth` dulu pakai
  `Auth::loginUsingId()`, yang di Laravel memanggil `session()->migrate(true)` — id
  session lama dihapus di setiap request. Akibatnya `/kelola-wajah` yang nge-fetch
  kota/toko/karyawan secara paralel saling menendang ke `/admin/login`, sehingga
  daftar toko nyangkut “Memuat data…” dan hitungannya 0. Sekarang pakai
  `Auth::setUser()` (`tests/Feature/AdminSessionTest.php` mengunci ini).
- Aksi balik ke halaman sendiri (`/kelola-wajah` dll) tetap dianggap login, jadi
  pagination + isi tabel kelihatan setelah data termuat.

## Struktur

```
resources/views/absen.blade.php   web SPG (kota→toko→nama→absen + peta)
resources/views/wajah.blade.php   admin kelola wajah & lokasi
app/Http/Controllers/Api/         MasterController (kota/toko), EmployeeController, AttendanceController
app/Support/Geo.php               haversine distance
app/Http/Middleware/ApiKey.php    proteksi X-Api-Key (opsional)
config/faceid.php                 toggle wajah + radius + api key
database/migrations/              cities, stores, employees, attendances
database/seeders/ + Console/      seeder & command import/seed
tls-proxy.py                      HTTPS :8444 → HTTP :8000 (buat GPS di HP)
run-backend.bat                   launcher windows
```