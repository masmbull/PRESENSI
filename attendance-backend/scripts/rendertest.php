<?php // rendertest.php — render absen & wajah blade jadi string, laporan hasil
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (['absen' => [], 'wajah' => ['stats' => ['cities' => 0, 'stores' => 0, 'employees' => 0, 'linked' => 0, 'no_face' => 0]]] as $v => $data) {
    try {
        $html = view($v, $data)->render();
        $len = strlen($html);
        $bad = preg_match('/[\xC2\xC3\xE2\xF0](?![\x80-\xBF]{1,2})/s', $html); // heuristik mojibake mentah
        echo "$v: OK ($len byte, mojibake-raw=" . var_export((bool) $bad, true) . ")\n";
        // cuplikan: pastikan elemen kunci view wajah ter-render
        if ($v === 'wajah') {
            $markers = ['kw-tab', 'pMaster', 'pWajah', 'pDanger', 'btnKota', 'btnLokasi', 'btnDaftar', 'storeRows', 'empList'];
            $missing = array_filter($markers, fn ($m) => ! str_contains($html, $m));
            echo $missing
                ? '  MARKER HILANG: ' . implode(', ', $missing) . "\n"
                : "  marker wajah lengkap\n";
        }
    } catch (Throwable $e) {
        echo "$v: GAGAL — " . $e->getMessage() . "\n";
    }
}
