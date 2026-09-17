<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Store;
use Illuminate\Console\Command;

class ImportStores extends Command
{
    protected $signature = 'stores:import {--file= : path file JSON toko (default ../stores.json)}';

    protected $description = 'Impor/upsert toko dari stores.json. Toko tanpa GPS (lat/lon) dilewati dan dilaporkan.';

    public function handle(): int
    {
        $path = $this->option('file') ?: base_path('../stores.json');

        if (! is_string($path) || ! is_file($path)) {
            $this->error("File JSON tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $rows = json_decode((string) file_get_contents($path), true);
        if (! is_array($rows)) {
            $this->error('Isi file JSON tidak valid.');

            return self::FAILURE;
        }

        $inserted = 0;
        $updated = 0;
        $skipped = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['store'] ?? ''));
            if ($name === '') {
                continue;
            }

            $lat = $row['lat'] ?? null;
            $lon = $row['lon'] ?? null;

            if (! is_numeric($lat) || ! is_numeric($lon)
                || (float) $lat < -90 || (float) $lat > 90
                || (float) $lon < -180 || (float) $lon > 180) {
                $skipped[] = $name;

                continue;
            }

            $address = trim((string) ($row['address'] ?? ''));
            $city = $this->city($name, $address);

            $data = [
                'city_id' => $city->id,
                'name' => $name,
                'lat' => (float) $lat,
                'lon' => (float) $lon,
                'address' => $address !== '' ? $address : null,
            ];

            $store = Store::whereRaw('lower(name) = ?', [mb_strtolower($name)])->first();

            if ($store === null) {
                Store::create($data);
                $inserted++;
            } else {
                $store->update($data);
                $updated++;
            }
        }

        $this->info(sprintf('Selesai: %d toko baru, %d toko di-update.', $inserted, $updated));
        $this->info('Total toko di DB: '.Store::count());

        if ($skipped !== []) {
            $this->warn(sprintf('%d toko tanpa GPS dilewati (isi lat/lon di stores.json dulu):', count($skipped)));
            foreach ($skipped as $s) {
                $this->line('  - '.$s);
            }
        }

        return self::SUCCESS;
    }

    private function city(string $storeName, string $address): City
    {
        $label = $this->cityFromAddress($address) ?? $this->cityFromStoreName($storeName) ?? 'Lainnya';

        return City::firstOrCreate(['name' => $label]);
    }

    private function cityFromAddress(string $address): ?string
    {
        $parts = array_map('trim', explode(',', $address));
        $parts = array_values(array_filter(
            $parts,
            fn (string $p): bool => $p !== ''
                && mb_strtolower($p) !== 'indonesia'
                && ! preg_match('/^\d{4,5}$/', $p)
        ));

        if (count($parts) < 2) {
            return null;
        }

        // Segment sebelum provinsi = kota/kabupaten ("..., Bandung, Jawa Barat, ...").
        return $this->normalizeCity($parts[count($parts) - 2]);
    }

    private function cityFromStoreName(string $name): ?string
    {
        $known = [
            'balikpapan', 'bandung', 'surabaya', 'kediri', 'banjarmasin', 'banjarbaru',
            'banyuwangi', 'bekasi', 'jember', 'kudus', 'malang', 'makassar', 'medan',
            'palembang', 'palu', 'manado', 'klaten', 'jakarta', 'tangerang', 'mataram',
            'lombok', 'denpasar', 'bontang', 'martapura', 'palangkaraya', 'pangkalanbun',
            'tanah grogot', 'jogja', 'yogyakarta', 'purwokerto', 'sidoarjo', 'pekanbaru',
            'padang', 'jambi', 'pontianak', 'samarinda', 'tenggarong',
        ];

        foreach ($known as $key) {
            if (preg_match('/'.preg_quote($key, '/').'/i', $name)) {
                return $this->normalizeCity(ucwords($key));
            }
        }

        return null;
    }

    private function normalizeCity(string $label): string
    {
        // Buang prefix "Kota "/"Kabupaten " biar dropdown konsisten ("Bandung" bukan "Kota Bandung").
        $clean = preg_replace('/^kota\s+|^kabupaten\s+/i', '', $label);
        $clean = trim((string) $clean);

        return $clean !== '' ? $clean : 'Lainnya';
    }
}