<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminAuth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Regresi: middleware AdminAuth sempat manggil Auth::loginUsingId() di setiap
 * request → SessionGuard migrate(true) → id session lama dihapus. Halaman yang
 * nge-fetch kota/toko/karyawan paralel jadi saling nendang ke /admin/login
 * (daftar toko nyangkut "Memuat data..." + hitungan 0).
 */
class AdminSessionTest extends TestCase
{
    use RefreshDatabase;

    private function loginAdmin(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@presensi.local',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
        ]);

        $this->post('/admin/login', ['email' => 'admin@presensi.local', 'password' => 'admin123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_middleware_admin_gak_ngebuang_id_session(): void
    {
        // Driver 'array' (default tes) gak nyimpen session; pakai database biar kelihatan
        // baris session lama dihapus atau enggak. forgetDrivers() wajib karena manager
        // session sudah ter-cache dengan handler array sebelumnya.
        config(['session.driver' => 'database']);
        $this->app['session']->forgetDrivers();

        $user = User::create([
            'name' => 'Administrator', 'email' => 'admin@uji.local', 'role' => 'admin', 'password' => Hash::make('rahasia123'),
        ]);

        $store = app('session.store');
        $store->setId('sess-uji-'.Str::random(24));
        $store->start();
        $store->put('admin_user', $user->id);
        $store->save();
        $awal = $store->getId();

        $request = Request::create('/admin', 'GET');
        $request->setLaravelSession($store);

        (new AdminAuth())->handle($request, fn () => new Response('ok'));

        // Bug lama: middleware manggil Auth::loginUsingId() → SessionGuard::login() →
        // session()->migrate(true): id session dibuang tiap request, jadi request paralel
        // (halaman + fetch kota/toko/karyawan) saling nendang ke /admin/login.
        $this->assertSame($awal, $store->getId(), 'id session di-migrate ulang saat request admin');
        $this->assertTrue(DB::table('sessions')->where('id', $awal)->exists(), 'baris session lama dihapus');
        $this->assertSame($user->id, $request->attributes->get('admin_user')->id);
    }

    public function test_fetch_paralel_data_master_gak_kena_redirect_login(): void
    {
        $this->loginAdmin();

        // Urutan request seperti halaman /kelola-wajah: halaman dulu, baru data.
        $this->get('/kelola-wajah')->assertOk();

        foreach (['/kelola-wajah/kota', '/kelola-wajah/toko', '/kelola-wajah/karyawan'] as $url) {
            $res = $this->withHeaders(['Accept' => 'application/json'])->get($url);
            $res->assertOk();
            $this->assertStringStartsWith('application/json', (string) $res->headers->get('content-type'), $url.' bukan JSON');
        }
    }

    public function test_basic_auth_masih_jalan_tanpa_ngerusak_session(): void
    {
        User::create([
            'name' => 'Administrator', 'email' => 'basic@uji.local', 'role' => 'admin', 'password' => Hash::make('rahasia123'),
        ]);
        config()->set('faceid.admin_password', 'pass-basic');

        $this->withHeaders(['Authorization' => 'Basic '.base64_encode('admin:pass-basic')])
            ->withHeaders(['Accept' => 'application/json'])
            ->get('/kelola-wajah/kota')
            ->assertOk();
    }
}