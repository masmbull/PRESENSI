<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FaceAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $password = (string) config('faceid.admin_password');
        if ($password === '') {
            abort(503, 'Pengelolaan wajah belum aktif. Isi FACEID_ADMIN_PASSWORD di .env server.');
        }

        if ($request->getUser() !== 'admin' || ! hash_equals($password, (string) $request->getPassword())) {
            return response('Masuk menggunakan username admin dan password pengelolaan wajah.', 401)
                ->header('WWW-Authenticate', 'Basic realm="Kelola Wajah", charset="UTF-8"');
        }

        return $next($request)->header('Cache-Control', 'no-store, private');
    }
}
