<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC: cek role user terhadap list role yang diizinkan.
 * Dipakai setelah AdminAuth (soal session-nya udah aman).
 * Role: admin > manager > supervisor.
 */
class RequireRole
{
    /** @param string|string[] $roles */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->attributes->get('admin_user');

        if (! $user instanceof User) {
            return redirect()->route('admin.login');
        }

        if (! $user->hasRole(...$roles)) {
            abort(403, 'Anda tidak punya akses untuk halaman ini.');
        }

        return $next($request);
    }
}
