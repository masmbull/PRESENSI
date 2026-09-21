<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Toko yang sudah ada bisa diubah: nama, kota, alamat, koordinat, radius. */
class StoreUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::create([
            'name' => 'Administrator', 'email' => 'admin@uji.local', 'role' => 'admin', 'password' => 'rahasia123',
        ]);
        $this->actingAs($admin)->withSession(['admin_user' => $admin->id]);

        return $admin;
    }

    public function test_ubah_toko_termasuk_alamat_dan_koordinat(): void
    {
        $this->admin();
        $bandung = City::create(['name' => 'Bandung']);
        $store = Store::create([
            'city_id' => $bandung->id, 'name' => 'MITO Lama', 'address' => null,
            'lat' => -6.9, 'lon' => 107.6, 'radius_m' => 150,
        ]);
        $jakarta = City::create(['name' => 'Jakarta']);

        $this->postJson('/kelola-wajah/lokasi/'.$store->id, [
            'city_id' => $jakarta->id,
            'store' => 'MITO Baru',
            'address' => 'Jl. Basuki Rahmat No. 12',
            'lat' => -6.0883,
            'lon' => 106.7439,
            'radius_m' => 250,
        ])->assertOk()->assertJsonPath('store.address', 'Jl. Basuki Rahmat No. 12');

        $store->refresh();
        $this->assertSame('MITO Baru', $store->name);
        $this->assertSame($jakarta->id, $store->city_id);
        $this->assertSame('Jl. Basuki Rahmat No. 12', $store->address);
        $this->assertSame(-6.0883, $store->lat);
        $this->assertSame(106.7439, $store->lon);
        $this->assertSame(250.0, (float) $store->radius_m);
    }

    public function test_nama_toko_bentrok_di_kota_sama_ditolak(): void
    {
        $this->admin();
        $city = City::create(['name' => 'Jakarta']);
        Store::create(['city_id' => $city->id, 'name' => 'MITO Sudirman', 'lat' => -6.2, 'lon' => 106.8]);
        $store = Store::create(['city_id' => $city->id, 'name' => 'MITO Kalimalang', 'lat' => -6.2, 'lon' => 106.9]);

        $this->postJson('/kelola-wajah/lokasi/'.$store->id, [
            'city_id' => $city->id, 'store' => 'MITO Sudirman', 'lat' => -6.2, 'lon' => 106.9,
        ])->assertStatus(422);

        $this->assertSame('MITO Kalimalang', $store->fresh()->name);
    }

    public function test_form_ubah_toko_ada_di_halaman(): void
    {
        $this->admin();

        $this->get('/kelola-wajah')->assertOk()
            ->assertSee('id="stBg"', false)
            ->assertSee('id="stAddr"', false)
            ->assertSee('<th>Alamat</th>', false);
    }
}
