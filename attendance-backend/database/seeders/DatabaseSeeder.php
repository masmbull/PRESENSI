<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seeder cuma bikin akun admin (idempotent). Data kota/toko/karyawan
     * diinput dari web (/kelola-wajah), bukan dari seeder.
     *
     * Email default: admin@presensi.local (override via ADMIN_EMAIL di .env)
     * Password     : FACEID_ADMIN_PASSWORD di .env (fallback: admin12345)
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => config('faceid.admin_email', 'admin@presensi.local')],
            [
                'name'     => 'Administrator',
                'role'     => 'admin',
                'password' => Hash::make((string) config('faceid.admin_password') ?: 'admin12345'),
            ],
        );
    }
}
