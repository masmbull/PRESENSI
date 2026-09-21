<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Halaman admin tetap render normal + elemen baru (pagination, zona bahaya, saklar fitur) ada. */
class AdminPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Features::flush();
    }

    public function test_halaman_admin_render_dengan_elemen_baru(): void
    {
        $admin = User::create([
            'name' => 'Administrator', 'email' => 'admin@uji.local', 'role' => 'admin', 'password' => 'rahasia123',
        ]);
        Employee::create(['name' => 'SPG Uji', 'active' => true, 'face_key' => 'face-1']);

        $this->actingAs($admin)->withSession(['admin_user' => $admin->id]);

        // Sidebar admin: saklar fitur + label zona waktu WIB.
        $dash = $this->get('/admin')->assertOk();
        $dash->assertSee('data-feat="faceid"', false)
            ->assertSee('data-feat="geofence"', false)
            ->assertSee('data-feat="cooldown"', false)
            ->assertSee('WIB UTC+7');

        // Kelola wajah: pagination toko/karyawan/belum-wajah + hapus 1 wajah.
        $wajah = $this->get('/kelola-wajah')->assertOk();
        foreach (['delFaceEmp', 'btnHapusSatu', 'delAllConfirm', 'storePrev', 'storeNext', 'empPrev', 'empNext', 'noFacePrev', 'noFaceNext'] as $id) {
            $wajah->assertSee('id="'.$id.'"', false);
        }

        // Keamanan akun tetap jalan buat manager/supervisor.
        $this->get('/admin/pengguna')->assertOk()->assertSee('Kelola Akun');

        // Halaman data karyawan (profil HRD lengkap): render + kontrol kuncinya ada.
        // Tabelnya client-side dari /admin/karyawan/data, jadi HTML-nya cukup cek kerangka.
        $kar = $this->get('/admin/karyawan')->assertOk();
        foreach (['btnAdd', 'btnExport', 'fQ', 'fStore', 'fStat', 'fAkt', 'tb'] as $id) {
            $kar->assertSee('id="'.$id.'"', false);
        }
        $kar->assertSee("const API = '".url('admin/karyawan')."'", false);
    }

    public function test_data_karyawan_ditampilkan_di_halaman_dan_endpoint_json(): void
    {
        $admin = User::create([
            'name' => 'Administrator', 'email' => 'admin@uji.local', 'role' => 'admin', 'password' => 'rahasia123',
        ]);

        $city = \App\Models\City::create(['name' => 'Jakarta']);
        $store = \App\Models\Store::create(['city_id' => $city->id, 'name' => 'TOKO UJI', 'lat' => -6.2, 'lon' => 106.8, 'radius_m' => 100]);
        Employee::create([
            'name' => 'Rina Uji', 'employee_code' => 'SPG-900', 'store_id' => $store->id, 'active' => true,
            'nik' => '3271010101900001', 'phone' => '081200000001', 'position' => 'SPG', 'gender' => 'P',
            'birth_date' => '1990-01-01', 'birth_place' => 'Jakarta',
        ]);

        $this->actingAs($admin)->withSession(['admin_user' => $admin->id]);

        // Endpoint data tabel: field lengkap + relasi toko/kota ikut.
        $res = $this->getJson('/admin/karyawan/data');
        $res->assertOk()->assertJsonPath('count', 1)
            ->assertJsonPath('rows.0.name', 'Rina Uji')
            ->assertJsonPath('rows.0.nik', '3271010101900001')
            ->assertJsonPath('rows.0.position', 'SPG')
            ->assertJsonPath('rows.0.store_name', 'TOKO UJI')
            ->assertJsonPath('rows.0.city_name', 'Jakarta');

        // Endpoint detail 1 karyawan buat modal profil.
        $this->getJson('/admin/karyawan/'.Employee::where('name', 'Rina Uji')->value('id'))
            ->assertOk()
            ->assertJsonPath('employee.name', 'Rina Uji')
            ->assertJsonPath('employee.nik', '3271010101900001')
            ->assertJsonPath('employee.photo_url', null);
    }

    public function test_manager_gak_lihat_saklar_fitur(): void
    {
        $manager = User::create([
            'name' => 'Manager', 'email' => 'manager@uji.local', 'role' => 'manager', 'password' => 'rahasia123',
        ]);
        $this->actingAs($manager)->withSession(['admin_user' => $manager->id]);

        $this->get('/admin')->assertOk()->assertDontSee('data-feat="faceid"', false);
        $this->get('/admin/pengguna')->assertStatus(403);
    }
}