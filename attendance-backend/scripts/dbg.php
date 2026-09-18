<?php // dbg.php — sementara: cari penyebab error render & nilai device record manual
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $html = view('admin.absensi', [
        'cities' => App\Models\City::query()->orderBy('name')->get(['id', 'name']),
        'stores' => App\Models\Store::query()->with('city:id,name')->orderBy('name')->get(['id', 'city_id', 'name', 'radius_m']),
        'range' => ['from' => '2026-09-01', 'to' => '2026-09-18', 'oldest' => null],
    ])->render();
    echo 'RENDER OK ('.strlen($html)." byte)\n";
} catch (Throwable $e) {
    echo get_class($e).': '.$e->getMessage()."\n@ ".$e->getFile().':'.$e->getLine()."\n";
}

$last = App\Models\Attendance::query()->latest('id')->first();
echo 'device record terakhir: '.var_export($last?->device, true).' | raw: '.var_export($last?->getRawOriginal('device'), true)."\n";