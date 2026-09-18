<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\City;
use App\Models\Employee;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceAdminController extends Controller
{
    /** GET /admin — ringkasan absen masuk & pulang (hari ini + tren 14 hari). */
    public function dashboard()
    {
        $today = now()->toDateString();

        $rows = Attendance::query()
            ->with('store.city:id,name', 'employee:id,name,employee_code')
            ->whereDate('created_at', $today)
            ->orderBy('id')
            ->get();

        $masuk = $rows->where('type', 'masuk');
        $pulang = $rows->where('type', 'pulang');
        $hadirIds = $masuk->pluck('employee_id')->filter()->unique();
        $pulangIds = $pulang->pluck('employee_id')->filter()->unique();

        $stats = [
            'date' => $today,
            'total' => $rows->count(),
            'masuk' => $masuk->count(),
            'pulang' => $pulang->count(),
            'orang_hadir' => $hadirIds->count(),
            'belum_pulang' => $hadirIds->diff($pulangIds)->count(),
            'luar_radius' => $rows->filter(fn ($r) => $r->within_radius === false)->count(),
            'stores_aktif' => $rows->pluck('store_id')->filter()->unique()->count(),
            'cities_aktif' => $rows->map(fn ($r) => $r->store?->city?->name)->filter()->unique()->count(),
        ];

        // Rekap per karyawan hari ini — jam masuk pertama & pulang terakhir.
        $perEmployee = $rows->whereNotNull('employee_id')
            ->groupBy('employee_id')
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'name' => $first->name ?? $first->employee?->name,
                    'code' => $first->employee?->employee_code,
                    'store' => $first->store?->name,
                    'city' => $first->store?->city?->name,
                    'masuk' => $group->where('type', 'masuk')->first()?->created_at?->format('H:i'),
                    'pulang' => $group->where('type', 'pulang')->last()?->created_at?->format('H:i'),
                    'luar_radius' => $group->filter(fn ($r) => $r->within_radius === false)->count(),
                ];
            })
            ->sortBy('name')
            ->values();

        // Rekap per toko & per daerah (kota) hari ini.
        $perStore = $rows->groupBy(fn ($r) => $r->store?->name ?? '—')
            ->map(fn ($g, $name) => [
                'store' => $name,
                'city' => $g->first()->store?->city?->name ?? '—',
                'orang' => $g->where('type', 'masuk')->pluck('employee_id')->filter()->unique()->count(),
                'masuk' => $g->where('type', 'masuk')->count(),
                'pulang' => $g->where('type', 'pulang')->count(),
            ])
            ->sortByDesc('masuk')
            ->values();

        $perCity = $rows->groupBy(fn ($r) => $r->store?->city?->name ?? '—')
            ->map(fn ($g, $name) => [
                'city' => $name,
                'stores' => $g->pluck('store_id')->filter()->unique()->count(),
                'orang' => $g->where('type', 'masuk')->pluck('employee_id')->filter()->unique()->count(),
                'masuk' => $g->where('type', 'masuk')->count(),
                'pulang' => $g->where('type', 'pulang')->count(),
            ])
            ->sortByDesc('masuk')
            ->values();

        return view('admin.dashboard', [
            'stats' => $stats,
            'trend' => $this->trend(14),
            'perEmployee' => $perEmployee,
            'perStore' => $perStore,
            'perCity' => $perCity,
            'latest' => Attendance::query()->with('store.city:id,name')->latest('id')->limit(8)->get(),
        ]);
    }

    /** Tren harian masuk/pulang + tinggi bar (%) buat chart CSS. */
    private function trend(int $days): array
    {
        $rows = Attendance::query()
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->selectRaw('date(created_at) as d, type, count(*) as c')
            ->groupBy('d', 'type')
            ->get();

        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $out[$day->toDateString()] = [
                'date' => $day->toDateString(),
                'label' => $day->format('d/m'),
                'masuk' => 0,
                'pulang' => 0,
            ];
        }

        foreach ($rows as $row) {
            $key = (string) $row->d;
            if (! isset($out[$key])) continue;
            $out[$key][$row->type === 'pulang' ? 'pulang' : 'masuk'] = (int) $row->c;
        }

        $out = array_values($out);
        $max = 1;
        foreach ($out as $r) $max = max($max, $r['masuk'], $r['pulang']);
        foreach ($out as &$r) {
            $r['h_masuk'] = (int) round($r['masuk'] / $max * 100);
            $r['h_pulang'] = (int) round($r['pulang'] / $max * 100);
        }

        return $out;
    }

    /** GET /admin/absensi — riwayat absen (tabel, filter, export). */
    public function index()
    {
        return view('admin.absensi', [
            'cities' => City::query()->orderBy('name')->get(['id', 'name']),
            'stores' => Store::query()->with('city:id,name')->orderBy('name')->get(['id', 'city_id', 'name', 'radius_m']),
            'range' => $this->defaultRange(),
        ]);
    }

    /** GET /admin/absensi/data?from=&to=&limit= — data buat tabel client-side. */
    public function data(Request $request): JsonResponse
    {
        $range = $this->defaultRange();
        $from = $this->normDate($request->query('from')) ?? $range['from'];
        $to = $this->normDate($request->query('to')) ?? $range['to'];
        $limit = (int) min(50000, max(1, (int) $request->query('limit', 20000)));

        $rows = Attendance::query()
            ->with('store.city:id,name', 'employee:id,name,employee_code')
            ->when($from !== 'all', fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to !== 'all', fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Attendance $a) => [
                'id' => $a->id,
                'at' => $a->created_at?->format('Y-m-d H:i:s'),
                'date' => $a->created_at?->format('Y-m-d'),
                'time' => $a->created_at?->format('H:i:s'),
                'name' => $a->name ?? $a->employee?->name,
                'code' => $a->employee?->employee_code,
                'type' => $a->type,
                'status' => $a->status,
                'store' => $a->store?->name,
                'store_id' => $a->store_id,
                'city' => $a->store?->city?->name,
                'distance_m' => $a->distance_m,
                'radius_m' => $a->store?->radius_m,
                'within' => $a->within_radius,
                'acc' => $a->acc,
                'ip' => $a->ip,
                'lat' => $a->lat,
                'lon' => $a->lon,
                'device' => $a->device,
                'reason' => $a->reason,
                'face_key' => $a->face_key,
                'cosine' => $a->cosine,
                'liveness' => $a->liveness,
                'has_thumb' => ! empty($a->thumb),
                'attrs' => $a->attrs,
            ])
            ->values();

        return response()->json([
            'ok' => true,
            'from' => $from,
            'to' => $to,
            'limit' => $limit,
            'count' => $rows->count(),
            'rows' => $rows,
        ]);
    }

    /** GET /admin/absensi/{id}/foto — thumbnail selfie (kalau ada) buat modal detail. */
    public function photo(Attendance $attendance)
    {
        $thumb = trim((string) ($attendance->thumb ?? ''));
        if ($thumb === '') abort(404, 'Record ini tidak punya foto.');

        $meta = 'data:image/jpeg;base64';
        if (str_contains($thumb, ',')) {
            [$meta, $thumb] = explode(',', $thumb, 2);
        }

        $bytes = base64_decode(trim($thumb), true);
        if ($bytes === false || $bytes === '') abort(404, 'Foto tidak bisa dibaca.');

        return response($bytes, 200)
            ->header('Content-Type', str_contains(strtolower($meta), 'png') ? 'image/png' : 'image/jpeg')
            ->header('Cache-Control', 'private, max-age=600');
    }

    /** POST /admin/absensi/manual — input absen manual (SPG lupa absen / koreksi admin). */
    public function storeManual(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'type' => 'required|string|in:masuk,pulang',
            'note' => 'nullable|string|max:120',
        ]);

        $employee = Employee::with('store.city')->findOrFail($data['employee_id']);
        $store = $employee->store;
        if ($store === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Karyawan ini belum ditempatkan di toko — set tokonya dulu di Kelola Wajah.',
            ], 422);
        }

        $at = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'], config('app.timezone'));
        if ($at->isFuture()) {
            return response()->json(['ok' => false, 'message' => 'Waktu absen tidak boleh di masa depan.'], 422);
        }

        $record = Attendance::create([
            'employee_id' => $employee->id,
            'store_id' => $store->id,
            'name' => $employee->name,
            'status' => 'hadir',
            'type' => $data['type'],
            'lat' => $store->lat,
            'lon' => $store->lon,
            'acc' => null,
            'distance_m' => 0.0,
            'within_radius' => true,
            'device' => 'input manual admin',
            'ip' => $request->header('CF-Connecting-IP') ?? $request->ip(),
            'reason' => 'manual'.(isset($data['note']) && $data['note'] !== '' ? ': '.$data['note'] : ''),
        ]);

        // Timestamp mengikuti waktu yang diisi admin (bukan waktu server sekarang).
        $record->created_at = $at;
        $record->updated_at = $at;
        $record->save();

        return response()->json([
            'ok' => true,
            'id' => $record->id,
            'message' => 'Absen '.$data['type'].' '.$employee->name.' ('.$at->format('d/m/Y H:i').') tersimpan.',
        ], 201);
    }

    /** POST /admin/absensi/{id}/hapus — hapus satu record absen. */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $label = ($attendance->name ?? 'record').' · '.$attendance->created_at?->format('d/m/Y H:i');
        $attendance->delete();

        return response()->json(['ok' => true, 'message' => 'Record dihapus: '.$label]);
    }

    /** Rentang default daftar riwayat: awal bulan ini → hari ini. */
    private function defaultRange(): array
    {
        $oldest = Attendance::query()->min('created_at');

        return [
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
            'oldest' => $oldest ? Carbon::parse($oldest)->toDateString() : null,
        ];
    }

    /** Normalisasi input tanggal: Y-m-d valid, 'all', atau null (pakai default). */
    private function normDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        if ($value === 'all') return 'all';

        $date = Carbon::createFromFormat('Y-m-d', $value);

        return ($date && $date->format('Y-m-d') === $value) ? $value : null;
    }
}
