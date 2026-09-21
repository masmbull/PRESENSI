<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Employee;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Data Karyawan (HRD): halaman khusus /admin/karyawan.
 * Beda sama /kelola-wajah: halaman ini fokus biodata lengkap ala HRD
 * (identitas, kontak, kepegawaian, kontak darurat, bank & BPJS, foto),
 * bukan pendaftaran wajah.
 */
class EmployeeController extends Controller
{
    private const GENDERS = ['Laki-laki', 'Perempuan'];
    private const MARITALS = ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];
    private const RELIGIONS = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
    private const EDUCATIONS = ['SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'];
    private const STATUSES = ['Tetap', 'Kontrak', 'Magang', 'Harian'];

    /** GET /admin/karyawan — halaman daftar + form. */
    public function index(): View
    {
        return view('admin.karyawan', [
            'cities' => City::query()->orderBy('name')->get(['id', 'name']),
            'stores' => Store::query()->with('city:id,name')->orderBy('name')->get(['id', 'city_id', 'name']),
            'genders' => self::GENDERS,
            'maritals' => self::MARITALS,
            'religions' => self::RELIGIONS,
            'educations' => self::EDUCATIONS,
            'statuses' => self::STATUSES,
        ]);
    }

    /** GET /admin/karyawan/data — JSON buat tabel client-side. */
    public function data(): JsonResponse
    {
        $rows = Employee::query()
            ->with('store.city:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Employee $e) => $this->payload($e));

        return response()->json(['ok' => true, 'count' => $rows->count(), 'rows' => $rows->values()]);
    }

    /** POST /admin/karyawan — tambah karyawan + profil HRD. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        $this->guardNameClash($data, null);
        $data['name'] = trim($data['name']);
        $this->handlePhoto($request, $data);

        $employee = Employee::create($data + ['active' => true]);

        return response()->json([
            'ok' => true,
            'employee' => $this->payload($employee->fresh()->load('store.city:id,name')),
            'message' => 'Karyawan '.$employee->name.' ditambahkan.',
        ], 201);
    }

    /** GET /admin/karyawan/{employee} — detail 1 karyawan (modal profil). */
    public function show(Employee $employee): JsonResponse
    {
        return response()->json(['ok' => true, 'employee' => $this->payload($employee->load('store.city:id,name'))]);
    }

    /** POST /admin/karyawan/{employee} — ubah profil HRD. */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate($this->rules($employee->id));

        $this->guardNameClash($data, $employee->id);
        $data['name'] = trim($data['name']);
        $this->handlePhoto($request, $data, $employee);

        $employee->update($data);

        return response()->json([
            'ok' => true,
            'employee' => $this->payload($employee->fresh()->load('store.city:id,name')),
            'message' => 'Data '.$employee->name.' diperbarui.',
        ]);
    }

    /** POST /admin/karyawan/{employee}/hapus — hapus (ditolak kalau punya riwayat absen). */
    public function destroy(Employee $employee): JsonResponse
    {
        if ($employee->attendances()->exists()) {
            return response()->json([
                'ok' => false,
                'message' => 'Karyawan '.$employee->name.' punya riwayat absen — nonaktifkan saja biar laporan tetap utuh.',
            ], 422);
        }

        if ($employee->photo) Storage::disk('public')->delete($employee->photo);
        $name = $employee->name;
        $employee->delete();

        return response()->json(['ok' => true, 'message' => 'Karyawan '.$name.' dihapus.']);
    }

    // ================= aturan validasi + helper =================

    /** Aturan validasi form HRD (tambah & ubah). */
    private function rules(?int $ignoreId = null): array
    {
        return [
            'name' => 'required|string|max:120',
            'employee_code' => ['nullable', 'string', 'max:40', Rule::unique('employees', 'employee_code')->ignore($ignoreId)],
            'nik' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]*$/', Rule::unique('employees', 'nik')->ignore($ignoreId)],
            'store_id' => 'nullable|integer|exists:stores,id',
            'birth_place' => 'nullable|string|max:80',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'gender' => 'nullable|string|in:L,P',
            'marital_status' => 'nullable|string|in:'.implode(',', self::MARITALS),
            'religion' => 'nullable|string|in:'.implode(',', self::RELIGIONS),
            'education' => 'nullable|string|in:'.implode(',', self::EDUCATIONS),
            'phone' => 'nullable|string|max:20|regex:/^[0-9+() \-.]*$/',
            'email' => 'nullable|email:rfc|max:120',
            'address' => 'nullable|string|max:2000',
            'position' => 'nullable|string|max:60',
            'department' => 'nullable|string|max:60',
            'employment_status' => 'nullable|string|in:'.implode(',', self::STATUSES),
            'join_date' => 'nullable|date',
            'contract_end' => 'nullable|date|after_or_equal:join_date',
            'resign_date' => 'nullable|date|after_or_equal:join_date',
            'emergency_name' => 'nullable|string|max:120',
            'emergency_relation' => 'nullable|string|max:40',
            'emergency_phone' => 'nullable|string|max:20|regex:/^[0-9+() \-.]*$/',
            'bank_name' => 'nullable|string|max:40',
            'bank_account' => 'nullable|string|max:40|regex:/^[0-9 \-.]*$/',
            'bank_holder' => 'nullable|string|max:120',
            'bpjs_health' => 'nullable|string|max:30',
            'bpjs_labor' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_photo' => 'nullable|boolean',
        ];
    }

    /**
     * Nama yang sama di toko yang sama (case-insensitive) ditolak —
     * konsisten sama dedupe di FaceManagementController.
     */
    private function guardNameClash(array $data, ?int $ignoreId): void
    {
        $storeId = $data['store_id'] ?? null;
        $hit = Employee::query()
            ->where('store_id', $storeId)
            ->whereRaw('lower(name) = ?', [mb_strtolower(trim($data['name']))])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
        if ($hit) {
            throw ValidationException::withMessages(['name' => 'Nama ini sudah terdaftar di toko tersebut.']);
        }
    }

    /** Upload foto profil baru / hapus foto lama (disk public/karyawan). */
    private function handlePhoto(Request $request, array &$data, ?Employee $employee = null): void
    {
        unset($data['photo_file'], $data['photo_path']);
        $remove = filter_var($request->input('remove_photo'), FILTER_VALIDATE_BOOLEAN);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('karyawan', 'public');
            if ($employee?->photo) Storage::disk('public')->delete($employee->photo);
            $data['photo'] = $path;
            unset($data['remove_photo']);
            return;
        }

        unset($data['photo']);
        if ($remove && $employee?->photo) {
            Storage::disk('public')->delete($employee->photo);
            $data['photo'] = null;
        }
        unset($data['remove_photo']);
    }

    /** Bentuk JSON 1 karyawan buat tabel client-side + modal profil. */
    private function payload(Employee $e): array
    {
        $arr = $e->toArray();
        $arr['store_name'] = $e->store?->name;
        $arr['city_name'] = $e->store?->city?->name;
        $arr['photo_url'] = $e->photo ? Storage::url($e->photo) : null;
        $arr['age'] = $e->age();
        $arr['tenure'] = $e->tenure();
        $arr['has_face'] = (bool) $e->face_key;
        return $arr;
    }
}
