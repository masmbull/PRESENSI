# Face ID — Presensi SPG Indonesia

Proyek **presensi SPG (Sales Promotion Girl) seluruh Indonesia**: web absen masuk/pulang
berbasis **lokasi GPS**, plus **verifikasi wajah** (AI face recognition) opsional.
Backend **Laravel 13 + SQLite**, AI engine **MITO (ONNX)** di **Python/FastAPI**.

> 🔒 Repo bersifat **public** — semua kredensial, cert, data biometrik/absensi,
> `.env` asli, domain dan IP produksi **tidak ikut di-commit**. Setiap file config
> cuma berisi **placeholder** — nilai asli diisi langsung di server via SSH
> (jangan pernah commit nilai asli ke repo ini).

## Fitur

- ✅ Absen **Masuk / Pulang** divalidasi **lokasi GPS** — geofence radius dari pinpoint toko (haversine, server-side)
- ✅ Flow SPG: **Kota → Toko → Nama → muncul ID Karyawan → absen**
- ✅ Verifikasi **wajah** opsional (selfie scan) — bisa dimatikan lewat config (`FACEID_ENABLED=false`)
- ✅ Admin: kelola kota / toko / karyawan / wajah lewat web
- ✅ Toko yang sudah ada bisa **diubah** (nama, kota, alamat, koordinat, radius) dari tab Master data — tombol **Ubah** di daftar toko
- ✅ Saklar fitur di sidebar admin (Face ID / Geo location / anti dobel-klik) — aktif-nonaktif langsung, tanpa deploy
- ✅ Semua jam presensi **WIB (Asia/Jakarta, UTC+7)**
- ✅ Riwayat & rekap absen via API
- ✅ Peta Leaflet dark, tampilan mobile-first

## Struktur

```
attendance-backend/   Laravel 13 — web absensi + API (punya README sendiri)
mito/                 engine face AI: detector, embedding, liveness, attributes
models_pro/           model ONNX (~52 MB) — ikut repo biar clone langsung jalan
static/               playground lama (face-api.js, jalan di browser)
deployment/           panduan & skrip deploy produksi (placeholder-only)
stores.json           data master toko (nama, kota, lat/lon) buat import
```

## Quick start lokal

**AI engine** (folder repo):
```powershell
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
python restart.py            # http :8090 + https :8443
python smoke_pro.py          # smoke test — harus semua 200
```

**Backend absensi** (`attendance-backend/`):
```powershell
cd attendance-backend
copy .env.example .env       # lalu: php artisan key:generate
php artisan migrate --seed
.\run-backend.bat            # http :8000 + https :8444
```

## Setup data dummy

```powershell
# dari folder attendance-backend:
php artisan stores:import    # import master toko dari stores.json (bikin kota otomatis)
php artisan spg:seed         # seed SPG dummy per toko (ID SPG-001, ...)
```

## Deploy produksi

Lihat **`deployment/README.md`** — panduan VPS/EC2 + Cloudflare. Semua nilai asli
pakai placeholder; isi di server, jangan commit.

## Lisensi & status

Private / eksperimen. Data real SPG & toko **tidak** boleh tersebar lewat repo ini.