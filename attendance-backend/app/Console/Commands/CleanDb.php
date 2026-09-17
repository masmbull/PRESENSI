<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\City;
use App\Models\Employee;
use App\Models\Store;
use Illuminate\Console\Command;

class CleanDb extends Command
{
    protected $signature = 'db:clean';

    protected $description = 'Bersihkan master data: trim nama, merge varian kota hasil scrape, gabung karyawan duplikat per toko.';

    /**
     * Varian nama kota hasil scrape → kota induk.
     * "Blimbing" itu kecamatan di kota Malang, bukan kota.
     */
    private const CITY_MERGES = [
        'Jakarta Selatan' => 'Jakarta',
        'Jakarta Utara' => 'Jakarta',
        'Jakarta Pusat' => 'Jakarta',
        'Jakarta Barat' => 'Jakarta',
        'Jakarta Timur' => 'Jakarta',
        'Blimbing' => 'Malang',
    ];

    public function handle(): int
    {
        $this->info('== 1. trim whitespace nama ==');
        $this->trimAll(City::all(), 'name', 'kota');
        $this->trimAll(Store::all(), 'name', 'toko');
        $this->trimAll(Employee::all(), 'name', 'karyawan');

        $this->info('== 2. merge varian kota ==');
        foreach (self::CITY_MERGES as $from => $to) {
            $src = City::where('name', $from)->first();
            if ($src === null) {
                continue;
            }
            $dst = City::where('name', $to)->first();

            if ($dst === null) {
                // kota induk belum ada → cukup rename
                $src->update(['name' => $to]);
                $this->line("  rename kota '$from' → '$to' ({$src->stores()->count()} toko)");
                continue;
            }

            // pindahkan toko satu-satu — kalau nama toko sama di kota tujuan,
            // gabungkan (employees + attendance pindah, toko duplikat dihapus).
            $moved = 0;
            $mergedStores = 0;
            foreach ($src->stores()->get() as $store) {
                $twin = Store::where('city_id', $dst->id)
                    ->whereRaw('lower(name) = ?', [strtolower(trim($store->name))])
                    ->first();
                if ($twin !== null) {
                    Employee::where('store_id', $store->id)->update(['store_id' => $twin->id]);
                    Attendance::where('store_id', $store->id)->update(['store_id' => $twin->id]);
                    $store->delete();
                    $mergedStores++;
                    continue;
                }
                $store->update(['city_id' => $dst->id]);
                $moved++;
            }
            $src->delete();
            $this->line("  merge kota '$from' → '$to' ({$moved} toko pindah, {$mergedStores} toko digabung)");
        }

        $this->info('== 3. gabung karyawan duplikat (nama sama di toko yang sama) ==');
        $merged = 0;
        Employee::query()
            ->whereNotNull('store_id')
            ->get()
            ->groupBy(fn ($e) => $e->store_id.'|'.strtolower(trim($e->name)))
            ->each(function ($group) use (&$merged) {
                if ($group->count() < 2) {
                    return;
                }
                // yang menang: yang udah punya wajah, kalau semua kosong yang id paling kecil
                $keep = $group->sortByDesc(fn ($e) => $e->face_key !== null ? 1 : 0)->first();
                $group->except($keep->id)->each(function ($dup) use ($keep, &$merged) {
                    Attendance::where('employee_id', $dup->id)->update(['employee_id' => $keep->id]);
                    if ($keep->face_key === null && $dup->face_key !== null) {
                        $keep->face_key = $dup->face_key;
                        $keep->save();
                    }
                    $dup->delete();
                    $merged++;
                });
            });
        $this->line("  {$merged} karyawan duplikat digabung");

        $this->info('== ringkasan ==');
        $this->line('  kota    : '.City::count());
        $this->line('  toko    : '.Store::count());
        $this->line('  karyawan: '.Employee::count());
        $this->line('  wajah   : '.Employee::whereNotNull('face_key')->count());

        return self::SUCCESS;
    }

    private function trimAll($rows, string $field, string $label): void
    {
        $n = 0;
        foreach ($rows as $row) {
            $clean = trim((string) $row->{$field});
            if ($clean !== '' && $clean !== $row->{$field}) {
                $row->update([$field => $clean]);
                $n++;
            }
        }
        $this->line("  {$label}: {$n} nama di-trim");
    }
}
