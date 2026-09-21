<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Employee;
use App\Models\Store;
use App\Models\User;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Nonaktifkan karyawan (resign) + hapus toko kosong. */
class EmployeeActiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Features::flush();
        // Uji gate nonaktif tanpa keburu kena gate face id / geofence / cooldown.
        Features::set('faceid', false);
        Features::set('geofence', false);
        Features::set('cooldown', false);
    }

    private function admin(): void
    {
        $admin = User::create([
            'name' => 'Administrator', 'email' => 'admin@uji.local', 'role' => 'admin', 'password' => 'rahasia123',
        ]);
        $this->actingAs($admin)->withSession(['admin_user' => $admin->id]);
    }

    /** X-Api-Key cuma kalau server memang dikunci. */
    private function apiHeaders(): array
    {
        $key = trim((string) config('faceid.api_key'));

        return $key === '' ? [] : ['X-Api-Key' => $key];
    }

    private function store(): Store
    {
        $city = City::create(['name' => 'Jakarta']);

        return Store::create(['city_id' => $city->id, 'name' => 'Toko Uji', 'lat' => -6.2, 'lon' => 106.8166, 'radius_m' => 150]);
    }

    public function test_nonaktif_karyawan_hilang_dari_dropdown_dan_gak_bisa_absen(): void
    {
        $this->admin();
        $store = $this->store();
        $employee = Employee::create(['name' => 'SPG Uji', 'active' => true, 'store_id' => $store->id]);

        $this->postJson('/kelola-wajah/karyawan/'.$employee->id.'/aktif', ['active' => false])->assertOk();
        $this->assertFalse($employee->fresh()->active);

        // Gak muncul di dropdown halaman absen SPG.
        $this->withHeaders($this->apiHeaders())->getJson('/api/employees?store_id='.$store->id)
            ->assertOk()->assertJsonCount(0);

        // Gak bisa absen walau ID-nya masih dikirim.
        $this->withHeaders($this->apiHeaders())->postJson('/api/attendances', [
            'employee_id' => $employee->id, 'type' => 'masuk', 'lat' => -6.2, 'lon' => 106.8166, 'acc' => 10,
        ])->assertStatus(422)->assertJsonPath('ok', false);

        // Aktifkan lagi → muncul lagi & bisa absen.
        $this->postJson('/kelola-wajah/karyawan/'.$employee->id.'/aktif', ['active' => true])->assertOk();
        $this->withHeaders($this->apiHeaders())->getJson('/api/employees?store_id='.$store->id)
            ->assertOk()->assertJsonCount(1);

        $this->withHeaders($this->apiHeaders())->postJson('/api/attendances', [
            'employee_id' => $employee->id, 'type' => 'masuk', 'lat' => -6.2, 'lon' => 106.8166, 'acc' => 10,
        ])->assertStatus(201)->assertJsonPath('logged', true);
    }

    public function test_daftar_ulang_nama_yang_nonaktif_otomatis_diaktifkan(): void
    {
        $this->admin();
        $store = $this->store();
        Employee::create(['name' => 'SPG Uji', 'active' => false, 'store_id' => $store->id]);

        $this->postJson('/kelola-wajah/karyawan', ['name' => 'spg uji', 'store_id' => $store->id])
            ->assertOk()
            ->assertJsonPath('reactivated', true);

        $this->assertTrue(Employee::where('name', 'SPG Uji')->first()->active);
        $this->assertSame(1, Employee::count(), 'gak boleh bikin baris dobel');
    }

    public function test_hapus_toko_ditolak_kalau_masih_ada_karyawan(): void
    {
        $this->admin();
        $store = $this->store();
        Employee::create(['name' => 'SPG Uji', 'store_id' => $store->id]);

        $this->postJson('/kelola-wajah/toko/'.$store->id.'/hapus')
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->assertDatabaseHas('stores', ['id' => $store->id]);
    }

    public function test_hapus_toko_kosong_berhasil(): void
    {
        $this->admin();
        $store = $this->store();

        $this->postJson('/kelola-wajah/toko/'.$store->id.'/hapus')
            ->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseMissing('stores', ['id' => $store->id]);
    }

    public function test_kontrol_aktif_dan_hapus_toko_ada_di_halaman(): void
    {
        $this->admin();

        $this->get('/kelola-wajah')->assertOk()
            ->assertSee('id="fltActive"', false)
            ->assertSee('data-toggleemp', false)
            ->assertSee('data-delstore', false);
    }
}
