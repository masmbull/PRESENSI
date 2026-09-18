<?php

return [

    // ---------- Face ID ----------
    // false = absen cukup pakai lokasi (radius toko) — mode sekarang.
    // true  = wajib kirim face_key hasil verifikasi engine MITO.
    'enabled' => env('FACEID_ENABLED', false),

    // ---------- Geofence ----------
    // Radius absen default dari titik toko (meter). Bisa dioverride per toko
    // lewat kolom stores.radius_m.
    'radius' => env('GEO_RADIUS_DEFAULT', 150),

    // ---------- Engine wajah (ai-service Python) ----------
    // Base URL engine MITO buat /api/face/verify (proxy dari HP).
    'api_base' => env('FACE_API_BASE', 'http://127.0.0.1:8090'),

    // ---------- Keamanan ----------
    // Isi FACEID_API_KEY di .env (mis. "FACEID_API_KEY=rahasia123") untuk
    // ngunci API — klien wajib kirim header X-Api-Key. Kosong = terbuka (dev).
    'admin_password' => env('FACEID_ADMIN_PASSWORD', ''),

    // Email default akun admin (login form). Password-nya pakai FACEID_ADMIN_PASSWORD.
    'admin_email' => env('ADMIN_EMAIL', 'admin@presensi.local'),

    'api_key' => env('FACEID_API_KEY', ''),

    // Jeda minimal antar-catat absen per karyawan per jenis masuk/pulang
    // (detik) — anti dobel-klik.
    'cooldown' => env('ATTENDANCE_COOLDOWN', 60),

];