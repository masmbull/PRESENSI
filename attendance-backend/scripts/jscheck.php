<?php // jscheck.php — render blade absen, ekstrak <script> inline, lint pakai node --check
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = view('absen', [])->render();
preg_match_all('#<script(?![^>]*\bsrc=)[^>]*>(.*?)</script>#is', $html, $m);

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'absen-jscheck';
if (!is_dir($tmp)) {
    mkdir($tmp, 0777, true);
}
$ok = true;
foreach ($m[1] as $i => $js) {
    $file = $tmp . DIRECTORY_SEPARATOR . "block$i.js";
    file_put_contents($file, $js);
    $out = [];
    $rc = 0;
    exec('node --check ' . escapeshellarg($file) . ' 2>&1', $out, $rc);
    echo "block$i: " . ($rc === 0 ? 'OK' : 'SYNTAX ERROR') . ' (' . strlen($js) . " byte)\n";
    if ($rc !== 0) {
        $ok = false;
        echo implode("\n", $out) . "\n";
    }
}
// cek penanda fix anti-ketutupan
foreach (['.dd-panel.floating', '.dd-panel.up{', 'classList.add("floating")', 'function place()'] as $needle) {
    $hit = str_contains($html, $needle);
    echo 'penanda ' . $needle . ': ' . ($hit ? 'ADA' : 'HILANG') . "\n";
    if (!$hit) {
        $ok = false;
    }
}
echo $ok ? "SEMUA JS OK\n" : "ADA MASALAH\n";
