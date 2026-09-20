<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

/**
 * Saklar fitur yang bisa dinyalakan/dimatikan dari sidebar admin tanpa deploy.
 * Nilai DB (tabel settings) menang atas default di config/faceid.php.
 * ponytail: 1 request = 1x query (cache in-memory), cukup buat trafik sekarang.
 *   Upgrade ke Cache::remember kalau nanti query-nya jadi panas.
 */
class Features
{
    /** key => [label, ket, default dari config] */
    public const DEFS = [
        'faceid' => ['label' => 'Face ID', 'ket' => 'Wajib verifikasi wajah sebelum absen', 'config' => 'faceid.enabled'],
        'geofence' => ['label' => 'Geo location', 'ket' => 'Absen cuma dari dalam radius toko', 'config' => 'faceid.geofence'],
        'cooldown' => ['label' => 'Anti dobel-klik', 'ket' => 'Jeda minimal antar absen jenis sama', 'config' => 'faceid.cooldown_on'],
    ];

    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $rows = [];
        try {
            $rows = Setting::query()->pluck('value', 'key')->all();
        } catch (Throwable $e) {
            // Tabel settings belum ada (migrate belum jalan) — pakai default config.
        }

        $out = [];
        foreach (self::DEFS as $key => $def) {
            $out[$key] = array_key_exists($key, $rows)
                ? filter_var($rows[$key], FILTER_VALIDATE_BOOLEAN)
                : (bool) config($def['config']);
        }

        return self::$cache = $out;
    }

    public static function on(string $key): bool
    {
        return self::all()[$key] ?? false;
    }

    /** Balikin state setelah diubah. */
    public static function set(string $key, bool $on): bool
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $on ? '1' : '0']);
        self::$cache = null;

        return self::on($key);
    }

    /** Dipakai test / setelah migrate — buang cache. */
    public static function flush(): void
    {
        self::$cache = null;
    }

    /** Buat tampilan: label + status + ket. */
    public static function list(): array
    {
        $state = self::all();

        return collect(self::DEFS)->map(fn ($d, $key) => [
            'key' => $key,
            'label' => $d['label'],
            'ket' => $d['ket'],
            'on' => $state[$key],
        ])->values()->all();
    }
}
