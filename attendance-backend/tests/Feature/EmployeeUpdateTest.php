<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Employee;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Karyawan yang sudah ada bisa diubah: nama, kode, pindah toko (wajah tetap nempel). */
class EmployeeUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): void
    {
        $admin = User::create([
            'name' => 'Administrator', 'email' => 'admin@uji.local', 'role' => 'admin', 'password' => 'rahasia123',
        ]);
        $this->actingAs($admin)->withSession(['admin_user' => $admin->id]);
    }

    public function test_ubah_nama_kode_dan_pindah_toko_wajah_tetap(): void
    {
        $this->admin();
        $city = City::create(['name' => 'Jakarta']);
        $lama = Store::create(['city_id' => $city->id, 'name' => 'MITO Kalimalang', 'lat' => -6.2, 'lon' => 106.9]);
        $baru = Store::create(['city_id' => $city->id, 'name' => 'MITO Sudirman', 'lat' => -6.2, 'lon' => 106.8]);
        $employee = Employee::create([
            'name' => 'Devi Agrully', 'employee_code' => 'SPG-001', 'face_key' => 'face-abc', 'store_id' => $lama->id,
        ]);

        $this->postJson('/kelola-wajah/karyawan/'.$employee->id, [
            'name' => 'Devi Isvaradilla Agrully',
            'employee_code' => 'SPG-002',
            'store_id' => $baru->id,
        ])->assertOk()->assertJsonPath('employee.store_id', $baru->id);

        $employee->refresh();
        $this->assertSame('Devi Isvaradilla Agrully', $employee->name);
        $this->assertSame('SPG-002', $employee->employee_code);
        $this->assertSame($baru->id, $employee->store_id);
        $this->assertSame('face-abc', $employee->face_key, 'wajah yang sudah didaftarkan gak boleh lepas');
    }

    public function test_nama_kembar_di_toko_sama_ditolak(): void
    {
        $this->admin();
        $city = City::create(['name' => 'Jakarta']);
        $store = Store::create(['city_id' => $city->id, 'name' => 'MITO Kalimalang', 'lat' => -6.2, 'lon' => 106.9]);
        Employee::create(['name' => 'Devi', 'store_id' => $store->id]);
        $lain = Employee::create(['name' => 'Putri', 'store_id' => $store->id]);

        $this->postJson('/kelola-wajah/karyawan/'.$lain->id, [
            'name' => 'Devi', 'store_id' => $store->id,
        ])->assertStatus(422);

        $this->assertSame('Putri', $lain->fresh()->name);
    }

    public function test_kode_karyawan_bentrok_ditolak(): void
    {
        $this->admin();
        Employee::create(['name' => 'Devi', 'employee_code' => 'SPG-001']);
        $lain = Employee::create(['name' => 'Putri', 'employee_code' => 'SPG-002']);

        $this->postJson('/kelola-wajah/karyawan/'.$lain->id, [
            'name' => 'Putri', 'employee_code' => 'SPG-001',
        ])->assertStatus(422);

        $this->assertSame('SPG-002', $lain->fresh()->employee_code);
    }

    public function test_form_cari_toko_dan_ubah_karyawan_ada_di_halaman(): void
    {
        $this->admin();

        $this->get('/kelola-wajah')->assertOk()
            ->assertSee('id="storeSearch"', false)
            ->assertSee('id="emBg"', false)
            ->assertSee('data-editemp', false)
            ->assertSee('data-editstore', false);
    }
}
