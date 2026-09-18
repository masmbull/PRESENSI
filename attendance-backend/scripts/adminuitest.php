<?php // adminuitest.php — uji dashboard admin di Edge headless: tabel client-side, filter, sort,
// modal detail, export Excel/PDF (di-stub), plus screenshot. Data: dari endpoint lokal + 3 baris uji.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;

config(['faceid.admin_password' => 'uji-admin']);
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$get = function (string $uri) use ($kernel) {
    return $kernel->handle(Request::create($uri, 'GET', [], [], [], [
        'PHP_AUTH_USER' => 'admin',
        'PHP_AUTH_PW' => 'uji-admin',
    ]));
};

$rows = json_decode($get('/admin/absensi/data?from=all&to=all')->getContent(), true)['rows'] ?? [];

// Mode snapshot (opsional): uji halaman LIVE, bukan render lokal.
//   php scripts/adminuitest.php <dashboard.html> <absensi.html> <data.json>
$snapDash = isset($argv[1]) && is_file($argv[1]) ? file_get_contents($argv[1]) : null;
$snapAbsen = isset($argv[2]) && is_file($argv[2]) ? file_get_contents($argv[2]) : null;
$snapData = isset($argv[3]) && is_file($argv[3]) ? json_decode((string) file_get_contents($argv[3]), true) : null;
if (is_array($snapData) && isset($snapData['rows'])) {
    $rows = $snapData['rows'];
}
echo 'sumber halaman: ' . ($snapDash && $snapAbsen ? 'snapshot LIVE' : 'render lokal') . "\n";

// 3 baris sintetis buat uji filter/pencarian (DB lokal bisa kosong).
$synthetic = [
    ['id' => 900001, 'at' => '2026-09-18 07:10:00', 'date' => '2026-09-18', 'time' => '07:10:00', 'name' => 'Budi Uji Coba', 'code' => 'UJI-1', 'type' => 'masuk', 'status' => 'hadir', 'store' => 'TOKO UJI A', 'store_id' => 9001, 'city' => 'KOTA UJI', 'distance_m' => 12.4, 'radius_m' => 150, 'within' => true, 'acc' => 8, 'ip' => '10.0.0.1', 'lat' => -6.2, 'lon' => 106.8, 'device' => 'uji-browser', 'reason' => null, 'face_key' => null, 'cosine' => null, 'liveness' => null, 'has_thumb' => false, 'attrs' => null],
    ['id' => 900002, 'at' => '2026-09-18 17:02:00', 'date' => '2026-09-18', 'time' => '17:02:00', 'name' => 'Budi Uji Coba', 'code' => 'UJI-1', 'type' => 'pulang', 'status' => 'hadir', 'store' => 'TOKO UJI A', 'store_id' => 9001, 'city' => 'KOTA UJI', 'distance_m' => 40.0, 'radius_m' => 150, 'within' => true, 'acc' => 25, 'ip' => '10.0.0.1', 'lat' => -6.2, 'lon' => 106.8, 'device' => 'uji-browser', 'reason' => null, 'face_key' => null, 'cosine' => null, 'liveness' => null, 'has_thumb' => false, 'attrs' => null],
    ['id' => 900003, 'at' => '2026-09-18 09:30:00', 'date' => '2026-09-18', 'time' => '09:30:00', 'name' => 'Siti Luar Radius', 'code' => 'UJI-2', 'type' => 'masuk', 'status' => 'hadir', 'store' => 'TOKO UJI B', 'store_id' => 9002, 'city' => 'KOTA UJI', 'distance_m' => 480.0, 'radius_m' => 150, 'within' => false, 'acc' => 60, 'ip' => '10.0.0.2', 'lat' => -6.3, 'lon' => 106.9, 'device' => 'input manual admin', 'reason' => 'manual: uji', 'face_key' => null, 'cosine' => null, 'liveness' => null, 'has_thumb' => false, 'attrs' => null],
];
$data = array_merge($synthetic, $rows);

echo 'data uji: ' . count($data) . ' baris (' . count($synthetic) . " sintetis + " . count($rows) . " dari DB)\n";

$stub = '<script>window.__ERRORS__ = [];
window.addEventListener("error", function (e) { window.__ERRORS__.push("error: " + e.message + " @" + e.lineno); });
window.addEventListener("unhandledrejection", function (e) { window.__ERRORS__.push("reject: " + e.reason); });
window.__DATA__ = ' . json_encode($data, JSON_UNESCAPED_UNICODE) . ";\n"
    . 'window.fetch = function (url) { window.__ERRORS__.push("fetch: " + url);
  var payload = String(url).indexOf("/admin/absensi/data") === 0
    ? { ok: true, from: "all", to: "all", count: window.__DATA__.length, rows: window.__DATA__ }
    : [];
  return Promise.resolve({ ok: true, status: 200, json: function () { return Promise.resolve(payload); } });
};</script>';

$harnessAbsen = <<<'HTML'
<div id="uitest-result" style="display:none"></div>
<script>
(function () {
  var out = {};
  var D = window.__DATA__ || [];
  var nRows = function () { return document.querySelectorAll('#rows tr').length; };
  // baris yang benar-benar data (pesan "Tidak ada record" tidak dihitung)
  var visible = function () {
    return document.getElementById('rows').textContent.indexOf('Tidak ada record') !== -1 ? 0 : nRows();
  };
  var fire = function (id, ev) { var el = document.getElementById(id); el.dispatchEvent(new Event(ev, { bubbles: true })); };
  var setVal = function (id, v, ev) { var el = document.getElementById(id); el.value = v; fire(id, ev); };

  function run() {
    try {
      out.sidebarAda = !!document.querySelector('.side');
      out.menuAktif = document.querySelectorAll('.side .nav.on').length;
      out.judulHalaman = document.querySelector('h1').textContent.trim();
      out.dataUjiAda = (window.__DATA__ || []).length;
      out.fetchStub = String(window.fetch).indexOf('__DATA__') !== -1;
      out.srcInfo = document.getElementById('srcInfo').textContent.trim();
      out.rowsHtml = document.getElementById('rows').textContent.replace(/\s+/g, ' ').trim().slice(0, 70);
      out.errors = (window.__ERRORS__ || []).join(' | ');
      out.rowsAwal = visible();
      out.kpiKartu = document.querySelectorAll('#kpis .kpi').length;
      out.infoBaris = document.getElementById('pageInfo').textContent.trim();
      out.halaman = document.getElementById('pageNum').textContent.trim();

      // 1. pencarian client-side
      setVal('fQ', 'Budi Uji', 'input');
      out.cariBudi = visible();
      setVal('fQ', 'tidak-ada-xyz', 'input');
      out.cariKosong = document.getElementById('rows').textContent.indexOf('Tidak ada record') !== -1;
      setVal('fQ', '', 'input');

      // 2. filter tipe & status & geofence
      var expPulang = D.filter(function (r) { return r.type === 'pulang'; }).length;
      setVal('fType', 'pulang', 'change');
      out.filterPulang = visible(); out.filterPulangHarap = expPulang;
      setVal('fType', '', 'change');

      var expLuar = D.filter(function (r) { return r.within === false; }).length;
      setVal('fRadius', 'out', 'change');
      out.filterLuarRadius = visible(); out.filterLuarRadiusHarap = expLuar;
      setVal('fRadius', '', 'change');

      // 3. filter daerah + toko (pakai opsi asli halaman → cascade)
      var citySel = document.getElementById('fCity');
      var cityVal = citySel.options[1] ? citySel.options[1].value : '';
      setVal('fCity', cityVal, 'change');
      out.kotaDipilih = cityVal;
      out.filterKota = visible();
      out.filterKotaHarap = D.filter(function (r) { return r.city === cityVal; }).length;

      var storeSel = document.getElementById('fStore');
      var shown = Array.prototype.filter.call(storeSel.options, function (o) { return !o.hidden && o.value; });
      out.opsiTokoNampak = shown.length;
      var storeVal = shown.length ? shown[0].value : '';
      setVal('fStore', storeVal, 'change');
      out.filterToko = visible();
      out.filterTokoHarap = D.filter(function (r) { return String(r.store_id) === storeVal && r.city === cityVal; }).length;
      setVal('fStore', '', 'change');
      setVal('fCity', '', 'change');

      // 4. sorting jarak asc → baris pertama harus jarak terkecil
      document.querySelector('th[data-sort="distance_m"]').click();
      var sel = document.querySelector('#rows tr td:nth-child(6)');
      out.jarakTerkecilDulu = sel ? sel.textContent.trim() : '(tidak ada baris)';
      var min = Infinity;
      D.forEach(function (r) { if (r.distance_m != null) min = Math.min(min, r.distance_m); });
      out.jarakMinHarap = min === Infinity ? null : Math.round(min);

      // 5. modal detail
      var dBtn = document.querySelector('#rows [data-detail]');
      if (dBtn) dBtn.click();
      out.modalTerbuka = document.getElementById('detailBg').classList.contains('on');
      out.modalBaris = document.querySelectorAll('#dBody .mrow').length;
      out.modalJudul = document.getElementById('dTitle').textContent.trim();
      document.getElementById('dOk').click();
      out.modalTertutup = !document.getElementById('detailBg').classList.contains('on');

      // 6. export Excel (blob + nama file)
      var blobBytes = 0, unduhNama = '';
      var realCreate = URL.createObjectURL, realClick = HTMLAnchorElement.prototype.click;
      URL.createObjectURL = function (b) { blobBytes = b.size; return 'blob:uji'; };
      HTMLAnchorElement.prototype.click = function () { unduhNama = this.download; };
      document.getElementById('btnXls').click();
      URL.createObjectURL = realCreate; HTMLAnchorElement.prototype.click = realClick;
      out.excelByte = blobBytes; out.excelNama = unduhNama;

      // 7. export PDF (window.open di-stub)
      var tertulis = '';
      var realOpen = window.open;
      window.open = function () {
        return { document: { write: function (h) { tertulis += h; }, close: function () {} } };
      };
      document.getElementById('btnPdf').click();
      window.open = realOpen;
      out.pdfAdaJudul = tertulis.indexOf('REKAP ABSEN MASUK') !== -1;
      out.pdfAdaBaris = tertulis.indexOf('Budi Uji Coba') !== -1;
      out.pdfAdaAutoPrint = tertulis.indexOf('window.print()') !== -1;
      out.pdfByte = tertulis.length;

      // 8. modal input manual
      document.getElementById('btnManual').click();
      out.manualTerbuka = document.getElementById('manualBg').classList.contains('on');
      out.manualTanggalTerisi = !!(document.getElementById('mDate').value && document.getElementById('mTime').value);
      document.getElementById('mCancel').click();
    } catch (e) {
      out.err = String(e);
    }
    document.getElementById('uitest-result').textContent = JSON.stringify(out);
  }
  setTimeout(run, 900);
})();
</script>
HTML;

$harnessDash = <<<'HTML'
<div id="uitest-result" style="display:none"></div>
<script>
(function () {
  var out = {};
  setTimeout(function () {
    try {
      out.sidebarAda = !!document.querySelector('.side');
      out.menuAktif = document.querySelectorAll('.side .nav.on').length;
      out.judulHalaman = document.querySelector('h1').textContent.trim();
      out.kpiKartu = document.querySelectorAll('.kpi').length;
      out.batangGrafik = document.querySelectorAll('.chart .day').length;
      out.tabelAda = document.querySelectorAll('section.card table').length;
      out.barisTabel = document.querySelectorAll('section.card tbody tr').length;
      out.lebarSidebar = document.querySelector('.side').getBoundingClientRect().width;
      out.kontenTergeser = document.querySelector('.main').getBoundingClientRect().left;
    } catch (e) {
      out.err = String(e);
    }
    document.getElementById('uitest-result').textContent = JSON.stringify(out);
  }, 500);
})();
</script>
HTML;

$shots = realpath(__DIR__ . '/../storage/app');
$tmp = sys_get_temp_dir();
$edge = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';

/** Jalanin Edge headless: HTML + harness → [hasil JSON, path screenshot]. */
$run = function (string $html, string $harness, string $name, int $w, int $h) use ($tmp, $shots, $edge) {
    $file = $tmp . DIRECTORY_SEPARATOR . "admin-uitest-$name.html";
    // Sisipkan harness sebelum </body> TERAKHIR — halaman ini punya literal '</body>'
    // di dalam string JS (export Excel/PDF), jadi str_replace biasa bikin JS rusak.
    $at = strrpos($html, '</body>');
    $html = $at === false ? $html . $harness : substr($html, 0, $at) . $harness . "\n" . substr($html, $at);
    file_put_contents($file, $html);
    $png = $shots . DIRECTORY_SEPARATOR . "adminuitest-$name.png";
    $profile = $tmp . DIRECTORY_SEPARATOR . 'edge-adminuitest-profile';

    $cmd = escapeshellarg($edge) . ' --headless=new --disable-gpu --hide-scrollbars --no-first-run'
        . ' --no-default-browser-check --user-data-dir=' . escapeshellarg($profile)
        . ' --window-size=' . $w . ',' . $h . ' --virtual-time-budget=6000'
        . ' --screenshot=' . escapeshellarg($png)
        . ' --dump-dom ' . escapeshellarg('file:///' . str_replace('\\', '/', $file)) . ' 2>NUL';

    $dom = (string) shell_exec($cmd);
    preg_match('#<div id="uitest-result"[^>]*>(.*?)</div>#s', $dom, $m);
    $res = json_decode(html_entity_decode($m[1] ?? '', ENT_QUOTES), true);

    return [is_array($res) ? $res : [], $png];
};

$fail = 0;
$check = function (string $label, bool $ok, $got = '') use (&$fail) {
    echo ($ok ? 'OK   ' : 'GAGAL') . " — $label" . ($got === '' ? '' : " ($got)") . "\n";
    if (! $ok) $fail++;
};
$short = fn ($v) => is_bool($v) ? var_export($v, true) : (string) $v;

// ---------- 1. halaman riwayat absen (filter/pencarian/export) ----------
echo "=== riwayat absen (Edge headless, 1400x1100) ===\n";
$htmlAbsen = $get('/admin/absensi')->getContent();
// Sisipkan stub fetch setelah </head> PERTAMA (layout) — halaman punya literal
// '<title>' & '</head>' juga di dalam string JS export PDF, jadi jangan str_replace.
$head = strpos($htmlAbsen, '</head>');
if ($head !== false) {
    $htmlAbsen = substr($htmlAbsen, 0, $head) . $stub . substr($htmlAbsen, $head);
}
[$a, $pngA] = $run($htmlAbsen, $harnessAbsen, 'riwayat', 1400, 1100);
if (! empty($a['err'])) { $check('tanpa error JS', false, $a['err']); }
foreach ($a as $k => $v) echo '  ' . str_pad($k, 20) . ': ' . $short($v) . "\n";

$check('sidebar tampil', ! empty($a['sidebarAda']));
$check('menu aktif disorot', ($a['menuAktif'] ?? 0) === 1, 'menuAktif='.($a['menuAktif'] ?? 0));
$check('tabel terisi dari endpoint JSON', ($a['rowsAwal'] ?? 0) >= 4, 'baris='.($a['rowsAwal'] ?? 0));
$check('KPI client-side lengkap (6 kartu)', ($a['kpiKartu'] ?? 0) === 6, 'kartu='.($a['kpiKartu'] ?? 0));
$check('pencarian nama menyaring baris', ($a['cariBudi'] ?? 0) === 2, 'baris='.($a['cariBudi'] ?? 0));
$check('pencarian tanpa hasil → pesan kosong', ! empty($a['cariKosong']));
$check('filter tipe=pulang', ($a['filterPulang'] ?? -1) === ($a['filterPulangHarap'] ?? -2), ($a['filterPulang'] ?? '?').' vs harap '.($a['filterPulangHarap'] ?? '?'));
$check('filter geofence luar radius', ($a['filterLuarRadius'] ?? -1) === ($a['filterLuarRadiusHarap'] ?? -2) && ($a['filterLuarRadius'] ?? 0) >= 1, ($a['filterLuarRadius'] ?? '?').' vs harap '.($a['filterLuarRadiusHarap'] ?? '?'));
$check('filter daerah (kota)', ($a['filterKota'] ?? -1) === ($a['filterKotaHarap'] ?? -2), 'baris='.($a['filterKota'] ?? '?').' vs harap '.($a['filterKotaHarap'] ?? '?').' (kota '.($a['kotaDipilih'] ?? '').')');
$check('filter toko ikut daerah (cascade)', ($a['filterToko'] ?? -1) === ($a['filterTokoHarap'] ?? -2), 'baris='.($a['filterToko'] ?? '?').' vs harap '.($a['filterTokoHarap'] ?? '?'));
$check('opsi toko menyesuaikan daerah', ($a['opsiTokoNampak'] ?? 0) >= 1, 'opsi='.($a['opsiTokoNampak'] ?? 0));
$check('sort kolom jarak (asc = terkecil dulu)', str_contains((string) ($a['jarakTerkecilDulu'] ?? ''), '±'.($a['jarakMinHarap'] ?? -1).' m'), 'sel='.($a['jarakTerkecilDulu'] ?? '').' harap ±'.($a['jarakMinHarap'] ?? '?'));
$check('modal detail terbuka + isi lengkap', ! empty($a['modalTerbuka']) && ($a['modalBaris'] ?? 0) >= 18, 'baris modal='.($a['modalBaris'] ?? 0));
$check('modal detail bisa ditutup', ! empty($a['modalTertutup']));
$check('export Excel bikin file .xls berisi data', ($a['excelByte'] ?? 0) > 1000 && str_ends_with((string) ($a['excelNama'] ?? ''), '.xls'), ($a['excelByte'] ?? 0).' byte, '.($a['excelNama'] ?? ''));
$check('export PDF: judul + baris + auto print', ! empty($a['pdfAdaJudul']) && ! empty($a['pdfAdaBaris']) && ! empty($a['pdfAdaAutoPrint']), ($a['pdfByte'] ?? 0).' byte');
$check('modal input manual kebuka + tanggal terisi', ! empty($a['manualTerbuka']) && ! empty($a['manualTanggalTerisi']));

// ---------- 2. halaman ringkasan ----------
echo "\n=== ringkasan hari ini (Edge headless, 1500x1200) ===\n";
[$d, $pngD] = $run($get('/admin')->getContent(), $harnessDash, 'ringkasan', 1500, 1200);
if (! empty($d['err'])) { $check('tanpa error JS', false, $d['err']); }
foreach ($d as $k => $v) echo '  ' . str_pad($k, 20) . ': ' . $short($v) . "\n";

$check('sidebar tampil', ! empty($d['sidebarAda']));
$check('konten tergeser 248px (sidebar fixed)', ($d['kontenTergeser'] ?? 0) >= 240, 'left='.($d['kontenTergeser'] ?? '?'));
$check('7 kartu KPI', ($d['kpiKartu'] ?? 0) === 7, 'kartu='.($d['kpiKartu'] ?? 0));
$check('grafik tren 14 hari', ($d['batangGrafik'] ?? 0) === 14, 'bar='.($d['batangGrafik'] ?? 0));
$check('tabel rekap tampil', ($d['tabelAda'] ?? 0) >= 1, 'tabel='.($d['tabelAda'] ?? 0));

echo "\nscreenshot: $pngA " . (is_file($pngA) ? '(' . filesize($pngA) . " byte)\n" : "TIDAK ADA\n");
echo "screenshot: $pngD " . (is_file($pngD) ? '(' . filesize($pngD) . " byte)\n" : "TIDAK ADA\n");
echo $fail === 0 ? "\nHASIL: SEMUA LULUS\n" : "\nHASIL: $fail UJI GAGAL\n";
