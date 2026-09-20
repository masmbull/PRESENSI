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