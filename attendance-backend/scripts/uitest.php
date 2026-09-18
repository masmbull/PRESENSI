<?php // uitest.php — uji dropdown absen di Edge headless: panel harus floating (di <body>),
// tidak ketutupan section di bawahnya, dan tidak nabrak actionbar. Output: hasil + screenshot.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = isset($argv[1]) && is_file($argv[1]) ? file_get_contents($argv[1]) : view('absen', [])->render();
echo 'sumber: ' . (isset($argv[1]) && is_file($argv[1]) ? 'live snapshot' : 'render lokal') . "\n";

$harness = <<<'HTML'
<div id="uitest-result" style="display:none"></div>
<script>
(function () {
  var out = {};
  function measure() {
    try {
      var p = document.getElementById("ddEmpPanel");
      var r = p.getBoundingClientRect();
      out.parent = p.parentNode === document.body ? "body" : (p.parentNode ? (p.parentNode.id || p.parentNode.tagName) : "none");
      out.position = getComputedStyle(p).position;
      out.zIndex = getComputedStyle(p).zIndex;
      out.rect = [Math.round(r.left), Math.round(r.top), Math.round(r.width), Math.round(r.height)];
      out.openUp = p.classList.contains("up");
      out.hidden = p.hidden;
      var x = Math.round(r.left + r.width / 2);
      var y = Math.round(r.bottom - 12);
      var el = document.elementFromPoint(x, y);
      out.hit = el ? (el.className || el.tagName) : "null";
      out.panelOnTop = !!(el && p.contains(el));
      var bar = document.querySelector(".actionbar");
      out.barH = bar ? bar.offsetHeight : 0;
      out.overlapActionbar = r.bottom > (window.innerHeight - out.barH);
      out.innerH = window.innerHeight;
      out.items = document.querySelectorAll("#ddEmpList .dd-item").length;
    } catch (e) {
      out.err = String(e);
    }
    document.getElementById("uitest-result").textContent = JSON.stringify(out);
  }
  function drive() {
    try {
      ddCity.setItems([{ v: 1, label: "Jakarta", sub: "DKI Jakarta" }]);
      ddStore.setItems([{ v: 11, label: "Toko Sudirman", sub: "Jakarta" }]);
      var emps = [];
      for (var i = 1; i <= 12; i++) {
        emps.push({ v: i, label: "SPG " + i, sub: "Toko Sudirman - Jakarta" });
      }
      ddEmp.setItems(emps);
      ddEmp.setDisabled(false);
      document.getElementById("ddEmpBtn").click();
    } catch (e) {
      out.err = String(e);
    }
    setTimeout(measure, 400);
  }
  setTimeout(drive, 300);
})();
</script>
HTML;

$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'absen-uitest.html';
file_put_contents($file, str_replace('</body>', $harness . "\n</body>", $html));

$edge = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
$shots = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app';
$png = realpath($shots) . DIRECTORY_SEPARATOR . 'uitest-dropdown.png';
$userData = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'edge-uitest-profile';

$cmd = escapeshellarg($edge) . ' --headless=new --disable-gpu --hide-scrollbars --no-first-run'
    . ' --no-default-browser-check --user-data-dir=' . escapeshellarg($userData)
    . ' --window-size=390,844 --virtual-time-budget=4000 --screenshot=' . escapeshellarg($png)
    . ' --dump-dom ' . escapeshellarg('file:///' . str_replace('\\', '/', $file)) . ' 2>NUL';

$dom = shell_exec($cmd);
if (!preg_match('#<div id="uitest-result"[^>]*>(.*?)</div>#s', (string) $dom, $m)) {
    echo "GAGAL: hasil uji tidak ditemukan di DOM\n";
    exit(1);
}
$res = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);
if (!is_array($res)) {
    echo "GAGAL: json tidak valid -> " . $m[1] . "\n";
    exit(1);
}
foreach ($res as $k => $v) {
    echo str_pad($k, 16) . ': ' . (is_bool($v) ? var_export($v, true) : (is_array($v) ? implode(',', $v) : $v)) . "\n";
}
echo 'screenshot      : ' . $png . (is_file($png) ? ' (' . filesize($png) . " byte)\n" : " TIDAK ADA\n");
$okParent = ($res['parent'] ?? '') === 'body';
$okTop = !empty($res['panelOnTop']);
$okBar = empty($res['overlapActionbar']);
echo ($okParent && $okTop && $okBar) ? "HASIL: LULUS\n" : "HASIL: GAGAL\n";