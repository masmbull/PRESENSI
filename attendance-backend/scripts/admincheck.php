<?php // admincheck.php — uji dashboard admin: route+middleware, render view, lint JS inline,
// endpoint JSON /admin/absensi/data, dan input manual (buat → cek → hapus).
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

$fail = 0;
$pass = function (string $label, bool $ok, string $extra = '') use (&$fail) {
    echo ($ok ? 'OK   ' : 'GAGAL') . " — $label" . ($extra !== '' ? " ($extra)" : '') . "\n";
    if (! $ok) $fail++;
};

// Password admin untuk uji (di server diisi dari .env).
config(['faceid.admin_password' => 'uji-admin']);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$hit = function (string $uri, string $method = 'GET', array $body = []) use ($kernel) {
    $req = Request::create($uri, $method, $body, [], [], [
        'PHP_AUTH_USER' => 'admin',
        'PHP_AUTH_PW' => 'uji-admin',
    ]);

    return $kernel->handle($req);
};

echo "=== route + middleware (Basic auth face.admin) ===\n";
$unauth = $kernel->handle(Request::create('/admin', 'GET'));
$pass('/admin tanpa kredensial → 401', $unauth->getStatusCode() === 401, 'HTTP '.$unauth->getStatusCode());

$pages = [];
foreach (['/admin' => 'dashboard', '/admin/absensi' => 'riwayat'] as $uri => $label) {
    $res = $hit($uri);
    $pages[$label] = $res->getContent();
    $pass("$uri ($label) → 200", $res->getStatusCode() === 200, 'HTTP '.$res->getStatusCode().', '.strlen($pages[$label]).' byte');
}


echo "\n=== penanda UI (sidebar + fitur) ===\n";
$dashMarkers = ['class="side"', 'Ringkasan hari ini', 'Riwayat absen &amp; export', 'Kelola wajah &amp; karyawan', 'Absen masuk', 'Tren 14 hari', 'Rekap per karyawan hari ini', '.kpis{display:grid'];
$absenMarkers = ['id="fFrom"', 'id="fQ"', 'id="fType"', 'id="fCity"', 'id="fStore"', 'id="btnXls"', 'id="btnPdf"', 'id="btnManual"', 'function exportXls()', 'function exportPdf()', 'function openDetail(id)', 'data-sort="distance_m"', 'id="kpis"', '.filters{display:grid', 'function renderKpi()', 'function filterInfo()'];
foreach ($dashMarkers as $m) $pass('dashboard: '.$m, str_contains($pages['dashboard'], $m));
foreach ($absenMarkers as $m) $pass('riwayat: '.$m, str_contains($pages['riwayat'], $m));

echo "\n=== lint JS inline (node --check) ===\n";
if (trim((string) shell_exec('node --version 2>&1')) === '') {
    echo "SKIP — node tidak ada di PATH\n";
} else {
    foreach ($pages as $label => $html) {
        preg_match_all('#<script(?![^>]*\bsrc=)[^>]*>(.*?)</script>#is', $html, $m);
        foreach ($m[1] as $i => $js) {
            $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "admin-$label-$i.js";
            file_put_contents($file, $js);
            $out = [];
            $rc = 0;
            exec('node --check ' . escapeshellarg($file) . ' 2>&1', $out, $rc);
            $pass("JS $label blok$i", $rc === 0, strlen($js).' byte');
            if ($rc !== 0) echo implode("\n", $out) . "\n";
        }
    }
}

echo "\n=== endpoint JSON /admin/absensi/data ===\n";
$res = $hit('/admin/absensi/data?from=all&to=all');
$json = json_decode($res->getContent(), true);
$pass('data: HTTP 200 + ok=true', $res->getStatusCode() === 200 && ($json['ok'] ?? false) === true, 'HTTP '.$res->getStatusCode());
$pass('data: ada key rows', isset($json['rows']) && is_array($json['rows']), 'count='.count($json['rows'] ?? []));
if (! empty($json['rows'])) {
    $keys = ['id', 'at', 'date', 'time', 'name', 'type', 'status', 'store', 'city', 'distance_m', 'within', 'acc', 'ip', 'lat', 'lon', 'device', 'has_thumb'];
    $missing = array_diff($keys, array_keys($json['rows'][0]));
    $pass('data: kolom lengkap', $missing === [], $missing ? 'kurang: '.implode(',', $missing) : 'lengkap');
}

echo "\n=== input manual + hapus (round-trip) ===\n";
$employee = Employee::query()->whereNotNull('store_id')->with('store')->first();
if ($employee === null) {
    echo "SKIP — belum ada karyawan yang punya toko\n";
} else {
    $ctl = $app->make(App\Http\Controllers\Admin\AttendanceAdminController::class);
    $req = Request::create('/admin/absensi/manual', 'POST', [
        'employee_id' => $employee->id,
        'date' => now()->subDay()->toDateString(),
        'time' => '08:15',
        'type' => 'masuk',
        'note' => 'uji otomatis admincheck',
    ]);
    $made = $ctl->storeManual($req)->getData(true);
    $pass('manual: tersimpan', ($made['ok'] ?? false) === true, $made['message'] ?? '');
    if ($made['ok'] ?? false) {
        $rec = Attendance::find($made['id']);
        $pass('manual: timestamp ikut input', $rec && $rec->created_at->format('H:i') === '08:15', $rec?->created_at?->format('Y-m-d H:i'));
        $pass('manual: store ikut karyawan', $rec && $rec->store_id === $employee->store_id);
        $pass('manual: ditandai manual', $rec && str_contains((string) $rec->device, 'manual'), (string) $rec?->device);
        $del = $ctl->destroy($rec)->getData(true);
        $pass('manual: record uji dihapus', ($del['ok'] ?? false) === true && Attendance::find($made['id']) === null);
    }

    // waktu di masa depan harus ditolak
    $bad = $ctl->storeManual(Request::create('/admin/absensi/manual', 'POST', [
        'employee_id' => $employee->id,
        'date' => now()->addDay()->toDateString(),
        'time' => '09:00',
        'type' => 'masuk',
    ]))->getData(true);
    $pass('manual: tolak waktu masa depan', ($bad['ok'] ?? true) === false, $bad['message'] ?? '');
}

echo "\n=== cek mojibake (byte mentah) ===\n";
foreach ($pages as $label => $html) {
    $bad = preg_match('/[\xC2\xC3\xE2\xF0](?![\x80-\xBF]{1,2})/s', $html);
    $pass("$label: teks bersih", ! $bad);
}

echo $fail === 0 ? "\nSEMUA UJI LULUS\n" : "\nADA $fail UJI GAGAL\n";
