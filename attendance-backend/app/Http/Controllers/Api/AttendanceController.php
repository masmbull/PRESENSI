<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Support\Geo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /** POST /api/attendances — catat absen masuk/pulang (gate: radius toko + face opsional). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'type' => 'nullable|string|in:masuk,pulang',
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'acc' => 'nullable|numeric|min:0',
            'device' => 'nullable|string|max:120',
            'force' => 'nullable|boolean',
            // field face — kepakai kalau FACEID_ENABLED=true
            'face_key' => 'nullable|string|max:64',
            'cosine' => 'nullable|numeric',
            'liveness' => 'nullable|numeric',
            'reason' => 'nullable|string|max:120',
            'thumb' => 'nullable|string',
            'attrs' => 'nullable|array',
        ]);

        $employee = Employee::with('store')->find($data['employee_id']);
        $type = $data['type'] ?? 'masuk';

        // Gate 1 — Face ID (dimatikan nyala lewat FACEID_ENABLED di .env).
        if (config('faceid.enabled')) {
            if (empty($data['face_key'])) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Face ID aktif: verifikasi wajah diperlukan sebelum absen',
                ], 422);
            }
            if ($employee->face_key === null) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Karyawan ini belum daftar wajah — hubungi admin',
                ], 422);
            }
            if ($data['face_key'] !== $employee->face_key) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Wajah tidak cocok dengan karyawan ini',
                ], 422);
            }
        }

        // Gate 2 — radius toko (geofence).
        $store = $employee->store;
        if ($store === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Karyawan belum ditempatkan di toko mana pun',
            ], 422);
        }
        $distance = Geo::distanceMeters((float) $data['lat'], (float) $data['lon'], (float) $store->lat, (float) $store->lon);
        $radius = (float) ($store->radius_m ?? config('faceid.radius'));
        if ($distance > $radius) {
            return response()->json([
                'ok' => false,
                'message' => 'Di luar radius toko — jarakmu ±'.round($distance).' m dari titik (maks '.round($radius).' m)',
                'distance_m' => round($distance, 1),
                'radius_m' => $radius,
                'store' => ['id' => $store->id, 'name' => $store->name],
            ], 422);
        }

        // Cooldown anti dobel-klik per karyawan + jenis (masuk/pulang).
        $cooldown = max(0, (int) config('faceid.cooldown'));
        if (! ($data['force'] ?? false) && $cooldown > 0) {
            $last = Attendance::where('employee_id', $employee->id)->where('type', $type)->latest('id')->first();
            if ($last !== null && $last->created_at->diffInSeconds(now()) < $cooldown) {
                return response()->json([
                    'logged' => false,
                    'cooldown' => true,
                    'cooldown_s' => $cooldown,
                    'record' => null,
                ]);
            }
        }

        $record = Attendance::create([
            'employee_id' => $employee->id,
            'store_id' => $store->id,
            'name' => $employee->name,
            'status' => 'hadir',
            'type' => $type,
            'lat' => $data['lat'],
            'lon' => $data['lon'],
            'acc' => $data['acc'] ?? null,
            'distance_m' => round($distance, 1),
            'within_radius' => true,
            'device' => $data['device'] ?? null,
            'face_key' => $data['face_key'] ?? null,
            'cosine' => $data['cosine'] ?? null,
            'liveness' => $data['liveness'] ?? null,
            'reason' => $data['reason'] ?? null,
            'thumb' => $data['thumb'] ?? null,
            'attrs' => $data['attrs'] ?? null,
        ]);

        return response()->json([
            'logged' => true,
            'cooldown' => false,
            'distance_m' => round($distance, 1),
            'record' => $record->load('employee:id,name,employee_code', 'store:id,name'),
        ], 201);
    }

    /** GET /api/attendances — riwayat. Filter: ?limit=50&date=2026-09-16&type=masuk&store_id=1&employee_id=1&q= */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'limit' => 'nullable|integer|min:1|max:500',
            'date' => 'nullable|date_format:Y-m-d',
            'type' => 'nullable|string|in:masuk,pulang',
            'status' => 'nullable|string|in:hadir,unknown,no_face',
            'store_id' => 'nullable|integer|exists:stores,id',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'q' => 'nullable|string|max:120',
        ]);

        return response()->json(
            Attendance::query()
                ->with('employee:id,name,employee_code', 'store:id,name,city_id')
                ->when($data['date'] ?? null, fn ($b, $d) => $b->whereDate('created_at', $d))
                ->when($data['type'] ?? null, fn ($b, $t) => $b->where('type', $t))
                ->when($data['status'] ?? null, fn ($b, $s) => $b->where('status', $s))
                ->when($data['store_id'] ?? null, fn ($b, $s) => $b->where('store_id', $s))
                ->when($data['employee_id'] ?? null, fn ($b, $e) => $b->where('employee_id', $e))
                ->when($data['q'] ?? null, fn ($b, $s) => $b->where('name', 'like', '%'.$s.'%'))
                ->latest('id')
                ->limit($data['limit'] ?? 50)
                ->get()
        );
    }

    /** GET /api/attendances/summary?date=YYYY-MM-DD&store_id=1&city_id=2 — rekap harian masuk/pulang. */
    public function summary(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'store_id' => 'nullable|integer|exists:stores,id',
            'city_id' => 'nullable|integer|exists:cities,id',
        ]);
        $date = $data['date'] ?? now()->toDateString();

        $rows = Attendance::query()
            ->with('store.city:id,name', 'employee:id,name,employee_code')
            ->whereDate('created_at', $date)
            ->when($data['store_id'] ?? null, fn ($b, $s) => $b->where('store_id', $s))
            ->when($data['city_id'] ?? null, fn ($b, $c) => $b->whereHas('store', fn ($q) => $q->where('city_id', $c)))
            ->get();

        $perEmployee = $rows->whereNotNull('employee_id')
            ->groupBy('employee_id')
            ->values()
            ->map(function ($group) {
                $sorted = $group->sortBy('created_at')->values();
                $first = $sorted->first();
                $masuk = $sorted->where('type', 'masuk');
                $pulang = $sorted->where('type', 'pulang');

                return [
                    'employee_id' => $first->employee_id,
                    'name' => $first->employee?->name ?? $first->name,
                    'employee_code' => $first->employee?->employee_code,
                    'store' => $first->store?->name,
                    'masuk_at' => $masuk->first()?->created_at?->toIso8601String(),
                    'pulang_at' => $pulang->last()?->created_at?->toIso8601String(),
                    'masuk_count' => $masuk->count(),
                    'pulang_count' => $pulang->count(),
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json([
            'date' => $date,
            'total' => $rows->count(),
            'masuk' => $rows->where('type', 'masuk')->count(),
            'pulang' => $rows->where('type', 'pulang')->count(),
            'employees' => $perEmployee,
        ]);
    }

    /** DELETE /api/attendances/{id} — hapus satu record. */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $id = $attendance->id;
        $attendance->delete();

        return response()->json(['ok' => true, 'id' => $id]);
    }

    /** DELETE /api/attendances/clear — kosongkan semua riwayat. */
    public function clear(): JsonResponse
    {
        $count = Attendance::count();
        Attendance::query()->delete();

        return response()->json(['ok' => true, 'deleted' => $count]);
    }
}