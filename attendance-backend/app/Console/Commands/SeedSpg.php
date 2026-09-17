<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Console\Command;

class SeedSpg extends Command
{
    protected $signature = 'spg:seed {--force : hapus semua karyawan existing dulu, lalu bikin ulang}';

    protected $description = 'Seed SPG dummy (nama + employee_code SPG-XXX) untuk semua toko. Buat tes alur kota→toko→SPG→absen.';

    /** Nama dummy — sama dengan data dummy awal yang pernah di-seed. */
    private const NAMES = [
        'Rina Melati', 'Dewi Anggraini', 'Putri Lestari', 'Ayu Kartika',
        'Sari Wulandari', 'Mega Puspita', 'Lina Marlina', 'Fitri Handayani',
        'Ratna Kumala', 'Yuni Astuti', 'Novi Rahayu', 'Eka Putri',
        'Wulan Sari', 'Diah Permata', 'Intan Melati', 'Rosa Amelia',
        'Sri Mulyani', 'Tia Kartini', 'Nadia Salma', 'Vina Oktaviani',
        'Citra Kirana',
    ];

    public function handle(): int
    {
        if ($this->option('force')) {
            $del = Employee::query()->delete();
            $this->warn("Karyawan existing dihapus: {$del}");
        }

        $stores = Store::query()->orderBy('id')->get();
        $created = 0;
        $skipped = 0;
        $code = 0;

        foreach ($stores as $store) {
            // 2 SPG/toko, toko pertama 3 (mirip data dummy awal).
            $count = $store->id === 1 ? 3 : 2;

            for ($k = 0; $k < $count; $k++) {
                $code++;
                $employeeCode = sprintf('SPG-%03d', $code);

                if (Employee::where('employee_code', $employeeCode)->exists()) {
                    $skipped++;

                    continue;
                }

                Employee::create([
                    'name' => self::NAMES[$code % count(self::NAMES)],
                    'employee_code' => $employeeCode,
                    'store_id' => $store->id,
                    'active' => true,
                ]);
                $created++;
            }
        }

        $this->info(sprintf('Selesai: %d karyawan baru, %d sudah ada (skip). Karyawan total: %d.', $created, $skipped, Employee::count()));

        return self::SUCCESS;
    }
}