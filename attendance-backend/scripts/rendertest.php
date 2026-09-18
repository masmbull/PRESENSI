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
        // cuplikan: pastikan label toko+kota muncul di JS
        if ($v === 'wajah') {
            $found = str_contains($html, 'e.store.city') ? 'label kota ADA' : 'label kota HILANG';
            echo "  $found\n";
        }
    } catch (Throwable $e) {
        echo "$v: GAGAL — " . $e->getMessage() . "\n";
    }
}
