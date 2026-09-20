<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\Store;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Saklar fitur sidebar admin + zona waktu WIB. */
class FeatureToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Cache statis Features kebawa antar-test di proses yang sama.
        Features::flush();
    }

    public function test_timezone_wib_utc_plus_7(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('+07:00', now()->format('P'));
    }

    public function test_toggle_fitur_tersimpan_di_db_dan_ikut_healthz(): void
    {
        $this->assertFalse(Features::set('geofence', false));
        $this->assertSame('0', Setting::query()->find('geofence')->value);

        $res = $this->withHeaders($this->apiHeaders())->getJson('/api/healthz');
        $res->assertOk()->assertJsonPath('geofence', false)->assertJsonPath('timezone', 'Asia/Jakarta');

        $this->assertTrue(Features::set('geofence', true));
        $this->assertTrue(Features::on('geofence'));
    }

    public function test_geofence_off_ngejar_absen_luar_radius(): void
    {
        // Kondisi bersih: face id & cooldown mati, cuma geofence yang diuji.
        Features::set('faceid', false);
        Features::set('cooldown', false);
        Features::set('geofence', true);

        $city = City::create(['name' => 'Jakarta']);
        $store = Store::create([
            'city_id' => $city->id, 'name' => 'Toko Uji',
            'lat' => -6.2, 'lon' => 106.8166, 'radius_m' => 150,
        ]);
        $employee = Employee::create(['name' => 'SPG Uji', 'active' => true, 'store_id' => $store->id]);

        $far = ['employee_id' => $employee->id, 'type' => 'masuk', 'lat' => -6.3, 'lon' => 106.9, 'acc' => 10];

        $this->withHeaders($this->apiHeaders())->postJson('/api/attendances', $far)
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        Features::set('geofence', false);

        $this->withHeaders($this->apiHeaders())->postJson('/api/attendances', $far)
            ->assertStatus(201)
            ->assertJsonPath('logged', true);
    }

    /** X-Api-Key cuma kalau server memang dikunci. */
    private function apiHeaders(): array
    {
        $key = trim((string) config('faceid.api_key'));

        return $key === '' ? [] : ['X-Api-Key' => $key];
    }
}