<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /** GET /api/employees?store_id=1 — daftar karyawan (dropdown langkah 3). */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => 'nullable|integer|exists:stores,id',
        ]);

        return response()->json(
            Employee::query()
                ->where('active', true)
                ->with('store:id,name,city_id')
                ->withCount('attendances')
                ->when($data['store_id'] ?? null, fn ($b, $s) => $b->where('store_id', $s))
                ->orderBy('name')
                ->get()
        );
    }

    /** GET /api/employees/no-face — karyawan aktif yang belum punya wajah (cek admin). */
    public function noFace(): JsonResponse
    {
        return response()->json(
            Employee::query()
                ->where('active', true)
                ->whereNull('face_key')
                ->with('store:id,name,city_id')
                ->orderBy('name')
                ->get(['id', 'name', 'employee_code', 'store_id'])
        );
    }

    /** POST /api/employees — daftarkan karyawan (dedupe per toko+nama). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'store_id' => 'nullable|integer|exists:stores,id',
            'employee_code' => 'nullable|string|max:40|unique:employees,employee_code',
            'face_key' => 'nullable|string|max:64|unique:employees,face_key',
            'active' => 'nullable|boolean',
        ]);

        $name = trim($data['name']);

        // dedupe: nama sama di toko yang sama → balikin baris yang udah ada
        $existing = Employee::where('store_id', $data['store_id'] ?? null)
            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->first();
        if ($existing !== null) {
            return response()->json($existing->load('store.city'));
        }

        $employee = Employee::create([
            'name' => $name,
            'store_id' => $data['store_id'] ?? null,
            'employee_code' => $data['employee_code'] ?? null,
            'face_key' => $data['face_key'] ?? null,
            'active' => $data['active'] ?? true,
        ]);

        return response()->json($employee, 201);
    }

    /** POST /api/employees/sync — upsert dari daftar wajah engine: {faces:[{id,name},...]} */
    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'faces' => 'required|array|max:500',
            'faces.*.id' => 'required|string|max:64',
            'faces.*.name' => 'required|string|max:120',
        ]);

        $rows = [];
        foreach ($data['faces'] as $face) {
            // nama engine = "nama — toko" (biar unik) → buang sufiks toko dulu
            $clean = trim(explode(' — ', trim($face['name']))[0]);
            $name = mb_substr($clean !== '' ? $clean : trim($face['name']), 0, 120);

            // Match karyawan yang udah ada by nama dulu (biar gak dobel),
            // lalu ikat face_key engine-nya kalau masih bebas.
            $employee = Employee::whereRaw('lower(name) = ?', [mb_strtolower($name)])->first();
            if ($employee !== null
                && ! Employee::where('face_key', $face['id'])->where('id', '!=', $employee->id)->exists()) {
                $employee->update(['face_key' => $face['id']]);
                $rows[] = $employee;

                continue;
            }

            $rows[] = Employee::updateOrCreate(
                ['face_key' => $face['id']],
                ['name' => $name]
            );
        }

        return response()->json(['synced' => count($rows), 'employees' => $rows]);
    }

    /** DELETE /api/employees/{id} — hapus karyawan (riwayat absen tetap ada). */
    public function destroy(Employee $employee): JsonResponse
    {
        $id = $employee->id;
        $employee->delete();

        return response()->json(['ok' => true, 'id' => $id]);
    }
}