<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Employee;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaceManagementController extends Controller
{
    public function index()
    {
        $stats = [
            'cities' => City::query()->count(),
            'stores' => Store::query()->count(),
            'employees' => Employee::query()->count(),
            'linked' => Employee::query()->whereNotNull('face_key')->count(),
            'no_face' => Employee::query()->whereNull('face_key')->count(),
        ];

        return view('wajah', ['stats' => $stats]);
    }

    /** POST /kelola-wajah/lokasi — tambah kota + toko sekaligus. */
    public function location(Request $request): JsonResponse
    {
        $data = $request->validate([
            'city' => 'required|string|max:80',
            'store' => 'required|string|max:120',
            'address' => 'nullable|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'radius_m' => 'nullable|integer|min:10|max:5000',
        ]);

        $city = City::firstOrCreate(['name' => trim($data['city'])]);

        $store = Store::updateOrCreate(
            ['city_id' => $city->id, 'name' => trim($data['store'])],
            [
                'address' => $data['address'] ?? null,
                'lat' => $data['lat'],
                'lon' => $data['lon'],
                'radius_m' => $data['radius_m'] ?? (int) config('faceid.radius', 150),
            ]
        );

        return response()->json(['ok' => true, 'city' => $city, 'store' => $store->load('city')], 201);
    }

    /** GET /kelola-wajah/toko — daftar toko buat dropdown (tanpa API key). */
    public function stores(): JsonResponse
    {
        return response()->json(
            Store::query()->with('city:id,name')->withCount('employees')->orderBy('name')->get()
        );
    }

    /** GET /kelola-wajah/kota — daftar kota buat dropdown. */
    public function cities(): JsonResponse
    {
        return response()->json(
            City::query()->withCount('stores')->orderBy('name')->get(['id', 'name'])
        );
    }

    /** GET /kelola-wajah/karyawan — daftar karyawan + toko + kota (tanpa API key). */
    public function employees(): JsonResponse
    {
        return response()->json(
            Employee::query()
                ->with('store.city:id,name')
                ->orderBy('store_id')
                ->orderBy('name')
                ->get(['id', 'name', 'employee_code', 'face_key', 'store_id'])
        );
    }

    /** POST /kelola-wajah/karyawan — buat karyawan baru (dedupe per toko+nama). */
    public function createEmployee(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'employee_code' => 'nullable|string|max:40|unique:employees,employee_code',
            'store_id' => 'nullable|integer|exists:stores,id',
        ]);

        $name = trim($data['name']);

        // dedupe: nama yang sama di toko yang sama → jangan bikin baris baru
        $existing = Employee::where('store_id', $data['store_id'] ?? null)
            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->first();
        if ($existing !== null) {
            return response()->json([
                'ok' => true,
                'employee' => $existing->load('store.city'),
                'existing' => true,
                'message' => 'Karyawan ini sudah terdaftar di toko tersebut.',
            ]);
        }

        $employee = Employee::create([
            'name' => $name,
            'employee_code' => $data['employee_code'] ?? null,
            'active' => true,
            'store_id' => $data['store_id'] ?? null,
        ]);

        return response()->json(['ok' => true, 'employee' => $employee->load('store.city')], 201);
    }

    /**
     * POST /kelola-wajah/daftar — kirim foto ke engine untuk karyawan yang sudah ada.
     * Input: employee_id, photo (file) atau image (base64), store_id (opsional override).
     */
    public function enroll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'image' => 'nullable|string|max:12000000',
            'photo' => 'nullable|image|max:8192',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $bytes = $this->imageBytes($request, $data);
        if ($bytes === null) {
            return response()->json(['ok' => false, 'message' => 'Kirim foto lewat input "photo" atau kolom "image" base64.'], 422);
        }

        $engine = $this->engineEnroll(
            // nama unik di engine: "nama — toko" (engine dedupe by nama; dua karyawan
            // dengan nama sama di toko beda harus punya slot wajah masing-masing)
            trim($employee->name.' — '.($employee->store?->name ?? 'Tanpa Toko')),
            $bytes
        );
        if (! ($engine['ok'] ?? false)) return response()->json($engine, $engine['status'] ?? 502);

        $face = $engine['body'];
        if (! ($face['ok'] ?? false)) {
            $err = $face['error'] ?? 'unknown';
            return response()->json([
                'ok' => false,
                'message' => $err === 'no_face_detected'
                    ? ($face['message'] ?? 'Wajah tidak terdeteksi.')
                    : ($face['message'] ?? 'Engine menolak: '.$err),
                'engine' => $face,
            ], 422);
        }

        $conflict = Employee::where('face_key', $face['id'])->where('id', '!=', $employee->id)->first();
        if ($conflict) return response()->json(['ok' => false, 'message' => 'Wajah ini sudah dipakai '.$conflict->name.'.'], 409);

        $employee->update(['face_key' => $face['id']]);

        return response()->json(['ok' => true, 'employee' => $employee->fresh()->load('store'), 'engine' => $face], 201);
    }

    /** POST /kelola-wajah/hapus — hapus SEMUA wajah engine + lepas face_key. */
    public function clear(Request $request): JsonResponse
    {
        $request->validate(['confirm' => 'required|string|in:HAPUS SEMUA WAJAH']);

        try {
            $faces = Http::timeout(30)->get(rtrim((string) config('faceid.api_base'), '/').'/api/pro/faces');
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Engine wajah tidak bisa dihubungi.'], 503);
        }

        if ($faces->failed()) return response()->json(['ok' => false, 'message' => 'Gagal baca daftar wajah (HTTP '.$faces->status().').'], 502);

        $deleted = 0; $failed = [];
        foreach ($faces->json() ?? [] as $face) {
            $id = $face['id'] ?? null;
            if (! $id) continue;
            try {
                $res = Http::timeout(30)->delete(rtrim((string) config('faceid.api_base'), '/').'/api/pro/faces/'.urlencode($id));
                $res->successful() ? $deleted++ : $failed[] = $id;
            } catch (\Throwable $e) { $failed[] = $id; }
        }

        Employee::query()->whereNotNull('face_key')->update(['face_key' => null]);

        return response()->json([
            'ok' => empty($failed),
            'deleted' => $deleted, 'failed' => $failed,
            'message' => empty($failed)
                ? "Semua wajah engine dihapus ($deleted). Karyawan dan absen tetap ada."
                : 'Sebagian gagal dihapus, face_key sudah dilepas.',
        ]);
    }

    private function imageBytes(Request $request, array $data): ?string
    {
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $bytes = (string) file_get_contents($request->file('photo')->getRealPath());
            return $this->validImage($bytes) ? $bytes : null;
        }
        if (! empty($data['image'])) {
            $raw = trim($data['image']);
            if (str_contains($raw, ',')) {
                $parts = explode(',', $raw, 2);
                if (! str_starts_with(strtolower($parts[0]), 'data:image')) return null;
                $raw = $parts[1];
            }
            $bytes = base64_decode($raw, true);
            if ($bytes === false) return null;
            return $this->validImage($bytes) ? $bytes : null;
        }
        return null;
    }

    private function validImage(string $bytes): bool
    {
        if (strlen($bytes) < 500 || strlen($bytes) > 8 * 1024 * 1024) return false;
        $info = @getimagesizefromstring($bytes);
        return $info !== false && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true);
    }

    private function engineEnroll(string $name, string $bytes): array
    {
        set_time_limit(180);
        $base = rtrim((string) config('faceid.api_base'), '/');
        try { Http::timeout(25)->get($base.'/api/pro/config'); }
        catch (\Throwable $e) { Log::warning('face enroll: warmup gagal', ['err' => $e->getMessage()]); }

        try {
            $res = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
                ->connectTimeout(10)->timeout(170)
                ->send('POST', $base.'/api/pro/enroll?name='.urlencode(trim($name)), ['body' => $bytes]);
        } catch (\Throwable $e) {
            Log::error('face enroll: engine tidak bisa dihubungi', ['err' => $e->getMessage()]);
            return ['ok' => false, 'status' => 503, 'message' => 'Engine wajah tidak bisa dihubungi.'];
        }
        if ($res->status() === 422) return ['ok' => false, 'status' => 422, 'message' => 'Engine menolak foto ini.', 'engine' => $res->json()];
        if ($res->failed()) {
            Log::error('face enroll: engine HTTP gagal', ['status' => $res->status(), 'body' => substr((string) $res->body(), 0, 1000)]);
            return ['ok' => false, 'status' => 502, 'message' => 'Engine wajah error (HTTP '.$res->status().').'];
        }
        return ['ok' => true, 'body' => $res->json()];
    }
}
