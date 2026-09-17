<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * SEED DIMATIKAN: data kota/toko/karyawan sekarang diinput dari web
     * (/kelola-wajah). Seeder dibiarkan kosong biar `migrate --seed` di server
     * tidak bikin data dummy/testface123 lagi. Foto contoh tidak dihapus di
     * sini — hapus wajah lama dari tombol "Hapus Semua Wajah Engine".
     */
    public function run(): void
    {
    }
}
