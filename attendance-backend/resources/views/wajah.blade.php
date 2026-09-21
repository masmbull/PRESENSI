@extends('layouts.admin')

@section('title', 'Kelola wajah & karyawan')
@section('heading', 'Kelola wajah & karyawan')
@section('sub', 'Master data kota / toko / karyawan + pendaftaran wajah ke engine')

@section('actions')
  <a class="btn ghost" href="/" target="_blank" rel="noopener">📱 Halaman absen SPG</a>
  <a class="btn sky" href="{{ route('admin.absensi') }}">🗂️ Riwayat absen</a>
@endsection

@push('head')
<style>
/* ---------- /kelola-wajah ---------- */
.kw-tabs{display:flex;gap:5px;background:var(--card2);border:1px solid var(--line);border-radius:var(--r2);padding:4px;margin-bottom:14px;overflow-x:auto}
.kw-tab{flex:1;min-width:112px;padding:8px 12px;text-align:center;font-size:12.5px;font-weight:600;color:var(--dim);border:1px solid transparent;border-radius:8px;cursor:pointer;white-space:nowrap;transition:color .16s,background .16s;user-select:none}
.kw-tab:hover{color:var(--txt);background:rgba(151,181,217,.08)}
.kw-tab.on{color:var(--txt);background:var(--card);border-color:var(--line2)}
.kw-tab.on .n{background:rgba(52,211,153,.18);color:var(--acc)}
.kw-tab .n{display:inline-block;min-width:18px;margin-left:5px;padding:0 5px;border-radius:999px;background:rgba(151,181,217,.14);font-size:10px;color:var(--dim)}
.kw-pane{display:none}
.kw-pane.on{display:block}
.kw-cols{display:grid;grid-template-columns:1fr;gap:14px;align-items:start}
.kw-cols-2{display:grid;grid-template-columns:1fr;gap:14px;align-items:start}
/* min-width:0 → tabel ber-scroll di dalam kartu, gak melebarin halaman (mobile) */
.kw-cols>div,.kw-cols-2>div{min-width:0}
.table-wrap,.emp-row .nm{min-width:0}
@media(min-width:1080px){.kw-cols{grid-template-columns:minmax(330px,1fr) minmax(430px,1.3fr)}.kw-cols-2{grid-template-columns:1fr 1fr}}
.fld{display:block;margin-bottom:11px}
.flbl{display:block;font-size:9.5px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--dim);margin-bottom:6px}
.fld input,.fld select,.tools input,.tools select{width:100%;padding:11px 13px;border-radius:var(--r2);border:1px solid var(--line);background:var(--card2);color:var(--txt);font:inherit;font-size:13.5px}
.fld input:focus,.fld select:focus,.tools input:focus,.tools select:focus{outline:none;border-color:rgba(56,189,248,.55);box-shadow:0 0 0 3px rgba(56,189,248,.12)}
.fld input::placeholder,.tools input::placeholder{color:#4a5c70}
.fld select option{background:var(--card2);color:var(--txt)}
.g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:9px}
.hint{font-size:11.5px;color:var(--dim);line-height:1.65;margin-top:10px}
.hint b{color:#a7f3d0;font-weight:600}
.kpi{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:14px}
.kpi .b{background:var(--card);border:1px solid var(--line);border-radius:var(--r2);padding:12px 13px}
.kpi .n{font-size:21px;font-weight:800;line-height:1.1;font-variant-numeric:tabular-nums}
.kpi .l{font-size:9.5px;color:var(--dim);letter-spacing:.09em;text-transform:uppercase;margin-top:4px;font-weight:700}
.kpi .g .n{color:var(--acc)}.kpi .r .n{color:var(--warn)}.kpi .s .n{color:var(--sky)}
.tools{display:grid;grid-template-columns:1fr;gap:9px;margin-bottom:12px}
@media(min-width:720px){.tools{grid-template-columns:1.4fr 1fr 1fr}}
.emp-list{display:flex;flex-direction:column;gap:8px;min-height:0;padding-right:2px}
.smallpager{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:10px;padding-top:10px;border-top:1px solid rgba(151,181,217,.1);font-size:11.5px;color:var(--dim2)}
.smallpager button{min-width:30px;height:28px;padding:0 9px;border-radius:8px;border:1px solid var(--line);background:var(--card2);color:var(--dim);font:inherit;font-size:13px;line-height:1;font-weight:700;cursor:pointer;transition:color .15s,border-color .15s}
.smallpager button:hover:not(:disabled){color:var(--txt);border-color:var(--line2)}
.smallpager button:disabled{opacity:.4;cursor:not-allowed}
.emp-row{display:flex;align-items:center;gap:11px;padding:10px 12px;border:1px solid var(--line);border-radius:10px;background:var(--card2);cursor:pointer;transition:border-color .16s,background .16s}
.emp-row:hover{border-color:rgba(52,211,153,.4);background:rgba(52,211,153,.07)}
.emp-row.static{cursor:default}
.emp-row.static:hover{border-color:var(--line);background:var(--card2)}
.emp-row .av{width:32px;height:32px;flex:none;border-radius:9px;background:rgba(52,211,153,.16);color:#a7f3d0;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;border:1px solid rgba(52,211,153,.3)}
.emp-row .av.no{background:rgba(151,181,217,.12);color:var(--dim);border-color:var(--line)}
.emp-row .nm{min-width:0;flex:1}
.emp-row .nm b{display:block;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.emp-row .nm span{display:block;font-size:10.5px;color:var(--dim);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.badge{font-size:9px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;padding:3px 8px;border-radius:999px;border:1px solid transparent;flex:none}
.b-ok{background:rgba(52,211,153,.14);color:#6ee7b7;border-color:rgba(52,211,153,.3)}
.b-no{background:rgba(151,181,217,.12);color:var(--dim);border-color:var(--line)}
.rowbtn{display:flex;gap:9px;flex-wrap:wrap;margin:11px 0}
.cam-wrap{position:relative;border:1px solid var(--line);border-radius:var(--r);overflow:hidden;background:#020408;margin-bottom:11px}
.cam-wrap video{width:100%;max-height:300px;object-fit:cover;display:block;transform:scaleX(-1)}
#preview{width:100%;max-height:260px;object-fit:cover;border:1px solid var(--line);border-radius:var(--r);display:none;margin-bottom:11px}
.face-guide{display:flex;gap:10px;align-items:flex-start;font-size:11.5px;color:var(--dim);line-height:1.6;background:rgba(56,189,248,.07);border:1px solid rgba(56,189,248,.22);border-radius:var(--r2);padding:11px 12px;margin-bottom:13px}
.face-guide .ico{font-size:17px;flex:none}
.card.danger{border-color:rgba(248,113,113,.32)}
.card.narrow{max-width:660px}
</style>
@endpush
@section('content')
<div class="kpi">
  <div class="b s"><div class="n" id="kKota">{{ $stats['cities'] }}</div><div class="l">Kota</div></div>
  <div class="b s"><div class="n" id="kToko">{{ $stats['stores'] }}</div><div class="l">Toko</div></div>
  <div class="b"><div class="n" id="kEmp">{{ $stats['employees'] }}</div><div class="l">Karyawan</div></div>
  <div class="b g"><div class="n" id="kLink">{{ $stats['linked'] }}</div><div class="l">Wajah terdaftar</div></div>
  <div class="b r"><div class="n" id="kNo">{{ $stats['no_face'] }}</div><div class="l">Belum ada wajah</div></div>
  <div class="b s"><div class="n mono" style="font-size:15px">{{ (int) config('faceid.radius') }} m</div><div class="l">Radius absen default</div></div>
</div>

<div class="kw-tabs" id="tabs">
  <div class="kw-tab on" data-p="pMaster">🏘️ Master data <span class="n" id="tMaster">{{ $stats['employees'] }}</span></div>
  <div class="kw-tab" data-p="pWajah">🙂 Daftar wajah <span class="n" id="tWajah">{{ $stats['no_face'] }}</span></div>
  <div class="kw-tab" data-p="pDanger">⚠️ Zona bahaya</div>
</div>

<!-- ================== MASTER DATA ================== -->
<div class="kw-pane on" id="pMaster">
  <div class="kw-cols">
    <div>
      <section class="card">
        <h2><span>1 · Tambah kota</span></h2>
        <label class="fld"><span class="flbl">Nama kota</span>
          <input id="cityName" maxlength="80" placeholder="cth: Surabaya" autocomplete="off">
        </label>
        <button class="btn" id="btnKota" type="button">Simpan kota</button>
      </section>

      <section class="card">
        <h2><span>2 · Tambah toko</span></h2>
        <label class="fld"><span class="flbl">Kota (pilih yang ada, atau ketik baru)</span>
          <select id="locCity"><option value="">-- pilih kota --</option></select>
          <input id="locCityNew" maxlength="80" placeholder="atau nama kota baru…" autocomplete="off" style="margin-top:8px">
        </label>
        <label class="fld"><span class="flbl">Nama toko</span>
          <input id="locStore" maxlength="120" placeholder="cth: MITO Kalimalang" autocomplete="off">
        </label>
        <label class="fld"><span class="flbl">Alamat (opsional)</span>
          <input id="locAddr" maxlength="255" placeholder="cth: Jl. Basuki Rahmat No. 12" autocomplete="off">
        </label>
        <div class="g3">
          <label class="fld"><span class="flbl">Latitude</span><input id="locLat" type="number" step="any" placeholder="-6.0883"></label>
          <label class="fld"><span class="flbl">Longitude</span><input id="locLon" type="number" step="any" placeholder="106.7439"></label>
          <label class="fld"><span class="flbl">Radius (m)</span><input id="locRadius" type="number" min="10" max="5000" placeholder="150"></label>
        </div>
        <button class="btn" id="btnLokasi" type="button">Simpan toko</button>
        <div class="hint">Pinpoint ambil dari Google Maps (klik kanan → koordinat). Radius default <b>{{ (int) config('faceid.radius') }} m</b> kalau dikosongkan.</div>
      </section>

      <section class="card">
        <h2><span>3 · Tambah karyawan</span></h2>
        <label class="fld"><span class="flbl">Nama karyawan</span>
          <input id="empName" maxlength="120" placeholder="cth: Devi Isvaradilla Agrully" autocomplete="off">
        </label>
        <label class="fld"><span class="flbl">Toko penempatan</span>
          <select id="empStore"><option value="">-- pilih toko --</option></select>
        </label>
        <button class="btn" id="btnKaryawan" type="button">Tambah karyawan</button>
        <div class="hint">Nama sama di toko yang sama otomatis digabung — <b>tidak dobel</b>. Nama sama di toko berbeda tetap dibuat terpisah.</div>
      </section>
    </div>

    <div>
      <section class="card">
        <h2><span>Daftar toko</span><span class="pill p-mute" id="cStores">0</span></h2>
        <div class="tools" style="grid-template-columns:1fr">
          <input id="storeSearch" placeholder="cari toko / kota / alamat… contoh: Semarang, Jalan, ALASKA" autocomplete="off">
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Toko</th><th>Kota</th><th>Alamat</th><th>Koordinat</th><th>Karyawan</th><th></th></tr></thead>
            <tbody id="storeRows"><tr><td colspan="6" class="empty">Memuat data…</td></tr></tbody>
          </table>
        </div>
        <div class="smallpager">
          <span id="storePageInfo">—</span>
          <span style="display:flex;gap:6px">
            <button type="button" id="storePrev">‹</button>
            <button type="button" id="storeNext">›</button>
          </span>
        </div>
      </section>

      <section class="card">
        <h2><span>Daftar karyawan</span><span class="pill p-mute" id="cShown">0 tampil</span></h2>
        <div class="tools">
          <input id="empSearch" placeholder="cari nama karyawan…" autocomplete="off">
          <select id="fltStore"><option value="">semua toko</option></select>
          <select id="fltActive"><option value="1">karyawan aktif</option><option value="0">nonaktif</option><option value="">semua status</option></select>
        </div>
        <div class="emp-list" id="empList"><div class="empty">Memuat data…</div></div>
        <div class="smallpager">
          <span id="empPageInfo">—</span>
          <span style="display:flex;gap:6px">
            <button type="button" id="empPrev">‹</button>
            <button type="button" id="empNext">›</button>
          </span>
        </div>
        <div class="hint">Klik satu baris → langsung pindah ke tab <b>Daftar Wajah</b> dengan karyawan itu terpilih. Tombol <b>Ubah</b> buat ganti nama, kode, atau pindah toko.</div>
      </section>
    </div>
  </div>
</div>
<!-- ================== DAFTAR WAJAH ================== -->
<div class="kw-pane" id="pWajah">
  <div class="kw-cols">
    <section class="card">
      <h2><span>1 · Pilih karyawan</span></h2>
      <label class="fld"><span class="flbl">Karyawan</span>
        <select id="faceEmp"><option value="">-- pilih karyawan --</option></select>
      </label>
      <div class="face-guide">
        <div class="ico">🙂</div>
        <div><b>Panduan foto:</b> wajah depan, terang, tanpa masker / kacamata hitam, jarak ±50 cm. Satu orang boleh didaftarkan beberapa foto.</div>
      </div>
      <h2><span>2 · Foto wajah</span></h2>
      <label class="fld"><span class="flbl">Dari galeri / file</span>
        <input id="photo" type="file" accept="image/jpeg,image/png,image/webp" capture="user">
      </label>
      <div class="rowbtn">
        <button class="btn ghost" id="btnCam" type="button">📷 Buka kamera</button>
        <button class="btn ghost" id="btnSnap" type="button" disabled>📸 Jepret</button>
      </div>
      <div class="cam-wrap" id="camBox" style="display:none"><video id="cam" playsinline muted></video></div>
      <img id="preview" alt="Pratinjau foto wajah">
      <button class="btn" id="btnDaftar" type="button">Daftarkan ke engine</button>
      <div class="hint">Nama di engine otomatis <b>“nama — toko”</b>, jadi dua SPG bernama sama di toko berbeda tetap punya slot wajah masing-masing.</div>
    </section>

    <section class="card">
      <h2><span>Belum punya wajah</span><span class="pill p-warn" id="cNoFace">0 orang</span></h2>
      <div class="emp-list" id="noFaceList"><div class="empty">Memuat data…</div></div>
      <div class="smallpager">
        <span id="noFacePageInfo">—</span>
        <span style="display:flex;gap:6px">
          <button type="button" id="noFacePrev">‹</button>
          <button type="button" id="noFaceNext">›</button>
        </span>
      </div>
      <div class="hint">Klik nama di atas untuk langsung memilihnya di form pendaftaran wajah.</div>
    </section>
  </div>
</div>

<!-- ================== BAHAYA ================== -->
<div class="kw-pane" id="pDanger">
  <section class="card danger narrow">
    <h2><span>⚠️ Zona bahaya · hapus 1 wajah</span></h2>
    <p class="hint" style="margin-top:0">Hapus <b>face ID satu karyawan</b> dari engine — karyawan, toko, dan riwayat absennya <b>tetap ada</b>. Berguna kalau wajah salah orang / perlu didaftarkan ulang.</p>
    <label class="fld"><span class="flbl">Karyawan</span>
      <select id="delFaceEmp"><option value="">-- pilih karyawan --</option></select>
    </label>
    <button class="btn warn" id="btnHapusSatu" type="button">Hapus face ID karyawan ini</button>
  </section>

  <section class="card danger narrow">
    <h2><span>⚠️ Zona bahaya · hapus semua</span></h2>
    <p class="hint" style="margin-top:0">Menghapus <b>semua wajah</b> di engine dan melepas <b>face_key</b> seluruh karyawan. Data karyawan, toko, kota, dan riwayat absen <b>tidak</b> ikut terhapus.</p>
    <label class="fld"><span class="flbl">Ketik <b>HAPUS SEMUA WAJAH</b> buat konfirmasi</span>
      <input id="delAllConfirm" placeholder="HAPUS SEMUA WAJAH" autocomplete="off">
    </label>
    <button class="btn warn" id="btnHapus" type="button">Hapus semua wajah engine</button>
  </section>
</div>

<canvas id="shot" style="display:none"></canvas>

<!-- modal ubah toko -->
<div class="modal-bg" id="stBg">
  <div class="modal">
    <h3><span>Ubah toko</span><button type="button" class="close" id="stClose" aria-label="Tutup">×</button></h3>
    <input type="hidden" id="stId">
    <div class="fgrid">
      <label class="fld span2"><span>Nama toko</span><input id="stName" maxlength="120" autocomplete="off"></label>
      <label class="fld"><span>Kota</span><select id="stCity"></select></label>
      <label class="fld"><span>Radius (m)</span><input id="stRadius" type="number" min="10" max="5000" placeholder="150"></label>
      <label class="fld span2"><span>Alamat</span><input id="stAddr" maxlength="200" autocomplete="off" placeholder="cth: Jl. Basuki Rahmat No. 12"></label>
      <label class="fld"><span>Latitude</span><input id="stLat" type="number" step="any"></label>
      <label class="fld"><span>Longitude</span><input id="stLon" type="number" step="any"></label>
    </div>
    <div class="mfoot">
      <button class="btn ghost" type="button" id="stCancel">Batal</button>
      <button class="btn" type="button" id="stSave">Simpan perubahan</button>
    </div>
  </div>
</div>

<!-- modal ubah karyawan -->
<div class="modal-bg" id="emBg">
  <div class="modal">
    <h3><span>Ubah karyawan</span><button type="button" class="close" id="emClose" aria-label="Tutup">×</button></h3>
    <input type="hidden" id="emId">
    <div class="fgrid">
      <label class="fld span2"><span>Nama karyawan</span><input id="emName" maxlength="120" autocomplete="off"></label>
      <label class="fld"><span>Kode karyawan (opsional)</span><input id="emCode" maxlength="40" autocomplete="off" placeholder="cth: SPG-001"></label>
      <label class="fld"><span>Toko penempatan</span><select id="emStore"></select></label>
    </div>
    <p class="tiny" style="margin:12px 0 0">Wajah yang sudah didaftarkan tetap nyambung — yang berubah cuma data karyawannya. Kalau pindah toko, nama slot di engine dibiarkan seperti saat pendaftaran (keterangan di absen tetap ikut data di sini).</p>
    <div class="mfoot">
      <button class="btn ghost" type="button" id="emCancel">Batal</button>
      <button class="btn" type="button" id="emSave">Simpan perubahan</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  'use strict';
  const $ = (id) => document.getElementById(id);
  const D = { cities: [], stores: [], employees: [] };
  // 6 baris/halaman biar daftar toko/karyawan fit 1 layar tanpa scroll.
  const PER = 6;
  const pg = { store: 1, emp: 1, noFace: 1 };
  let stream = null, imageB64 = '';

  /* ---------- tab ---------- */
  $('tabs').addEventListener('click', (e) => {
    const t = e.target.closest('.kw-tab'); if (!t) return;
    document.querySelectorAll('.kw-tab').forEach((x) => x.classList.toggle('on', x === t));
    document.querySelectorAll('.kw-pane').forEach((p) => p.classList.toggle('on', p.id === t.dataset.p));
    stopCam();
  });
  function goTab(paneId) {
    const tab = document.querySelector('.kw-tab[data-p="' + paneId + '"]');
    if (tab) tab.click();
  }

  /* ---------- load master data ---------- */
  async function loadAll() {
    const hdr = { headers: { Accept: 'application/json' } };
    const [c, s, e] = await Promise.all([
      fetch('/kelola-wajah/kota', hdr).then((r) => r.json()),
      fetch('/kelola-wajah/toko', hdr).then((r) => r.json()),
      fetch('/kelola-wajah/karyawan', hdr).then((r) => r.json()),
    ]);
    D.cities = Array.isArray(c) ? c : [];
    D.stores = Array.isArray(s) ? s : [];
    D.employees = Array.isArray(e) ? e : [];
    render();
  }

  function storeName(id) {
    const s = D.stores.find((x) => x.id === id);
    return s ? s.name : null;
  }
  function empLabel(emp) {
    const st = storeName(emp.store_id);
    return st ? emp.name + ' — ' + st : emp.name;
  }

  /* ---------- pagination kecil (maks PER baris per halaman) ---------- */
  function slice(items, which) {
    const pages = Math.max(1, Math.ceil(items.length / PER));
    if (pg[which] > pages) pg[which] = pages;
    if (pg[which] < 1) pg[which] = 1;
    return items.slice((pg[which] - 1) * PER, pg[which] * PER);
  }
  function paintPager(which, infoId, prevId, nextId, shown, total) {
    $(infoId).textContent = total === 0 ? 'tidak ada data'
      : 'hal ' + pg[which] + ' / ' + Math.max(1, Math.ceil(total / PER)) + ' · ' + shown + ' dari ' + total;
    $(prevId).disabled = pg[which] <= 1;
    $(nextId).disabled = pg[which] >= Math.max(1, Math.ceil(total / PER));
  }
  function fillSelect(sel, items, placeholder) {
    const cur = sel.value;
    sel.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = ''; ph.textContent = placeholder;
    sel.appendChild(ph);
    items.forEach((it) => {
      const o = document.createElement('option');
      o.value = it.id; o.textContent = it.label;
      sel.appendChild(o);
    });
    if ([...sel.options].some((o) => o.value === cur)) sel.value = cur;
  }
  function render() {
    // karyawan nonaktif gak ikut dihitung "belum punya wajah" (mereka udah gak nagih)
    const noFace = D.employees.filter((e) => !e.face_key && e.active);
    $('kKota').textContent = D.cities.length;
    $('kToko').textContent = D.stores.length;
    $('kEmp').textContent = D.employees.length;
    $('kLink').textContent = D.employees.length - noFace.length;
    $('kNo').textContent = noFace.length;
    $('tMaster').textContent = D.employees.length;
    $('tWajah').textContent = noFace.length;
    $('cNoFace').textContent = noFace.length + ' orang';
    $('cStores').textContent = D.stores.length + ' toko';

    fillSelect($('locCity'), D.cities.map((c) => ({ id: c.id, label: c.name })), '-- pilih kota --');
    const storeOpts = D.stores.map((s) => ({ id: s.id, label: s.name + ' (' + (s.city?.name ?? '?') + ')' }));
    fillSelect($('empStore'), storeOpts, '-- pilih toko --');
    fillSelect($('fltStore'), storeOpts, 'semua toko');
    fillSelect($('faceEmp'), D.employees.map((e) => ({ id: e.id, label: empLabel(e) })), '-- pilih karyawan --');
    fillSelect($('delFaceEmp'), D.employees.filter((e) => e.face_key).map((e) => ({ id: e.id, label: empLabel(e) })), '-- pilih karyawan --');

    renderStores();

    renderEmpList();
    renderNoFace(noFace);
  }

  function renderStores() {
    const q = $('storeSearch').value.trim().toLowerCase();
    const list = D.stores.filter((s) => !q
      || s.name.toLowerCase().includes(q)
      || (s.city?.name ?? '').toLowerCase().includes(q)
      || (s.address ?? '').toLowerCase().includes(q));
    const rowsNow = slice(list, 'store');
    const tb = $('storeRows');
    tb.innerHTML = list.length ? '' : '<tr><td colspan="6" class="empty">Tidak ada toko yang cocok.</td></tr>';
    rowsNow.forEach((s) => {
      const tr = document.createElement('tr');
      tr.innerHTML = '<td><b>' + esc(s.name) + '</b>' + (s.radius_m ? ' <span class="pill p-mute">' + s.radius_m + ' m</span>' : '') + '</td>'
        + '<td class="dim">' + esc(s.city?.name ?? '-') + '</td>'
        + '<td class="dim">' + (s.address ? esc(s.address) : '<span style="color:var(--dim2)">belum diisi</span>') + '</td>'
        + '<td class="mono dim" style="font-size:11px">' + (+s.lat).toFixed(5) + ', ' + (+s.lon).toFixed(5) + '</td>'
        + '<td><span class="pill p-sky">' + (s.employees_count ?? 0) + '</span></td>'
        + '<td><button type="button" class="mini" data-editstore="' + s.id + '">Ubah</button> <button type="button" class="mini warn" data-delstore="' + s.id + '">Hapus</button></td>';
      tb.appendChild(tr);
    });
    paintPager('store', 'storePageInfo', 'storePrev', 'storeNext', rowsNow.length, list.length);
  }

  /* ---------- ubah toko (nama / kota / alamat / koordinat / radius) ---------- */
  function openStore(id) {
    const s = D.stores.find((x) => x.id === id);
    if (!s) return;
    $('stId').value = s.id;
    $('stName').value = s.name;
    $('stRadius').value = s.radius_m ?? '';
    $('stAddr').value = s.address ?? '';
    $('stLat').value = s.lat;
    $('stLon').value = s.lon;
    fillSelect($('stCity'), D.cities.map((c) => ({ id: c.id, label: c.name })), '-- pilih kota --');
    $('stCity').value = String(s.city_id);
    $('stBg').classList.add('on');
    $('stName').focus();
  }
  function closeStore() { $('stBg').classList.remove('on'); }
  $('stClose').onclick = closeStore;
  $('stCancel').onclick = closeStore;
  $('stBg').addEventListener('click', (ev) => { if (ev.target === $('stBg')) closeStore(); });
  $('storeRows').addEventListener('click', (ev) => {
    const b = ev.target.closest('[data-editstore]');
    if (b) return openStore(parseInt(b.dataset.editstore, 10));
    const d = ev.target.closest('[data-delstore]');
    if (d) return hapusToko(parseInt(d.dataset.delstore, 10));
  });

  /* hapus toko — cuma jalan kalau toko kosong (tanpa karyawan & riwayat absen) */
  async function hapusToko(id) {
    const s = D.stores.find((x) => x.id === id);
    if (!s) return;
    if (!confirm('Hapus toko "' + s.name + '"?\nCuma bisa kalau toko kosong: gak ada karyawan & gak ada riwayat absen.')) return;
    try {
      const j = await api('/kelola-wajah/toko/' + id + '/hapus', {});
      toast(j.message);
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
  }
  $('stSave').onclick = async () => {
    const id = $('stId').value;
    const body = {
      city_id: $('stCity').value ? parseInt($('stCity').value, 10) : null,
      store: $('stName').value.trim(),
      address: $('stAddr').value.trim() || null,
      lat: parseFloat($('stLat').value),
      lon: parseFloat($('stLon').value),
      radius_m: $('stRadius').value ? parseInt($('stRadius').value, 10) : null,
    };
    if (!body.store) return toast('Nama toko wajib diisi.', 'err');
    if (!body.city_id) return toast('Pilih kota dulu.', 'err');
    if (Number.isNaN(body.lat) || Number.isNaN(body.lon)) return toast('Latitude & longitude wajib angka.', 'err');
    const btn = $('stSave');
    btn.disabled = true;
    try {
      await api('/kelola-wajah/lokasi/' + id, body);
      toast('Toko "' + body.store + '" diperbarui.', 'ok');
      closeStore();
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
    btn.disabled = false;
  };

  /* ---------- ubah karyawan (nama / kode / pindah toko) ---------- */
  function openEmp(id) {
    const e = D.employees.find((x) => x.id === id);
    if (!e) return;
    $('emId').value = e.id;
    $('emName').value = e.name;
    $('emCode').value = e.employee_code ?? '';
    fillSelect($('emStore'), D.stores.map((s) => ({ id: s.id, label: s.name + ' (' + (s.city?.name ?? '?') + ')' })), '-- tanpa toko --');
    $('emStore').value = e.store_id ? String(e.store_id) : '';
    $('emBg').classList.add('on');
    $('emName').focus();
  }
  function closeEmp() { $('emBg').classList.remove('on'); }
  $('emClose').onclick = closeEmp;
  $('emCancel').onclick = closeEmp;
  $('emBg').addEventListener('click', (ev) => { if (ev.target === $('emBg')) closeEmp(); });
  $('emSave').onclick = async () => {
    const id = $('emId').value;
    const body = {
      name: $('emName').value.trim(),
      employee_code: $('emCode').value.trim() || null,
      store_id: $('emStore').value ? parseInt($('emStore').value, 10) : null,
    };
    if (!body.name) return toast('Nama karyawan wajib diisi.', 'err');
    const btn = $('emSave');
    btn.disabled = true;
    try {
      await api('/kelola-wajah/karyawan/' + id, body);
      toast('Karyawan "' + body.name + '" diperbarui.', 'ok');
      closeEmp();
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
    btn.disabled = false;
  };

  $('storeSearch').oninput = () => { pg.store = 1; renderStores(); };

  /* ---------- nonaktifkan / aktifkan karyawan (resign tanpa hapus riwayat) ---------- */
  async function toggleEmp(id, active) {
    const e = D.employees.find((x) => x.id === id);
    const label = e ? empLabel(e) : 'karyawan ini';
    if (!confirm((active ? 'Aktifkan ' : 'Nonaktifkan ') + label + '?\n'
      + (active ? 'Muncul lagi di halaman absen SPG.' : 'Gak muncul lagi di halaman absen SPG, riwayat & wajahnya tetap.'))) return;
    try {
      const j = await api('/kelola-wajah/karyawan/' + id + '/aktif', { active });
      toast(j.message);
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
  }

  function renderEmpList() {
    const q = $('empSearch').value.trim().toLowerCase();
    const f = $('fltStore').value;
    const a = $('fltActive').value;
    const list = D.employees.filter((e) =>
      (!q || e.name.toLowerCase().includes(q))
      && (!f || String(e.store_id) === f)
      && (!a || (a === '1' ? e.active : !e.active)));
    const rowsNow = slice(list, 'emp');
    $('cShown').textContent = list.length + ' tampil';
    const box = $('empList');
    box.innerHTML = list.length ? '' : '<div class="empty">Tidak ada yang cocok.</div>';
    rowsNow.forEach((e) => {
      const row = document.createElement('div');
      row.className = 'emp-row';
      row.innerHTML = '<div class="av ' + (e.face_key && e.active ? '' : 'no') + '">' + esc((e.name[0] || '?').toUpperCase()) + '</div>'
        + '<div class="nm"><b>' + esc(e.name) + '</b><span>#' + e.id + ' · ' + esc(storeName(e.store_id) ?? 'tanpa toko')
        + (e.employee_code ? ' · ' + esc(e.employee_code) : '') + '</span></div>'
        + (e.active ? '' : '<span class="badge b-no">nonaktif</span>')
        + (e.face_key ? '<span class="badge b-ok">wajah ✓</span>' : '<span class="badge b-no">belum</span>')
        + '<button type="button" class="mini ' + (e.active ? 'warn' : '') + '" data-toggleemp="' + e.id + '" data-active="' + (e.active ? '1' : '0') + '">'
        + (e.active ? 'Nonaktifkan' : 'Aktifkan') + '</button>'
        + '<button type="button" class="mini" data-editemp="' + e.id + '">Ubah</button>';
      row.onclick = (ev) => {
        const t = ev.target.closest('[data-toggleemp]');
        if (t) return toggleEmp(parseInt(t.dataset.toggleemp, 10), t.dataset.active !== '1');
        const b = ev.target.closest('[data-editemp]');
        if (b) return openEmp(parseInt(b.dataset.editemp, 10));
        $('faceEmp').value = String(e.id);
        goTab('pWajah');
        toast('Karyawan terpilih: ' + empLabel(e), 'ok');
      };
      box.appendChild(row);
    });
    paintPager('emp', 'empPageInfo', 'empPrev', 'empNext', rowsNow.length, list.length);
  }

  function renderNoFace(noFace) {
    const rowsNow = slice(noFace, 'noFace');
    const box = $('noFaceList');
    box.innerHTML = noFace.length ? '' : '<div class="empty">Semua karyawan sudah punya wajah 🎉</div>';
    rowsNow.forEach((e) => {
      const row = document.createElement('div');
      row.className = 'emp-row';
      row.innerHTML = '<div class="av no">' + esc((e.name[0] || '?').toUpperCase()) + '</div>'
        + '<div class="nm"><b>' + esc(e.name) + '</b><span>#' + e.id + ' · ' + esc(storeName(e.store_id) ?? 'tanpa toko') + '</span></div>'
        + '<span class="badge b-no">daftar</span>';
      row.onclick = () => { $('faceEmp').value = String(e.id); toast('Karyawan dipilih, lanjut foto wajah.', 'ok'); };
      box.appendChild(row);
    });
    paintPager('noFace', 'noFacePageInfo', 'noFacePrev', 'noFaceNext', rowsNow.length, noFace.length);
  }
  /* ---------- aksi kota / toko / karyawan ---------- */
  $('btnKota').onclick = async () => {
    const name = $('cityName').value.trim();
    if (!name) return toast('Isi nama kota dulu.', 'err');
    try {
      const j = await api('/kelola-wajah/kota', { name });
      $('cityName').value = '';
      toast(j.created ? 'Kota "' + name + '" disimpan.' : 'Kota "' + name + '" sudah ada.', 'ok');
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
  };

  $('btnLokasi').onclick = async () => {
    const body = {
      city_id: $('locCity').value || null,
      city: $('locCityNew').value.trim() || null,
      store: $('locStore').value.trim(),
      address: $('locAddr').value.trim() || null,
      lat: parseFloat($('locLat').value),
      lon: parseFloat($('locLon').value),
      radius_m: $('locRadius').value ? parseInt($('locRadius').value, 10) : null,
    };
    if (!body.store) return toast('Isi nama toko dulu.', 'err');
    if (!body.city_id && !body.city) return toast('Pilih kota, atau ketik nama kota baru.', 'err');
    if (Number.isNaN(body.lat) || Number.isNaN(body.lon)) return toast('Latitude & longitude wajib angka.', 'err');
    try {
      await api('/kelola-wajah/lokasi', body);
      ['locStore', 'locAddr', 'locLat', 'locLon', 'locRadius'].forEach((id) => { $(id).value = ''; });
      $('locCityNew').value = '';
      toast('Toko "' + body.store + '" disimpan.', 'ok');
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
  };

  $('btnKaryawan').onclick = async () => {
    const body = {
      name: $('empName').value.trim(),
      store_id: $('empStore').value ? parseInt($('empStore').value, 10) : null,
    };
    if (!body.name) return toast('Isi nama karyawan dulu.', 'err');
    try {
      const j = await api('/kelola-wajah/karyawan', body);
      $('empName').value = '';
      toast(j.existing ? j.message : 'Karyawan "' + body.name + '" ditambahkan.', 'ok');
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
  };

  $('empSearch').oninput = () => { pg.emp = 1; renderEmpList(); };
  $('fltStore').onchange = () => { pg.emp = 1; renderEmpList(); };
  $('fltActive').onchange = () => { pg.emp = 1; renderEmpList(); };
  $('storePrev').onclick = () => { pg.store--; renderStores(); };
  $('storeNext').onclick = () => { pg.store++; renderStores(); };
  $('empPrev').onclick = () => { pg.emp--; renderEmpList(); };
  $('empNext').onclick = () => { pg.emp++; renderEmpList(); };
  $('noFacePrev').onclick = () => { pg.noFace--; renderNoFace(D.employees.filter((e) => !e.face_key && e.active)); };
  $('noFaceNext').onclick = () => { pg.noFace++; renderNoFace(D.employees.filter((e) => !e.face_key && e.active)); };
  /* ---------- foto: file / kamera ---------- */
  function setImage(b64) {
    imageB64 = b64;
    const p = $('preview');
    p.src = b64;
    p.style.display = 'block';
    stopCam();
  }
  $('photo').onchange = () => {
    const f = $('photo').files[0];
    if (!f) return;
    const rd = new FileReader();
    rd.onload = () => setImage(rd.result);
    rd.readAsDataURL(f);
  };

  async function startCam() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 960 } }, audio: false });
      const v = $('cam');
      v.srcObject = stream;
      $('camBox').style.display = 'block';
      $('btnSnap').disabled = false;
      $('btnCam').textContent = '⏹ Tutup kamera';
      await v.play();
    } catch (e) {
      toast('Kamera tidak bisa dibuka: ' + e.message, 'err');
    }
  }
  function stopCam() {
    if (stream) { stream.getTracks().forEach((t) => t.stop()); stream = null; }
    $('camBox').style.display = 'none';
    $('btnSnap').disabled = true;
    $('btnCam').textContent = '📷 Buka kamera';
  }
  $('btnCam').onclick = () => (stream ? stopCam() : startCam());
  $('btnSnap').onclick = () => {
    const v = $('cam'), c = $('shot');
    if (!v.videoWidth) return toast('Kamera belum siap.', 'err');
    const W = 720, scale = W / v.videoWidth, H = Math.round(v.videoHeight * scale);
    c.width = W; c.height = H;
    c.getContext('2d').drawImage(v, 0, 0, W, H);
    setImage(c.toDataURL('image/jpeg', 0.9));
    toast('Foto diambil. Cek pratinjau lalu daftarkan.', 'ok');
  };
  /* ---------- daftar wajah ---------- */
  $('btnDaftar').onclick = async () => {
    const empId = $('faceEmp').value;
    if (!empId) return toast('Pilih karyawan dulu.', 'err');
    if (!imageB64) return toast('Ambil foto (kamera) atau pilih file dulu.', 'err');
    const btn = $('btnDaftar');
    btn.disabled = true; btn.textContent = 'Mengirim ke engine…';
    try {
      await api('/kelola-wajah/daftar', { employee_id: parseInt(empId, 10), image: imageB64 });
      imageB64 = '';
      $('preview').style.display = 'none';
      $('photo').value = '';
      toast('Wajah berhasil didaftarkan ✅', 'ok');
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
    btn.disabled = false; btn.textContent = 'Daftarkan ke engine';
  };

  /* ---------- zona bahaya ---------- */
  // Hapus face ID satu karyawan (dropdown).
  $('btnHapusSatu').onclick = async () => {
    const id = $('delFaceEmp').value;
    if (!id) return toast('Pilih karyawan dulu.', 'err');
    const opt = $('delFaceEmp').selectedOptions[0];
    if (!confirm('Hapus face ID ' + (opt ? opt.textContent : 'karyawan ini') + '?')) return;
    const btn = $('btnHapusSatu');
    btn.disabled = true; btn.textContent = 'Menghapus…';
    try {
      const j = await api('/kelola-wajah/hapus-satu', { employee_id: parseInt(id, 10) });
      toast(j.message, 'ok');
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
    btn.disabled = false; btn.textContent = 'Hapus face ID karyawan ini';
  };

  // Hapus semua — server minta konfirmasi teks, jadi ikut dikirim (dulu tombolnya
  // selalu gagal 422 karena field confirm gak pernah dikirim).
  $('btnHapus').onclick = async () => {
    const confirmText = $('delAllConfirm').value.trim();
    if (confirmText !== 'HAPUS SEMUA WAJAH') return toast('Ketik HAPUS SEMUA WAJAH dulu buat konfirmasi.', 'err');
    if (!confirm('Hapus SEMUA wajah di engine? face_key semua karyawan dilepas.')) return;
    const btn = $('btnHapus');
    btn.disabled = true; btn.textContent = 'Menghapus…';
    try {
      const j = await api('/kelola-wajah/hapus', { confirm: confirmText });
      toast(j.message, j.ok ? 'ok' : 'err');
      $('delAllConfirm').value = '';
      await loadAll();
    } catch (err) { toast(err.message, 'err'); }
    btn.disabled = false; btn.textContent = 'Hapus semua wajah engine';
  };

  loadAll().catch((e) => toast('Gagal memuat data: ' + e.message, 'err'));




})();
</script>
@endpush

