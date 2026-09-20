<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard session-based login untuk area admin.
 *  - Kalau udah login lewat session → boleh lanjut.
 *  - Kalau ada Basic auth yang bener → abaikan (backward compat: script/uitest yang
 *    masih pakai HTTP Basic tetep jalan tanpa toucuh).
 *  - Kalau nggak keduanya → redirect ke /admin/login (kecuali request API/JSON → 401).
 */
class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->session()->get('admin_user');

        if (is_numeric($adminId)) {
            $user = User::find((int) $adminId);
            if ($user) {
                // setUser() — JANGAN loginUsingId(): SessionGuard::login() manggil
                // session()->migrate(true), jadi id session lama dihapus di tiap request.
                // Efeknya request paralel (halaman + fetch kota/toko/karyawan) saling
                // nendang ke /admin/login. setUser cuma nyetel user di guard.
                Auth::setUser($user);
                $request->attributes->set('admin_user', $user);

                return $next($request);
            }
            // User terhapus — buang session basi.
            $request->session()->forget('admin_user');
        }

        if ($this->basicValid($request)) {
            // Backward compat Basic auth: biar RequireRole tetap dapat user,
            // pakai akun admin pertama (kalau ada). Juga tanpa nyentuh session.
            $user = User::where('role', 'admin')->first();
            Auth::setUser($user);
            if ($user) {
                $request->attributes->set('admin_user', $user);
            }

            return $next($request);
        }

        if ($request->is('admin/*') || $request->is('admin')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'login_required'], 401);
            }
            return redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }

    /** Cek apakah Basic auth yang dikirim cocok sama konfigurasi admin. */
    private function basicValid(Request $request): bool
    {
        $user = $request->getUser();
        $pass = $request->getPassword();

        if ($user === null || $pass === null) {
            return false;
        }

        $adminPass = config('faceid.admin_password');
        if ($adminPass === null || $adminPass === '') {
            return false;
        }

        return ($user === 'admin') && ($pass === $adminPass);
    }
}
