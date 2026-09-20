<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Menu khusus admin: kelola akun (admin/manager/supervisor) + keamanan akun
 * sendiri (ganti password).
 *
 * Catatan: /admin/pengguna dikunci middleware role:admin, jadi manager &
 * supervisor tidak bisa lihat/ubah daftar akun. Tapi manager/supervisor tetap
 * boleh ganti password sendiri lewat /admin/akun.
 */
class AdminUserController extends Controller
{
    private const ROLES = ['admin', 'manager', 'supervisor'];

    /** GET /admin/pengguna — daftar akun + form tambah/reset. */
    public function index(): View
    {
        return view('admin.pengguna', [
            'users' => User::query()->orderBy('name')->get(),
            'roles' => self::ROLES,
        ]);
    }

    /** POST /admin/pengguna — tambah akun. Password kosong = generate otomatis. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:80',
            'email' => 'required|email|max:120|unique:users,email',
            'role'  => 'required|in:'.implode(',', self::ROLES),
        ]);

        $password = $request->filled('password')
            ? (string) $request->input('password')
            : Str::password(12);

        $user = User::create([
            'name'     => trim($data['name']),
            'email'    => mb_strtolower(trim($data['email'])),
            'role'     => $data['role'],
            'password' => $password, // cast 'hashed' di model yang nge-hash.
        ]);

        return response()->json([
            'ok' => true,
            'user' => $this->payload($user),
            'password_plain' => $request->filled('password') ? null : $password,
            'message' => 'Akun '.$user->email.' dibuat.'
                .($request->filled('password') ? '' : ' Password sementara: '.$password),
        ], 201);
    }

    /** POST /admin/pengguna/{user} — ubah nama/email/role dan/atau reset password. */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'sometimes|required|string|max:80',
            'email'    => 'sometimes|required|email|max:120|unique:users,email,'.$user->id,
            'role'     => 'sometimes|required|in:'.implode(',', self::ROLES),
            'password' => 'nullable|string|min:6|max:120',
        ]);

        // Safety: jangan biarkan admin nyabut role admin miliknya sendiri
        // (bisa ngunci diri sendiri dari halaman ini).
        if (isset($data['role']) && $data['role'] !== 'admin' && $user->id === Auth::id()) {
            throw ValidationException::withMessages([
                'role' => ['Tidak bisa mengubah role akun sendiri dari admin.'],
            ]);
        }

        if (isset($data['name'])) {
            $user->name = trim($data['name']);
        }
        if (isset($data['email'])) {
            $user->email = mb_strtolower(trim($data['email']));
        }
        if (isset($data['role'])) {
            $user->role = $data['role'];
        }
        if ($request->filled('password')) {
            $user->password = (string) $data['password']; // cast 'hashed' yang nge-hash.
        }

        $user->save();

        return response()->json([
            'ok' => true,
            'user' => $this->payload($user),
            'message' => 'Akun '.$user->email.' diperbarui'
                .($request->filled('password') ? ' + password direset.' : '.'),
        ]);
    }

    /** POST /admin/pengguna/{user}/hapus — hapus akun. */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            return response()->json(['ok' => false, 'message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return response()->json(['ok' => false, 'message' => 'Ini satu-satunya admin — bikin admin lain dulu.'], 422);
        }

        $label = $user->name.' ('.$user->email.')';
        $user->delete();

        return response()->json(['ok' => true, 'message' => 'Akun dihapus: '.$label]);
    }

    /** GET /admin/akun — info akun sendiri + form ganti password. */
    public function account(Request $request): View|RedirectResponse
    {
        $user = $request->attributes->get('admin_user');

        if (! $user instanceof User) {
            return redirect()->route('admin.login');
        }

        return view('admin.akun', ['user' => $user]);
    }

    /** POST /admin/akun/password — ganti password akun sendiri. */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->attributes->get('admin_user');
        if (! $user instanceof User) {
            return response()->json(['ok' => false, 'message' => 'Sesi habis, masuk ulang.'], 401);
        }

        $data = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|max:120|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password sekarang tidak sesuai.'],
            ]);
        }

        $user->password = $data['password']; // cast 'hashed'
        $user->save();

        return response()->json(['ok' => true, 'message' => 'Password akun kamu sudah diganti.']);
    }

    /** Bentuk data user untuk JSON (password jangan pernah ikut). */
    private function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }
}
