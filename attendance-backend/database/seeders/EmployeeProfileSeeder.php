<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Employee;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Contoh profil HRD karyawan SPG (idempotent, aman di-run ulang).
 * Dipasang ke toko yang sudah ada di DB (atau bikin contoh kalau DB kosong).
 * NIK 16 digit unik per baris; nomor lain fiktif tapi formatnya valid.
 */
class EmployeeProfileSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::query()->with('city')->orderBy('id')->get();
        if ($stores->isEmpty()) {
            $city = City::firstOrCreate(['name' => 'Jakarta']);
            $stores = collect([
                Store::create(['city_id' => $city->id, 'name' => 'Toko Contoh Sudirman', 'lat' => -6.2297, 'lon' => 106.8294, 'radius_m' => 150]),
                Store::create(['city_id' => $city->id, 'name' => 'Toko Contoh Kelapa Gading', 'lat' => -6.1618, 'lon' => 106.9080, 'radius_m' => 150]),
            ]);
        }

        // [nama, kode, nik, tempat lahir, tgl lahir, gender, marital, agama, pendidikan,
        //  hp, email, alamat, posisi, dept, status kerja, tgl masuk, akhir kontrak,
        //  nama darurat, hubungan, hp darurat, bank, norek, pemilik rek, bpjs kes, bpjs kerja, catatan]
        $people = [
            ['Ayu Lestari', 'SPG-001', '3273015205910001', 'Bandung', '1991-05-15', 'P', 'Kawin', 'Islam', 'SMA/SMK', '081221345678', 'ayu.lestari@mail.id', 'Jl. Merdeka No. 12 RT 03/07, Bandung', 'SPG', 'Sales', 'Tetap', '2021-03-01', null, 'Dedi Lestari', 'Suami', '081322456789', 'BCA', '8210456789', 'AYU LESTARI', '0001234567890', '1002345678901', 'SPG senior, bisa buka-tutup toko.'],
            ['Rina Marlina', 'SPG-002', '3273021108930002', 'Bandung', '1993-08-11', 'P', 'Kawin', 'Islam', 'SMA/SMK', '081321987654', 'rina.marlina@mail.id', 'Jl. Cikutra No. 45, Bandung', 'SPG', 'Sales', 'Kontrak', '2023-06-01', '2026-05-31', 'Agus Marlina', 'Suami', '081322109876', 'BRI', '00210104567890', 'RINA MARLINA', '0002345678901', '1003456789012', 'Kontrak ke-2, target selalu tercapai.'],
            ['Dewi Anggraini', 'SPG-003', '3273032701950003', 'Jakarta', '1995-01-27', 'P', 'Belum Kawin', 'Islam', 'D3', '081298765432', 'dewi.anggraini@mail.id', 'Jl. Pahlawan No. 8, Bekasi', 'SPG', 'Sales', 'Kontrak', '2024-01-15', '2026-01-14', 'Siti Aminah', 'Ibu', '081387654321', 'Mandiri', '1560012345678', 'DEWI ANGGRAINI', '0003456789012', '1004567890123', null],
            ['Sinta Amelia', 'SPG-004', '3273040512960004', 'Surabaya', '1996-12-05', 'P', 'Belum Kawin', 'Kristen', 'SMA/SMK', '081332145678', 'sinta.amelia@mail.id', 'Jl. Kenanga No. 21, Surabaya', 'SPG', 'Sales', 'Kontrak', '2024-07-01', '2026-06-30', 'Maria Amelia', 'Ibu', '081333246810', 'BNI', '0234567890', 'SINTA AMELIA', '0004567890123', '1005678901234', null],
            ['Putri Wulandari', 'SPG-005', '3273051907970005', 'Semarang', '1997-07-19', 'P', 'Belum Kawin', 'Islam', 'S1', '081326789012', 'putri.wulandari@mail.id', 'Jl. Mawar No. 3, Semarang', 'SPG Leader', 'Sales', 'Tetap', '2020-08-10', null, 'Harto Wulandari', 'Ayah', '081327890123', 'BCA', '8210789012', 'PUTRI WULANDARI', '0005678901234', '1006789012345', 'Leader area, pegang 3 toko.'],
            ['Nina Kurnia', 'SPG-006', '3273060811900006', 'Medan', '1990-11-08', 'P', 'Cerai Hidup', 'Katolik', 'SMA/SMK', '081361234567', 'nina.kurnia@mail.id', 'Jl. Sisingamangaraja No. 77, Medan', 'SPG', 'Sales', 'Harian', '2025-02-01', '2026-01-31', 'Rudi Kurnia', 'Adik', '081362345678', 'BRI', '00210107890123', 'NINA KURNIA', '0006789012345', '1007890123456', 'Harian, standby event.'],
            ['Maya Fitriani', 'SPG-007', '3273072505920007', 'Palembang', '1992-05-25', 'P', 'Kawin', 'Islam', 'SMA/SMK', '081373456789', 'maya.fitriani@mail.id', 'Jl. Basuki Rahmat No. 9, Palembang', 'SPG', 'Sales', 'Kontrak', '2023-09-01', '2026-08-31', 'Joko Fitriani', 'Suami', '081374567890', 'Mandiri', '1560098765432', 'MAYA FITRIANI', '0007890123456', '1008901234567', null],
            ['Fitri Handayani', 'SPG-008', '3273081404940008', 'Makassar', '1994-04-14', 'P', 'Belum Kawin', 'Islam', 'SMA/SMK', '081341567890', 'fitri.handayani@mail.id', 'Jl. AP Pettarani No. 30, Makassar', 'SPG', 'Sales', 'Magang', '2025-06-01', '2025-12-01', 'Hj. Nurlina', 'Ibu', '081342678901', 'BNI', '0234890123', 'FITRI HANDAYANI', '0008901234567', '1009012345678', 'Magang 6 bulan.'],
        ];

        // Profil seed = 8 baris contoh berdiri sendiri (kode seri HRD-101.., NIK unik).
        // Sengaja gak nempel ke karyawan yang sudah ada — data asli (nama, toko,
        // kode SPG-xxx, wajah) tidak boleh ketimpa data contoh. Idempotent: kunci
        // updateOrCreate = NIK, jadi aman di-run ulang tanpa bikin dobel.
        foreach ($people as $i => $p) {
            Employee::updateOrCreate(
                ['nik' => $p[2]],
                [
                    'name' => $p[0],
                    'employee_code' => 'HRD-'.str_pad((string) (101 + $i), 3, '0', STR_PAD_LEFT),
                    'store_id' => $stores[$i % $stores->count()]->id,
                    'active' => true,
                    'nik' => $p[2],
                    'birth_place' => $p[3],
                    'birth_date' => Carbon::parse($p[4])->toDateString(),
                    'gender' => $p[5],
                    'marital_status' => $p[6],
                    'religion' => $p[7],
                    'education' => $p[8],
                    'phone' => $p[9],
                    'email' => $p[10],
                    'address' => $p[11],
                    'position' => $p[12],
                    'department' => $p[13],
                    'employment_status' => $p[14],
                    'join_date' => Carbon::parse($p[15])->toDateString(),
                    'contract_end' => $p[16] ? Carbon::parse($p[16])->toDateString() : null,
                    'resign_date' => null,
                    'emergency_name' => $p[17],
                    'emergency_relation' => $p[18],
                    'emergency_phone' => $p[19],
                    'bank_name' => $p[20],
                    'bank_account' => $p[21],
                    'bank_holder' => $p[22],
                    'bpjs_health' => $p[23],
                    'bpjs_labor' => $p[24],
                    'notes' => trim((string) $p[25].' [data contoh seeder]'),
                ],
            );
        }
    }
}
