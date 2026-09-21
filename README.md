<div align="center">

# 🛰️ PRESENSI

**Absensi SPG seluruh Indonesia — berbasis GPS geofence + verifikasi wajah AI**

[![CI](https://img.shields.io/badge/CI-GitHub_Actions-2088ff?logo=githubactions&logoColor=white)](.github/workflows/ci.yml)
![Backend](https://img.shields.io/badge/backend-Laravel_13-ff2d20?logo=laravel&logoColor=white)
![Engine](https://img.shields.io/badge/engine-Python_·_ONNX-3776ab?logo=python&logoColor=white)
![DB](https://img.shields.io/badge/db-SQLite-003b57?logo=sqlite&logoColor=white)

*Face-in. Geo-fenced. Anti-bolos.*

</div>

---

> ⚠️ **Repo public — keamanan data.** Kredensial, sertifikat, data biometrik & absensi,
> `.env` asli, domain dan IP produksi **tidak pernah di-commit**. Semua file config di repo
> ini cuma berisi **placeholder**; nilai asli diisi langsung di server, jangan pernah masuk git.

## 🤔 Apa ini?

Sales Promotion Girl (SPG) tersebar di banyak toko lintas kota. Masalah klasiknya:
**absen dari rumah, titip absen, dan absen kertas yang gak bisa diaudit.**

PRESENSI menyelesaikannya dengan dua lapis verifikasi:

1. **📍 Geofence GPS** — absen hanya bisa diproses kalau titik GPS SPG berada
   di dalam radius toko (dihitung *server-side* dengan haversine, bukan dipercaya dari klien).
2. **🧠 Face ID (opsional)** — selfie diverifikasi ke embedding wajah yang terdaftar
   lewat engine ONNX (SCRFD + ArcFace + anti-spoofing liveness).

## 🏗️ Arsitektur

```
   ┌──────────────┐   HTTPS    ┌────────────────────┐  localhost:8090   ┌─────────────────┐
   │   📱 HP SPG  │ ─────────► │  ⚙️ Laravel API     │ ────────────────► │  🧠 MITO Engine  │
   │  (mobile)    │            │  geofence · DB ·    │   JSON only,      │  ONNX inference │
   └──────────────┘            │  admin web          │   gak di-expose   │  SCRFD·ArcFace  │
                               └────────────────────┘                   └─────────────────┘
   ┌──────────────┐   HTTPS          ▲
   │ 💻 Admin web │ ─────────────────┘
   │  kota·toko·  │                        ┌──────────────┐
   │  karyawan·   │                        │ 🗃️ SQLite     │
   │  wajah       │                        │ + foto profil │
   └──────────────┘                        └──────────────┘
```

## ✨ Fitur

| | Fitur | Detail |
|---|---|---|
| 🗺️ | **Absen masuk / pulang** | Validasi GPS server-side, radius per toko bisa diatur |
| 🧠 | **Verifikasi wajah opsional** | Bisa dimatikan via saklar admin tanpa deploy |
| 🏪 | **Master data** | Kota · toko (nama, alamat, koordinat, radius) — tambah/ubah/hapus |
| 🪪 | **Data karyawan ala HRD** | NIK, TTL, kontak, kepegawaian, kontrak, kontak darurat, bank, BPJS, foto |
| 🕐 | **Resign yang aman** | Karyawan nonaktif hilang dari absen, tapi riwayat & wajahnya utuh |
| 🔀 | **Saklar fitur live** | Face ID / Geo / anti dobel-klik — on/off langsung dari sidebar |
| 🛡️ | **Perlindungan data** | Karyawan/toko berisi riwayat absen tidak bisa dihapus sembarangan |
| 🕐 | **WIB konsisten** | Semua jam presensi `Asia/Jakarta (UTC+7)` |
| 📱 | **Mobile-first** | Peta Leaflet dark, flow 3 langkah: Kota → Toko → Nama |

## 📁 Struktur

```
├── attendance-backend/   Laravel — web absensi + API + admin (README sendiri)
├── mito/                 Engine face AI: detector, embedding, liveness, attributes
├── models_pro/           Model ONNX (~52 MB) — ikut repo biar clone langsung jalan
├── static/               Playground lama (face-api.js, jalan di browser)
├── deployment/           Panduan & skrip deploy produksi (placeholder-only)
├── stores.json           Master toko (nama, kota, lat/lon) untuk import
├── DEVELOPMENT.md        📘 Mulai di sini kalau mau develop — arsitektur & konvensi
└── .github/workflows/    CI: PHPUnit (PHP 8.4) + cek modul engine Python
```

## 🚀 Mulai Cepat

**Prasyarat:** PHP ≥ 8.4 + Composer · Python 3.12+ · Node (opsional, util verifikasi)

**1️⃣ AI engine** (folder root repo):

```bash
python -m venv .venv
source .venv/bin/activate        # Windows: .venv\Scripts\activate
pip install -r requirements.txt
python restart.py                # http :8090 + https :8443
python smoke_pro.py              # smoke test — harus semua 200
```

**2️⃣ Backend absensi** (`attendance-backend/`):

```bash
cp .env.example .env             # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve                # http :8000 (alternatif: run-backend.bat, http+https)
```

**3️⃣ Data dummy** (dari `attendance-backend/`):

```bash
php artisan stores:import        # import master toko dari stores.json
php artisan spg:seed             # SPG dummy per toko
php artisan db:seed --class=Database\\Seeders\\EmployeeProfileSeeder
```

> 🔒 Seeder & import gak menampilkan data orang nyata di repo — hasilnya file DB
> lokal yang **tidak di-commit**. Seeder profil HRD bersifat idempotent (aman diulang),
> dan di README ini cuma perintahnya yang dicantumkan, bukan isinya.

## 🧪 Testing

```bash
cd attendance-backend
php artisan test
```

CI otomatis jalan di setiap push & PR ke `main` — job **backend** (PHPUnit di PHP 8.4)
dan job **engine** (cek import semua modul Python).

## 🚢 Deploy

Panduan lengkap VPS + Cloudflare ada di **`deployment/README.md`** — semua nilai
asli pakai placeholder, diisi di server.

## 🗺️ Dokumentasi

| Dokumen | Isi |
|---|---|
| [`DEVELOPMENT.md`](DEVELOPMENT.md) | 📘 **Mulai di sini** — arsitektur, konvensi, cara test, backlog |
| [`attendance-backend/README.md`](attendance-backend/README.md) | API, config, struktur backend |
| [`deployment/README.md`](deployment/README.md) | Deploy produksi langkah demi langkah |

---

<div align="center">

**Private / eksperimen** — data SPG & toko nyata tidak boleh tersebar lewat repo ini.

Made with ☕ dan 🛰️ GPS

</div>
