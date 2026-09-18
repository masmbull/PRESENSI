@extends('layouts.admin')

@section('title', 'Riwayat Absen')
@section('heading', 'Riwayat Absen Masuk & Pulang')
@section('sub', 'Semua data yang terekam: waktu, nama, toko, daerah, jarak, akurasi GPS, IP, device. Filter + pencarian jalan di sisi klien (instan), export Excel/PDF mengikuti hasil filter.')

@section('actions')
  <button class="btn" id="btnManual">+ Input manual</button>
  <button class="btn sky" id="btnXls">Export Excel</button>
  <button class="btn sky" id="btnPdf">Export PDF</button>
  <button class="btn ghost" id="btnReload">Muat ulang data</button>
@endsection

@push('head')
<style>
.filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(152px,1fr));gap:10px}
.fld{display:flex;flex-direction:column;gap:4px;min-width:0}
.fld label,.fld>span{font-size:9.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--dim)}
input,select{padding:10px 12px;border-radius:11px;border:1px solid var(--line);background:var(--card2);color:var(--txt);font:inherit;font-size:13px;width:100%}
input:focus,select:focus{outline:none;border-color:var(--sky);box-shadow:0 0 0 3px rgba(56,189,248,.15)}
input::placeholder{color:#4a5c70}
select option{background:var(--card2)}
.quick{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px}
.qbtn{padding:7px 12px;border-radius:999px;border:1px solid var(--line);background:var(--card2);color:var(--dim);font:inherit;font-size:11.5px;font-weight:700;cursor:pointer}
.qbtn.on{border-color:rgba(52,211,153,.4);color:#a7f3d0;background:rgba(52,211,153,.12)}
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:14px}
.kpi{background:linear-gradient(180deg,rgba(255,255,255,.035),transparent 45%),var(--card);border:1px solid var(--line);border-radius:14px;padding:12px 14px}
.klab{font-size:9.5px;letter-spacing:.12em;text-transform:uppercase;color:var(--dim)}
.kval{display:block;font-size:24px;font-family:var(--mono);margin:5px 0 1px}
.k-acc .kval{color:var(--acc)} .k-sky .kval{color:var(--sky)} .k-mid .kval{color:var(--mid)}
.k-warn .kval{color:var(--warn)} .k-vio .kval{color:var(--vio)} .k-txt .kval{color:var(--txt)}
.tbar{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:12px}
.tbar .grow{flex:1 1 260px;min-width:200px}
.tbar .small{width:120px}
th.sortable{cursor:pointer;user-select:none}
th.sortable:hover{color:var(--txt)}
.mini{padding:6px 9px;border-radius:9px;border:1px solid var(--line);background:var(--card2);color:var(--dim);font:inherit;font-size:11px;font-weight:700;cursor:pointer}
.mini:hover{color:var(--txt)}
.mini.warn{border-color:rgba(248,113,113,.3);color:#fca5a5}
.pager{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding-top:12px;font-size:12px;color:var(--dim)}
.modal-bg{position:fixed;inset:0;background:rgba(2,5,9,.74);z-index:150;display:none;align-items:flex-start;justify-content:center;padding:26px 14px;overflow-y:auto}
.modal-bg.on{display:flex}
.modal{width:100%;max-width:580px;background:#0c131f;border:1px solid var(--line);border-radius:18px;padding:18px;box-shadow:0 30px 70px rgba(0,0,0,.65)}
.modal h3{margin:0 0 12px;font-size:15px;display:flex;justify-content:space-between;gap:10px;align-items:center}
.close{background:none;border:0;color:var(--dim);font-size:20px;cursor:pointer;line-height:1}
.mrow{display:flex;justify-content:space-between;gap:14px;padding:8px 0;border-bottom:1px solid rgba(148,178,214,.08);font-size:12.5px}
.mrow span:first-child{color:var(--dim);flex:none}
.mrow span:last-child{text-align:right;word-break:break-word}
.mgrid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media (max-width:640px){.mgrid{grid-template-columns:1fr}}
.mfoot{display:flex;gap:8px;justify-content:flex-end;margin-top:16px}
.thumb{width:100%;max-width:220px;border-radius:12px;border:1px solid var(--line);display:block;margin:12px auto 0}
</style>
@endpush

@section('content')
<section class="card">
  <h2><span>Rentang data</span><span class="tiny" id="srcInfo">memuat…</span></h2>
  <div class="quick" id="quick">
    <button class="qbtn" data-q="today">Hari ini</button>
    <button class="qbtn" data-q="7">7 hari terakhir</button>
    <button class="qbtn" data-q="30">30 hari terakhir</button>
    <button class="qbtn" data-q="month">Bulan ini</button>
    <button class="qbtn" data-q="all">Semua</button>
  </div>
  <div class="filters">
    <label class="fld"><span>Dari tanggal</span><input type="date" id="fFrom" value="{{ $range['from'] }}"></label>
    <label class="fld"><span>Sampai tanggal</span><input type="date" id="fTo" value="{{ $range['to'] }}"></label>
    <label class="fld"><span>Tipe absen</span>
      <select id="fType"><option value="">Semua (masuk + pulang)</option><option value="masuk">Masuk</option><option value="pulang">Pulang</option></select>
    </label>
    <label class="fld"><span>Daerah / kota</span>
      <select id="fCity"><option value="">Semua daerah</option>@foreach ($cities as $c)<option value="{{ $c->name }}">{{ $c->name }}</option>@endforeach</select>
    </label>
    <label class="fld"><span>Toko</span>
      <select id="fStore"><option value="">Semua toko</option>@foreach ($stores as $s)<option value="{{ $s->id }}" data-city="{{ $s->city?->name }}">{{ $s->name }}</option>@endforeach</select>
    </label>
    <label class="fld"><span>Status</span>
      <select id="fStatus"><option value="">Semua status</option><option value="hadir">Hadir</option><option value="unknown">Unknown</option><option value="no_face">No face</option></select>
    </label>
    <label class="fld"><span>Geofence</span>
      <select id="fRadius"><option value="">Semua</option><option value="in">Dalam radius</option><option value="out">Di luar radius</option></select>
    </label>
  </div>
</section>

<div class="kpis" id="kpis"></div>

<section class="card">
  <h2><span>Data absen <span id="countInfo" class="tiny"></span></span><span class="tiny">klik judul kolom untuk urutkan</span></h2>
  <div class="tbar">
    <label class="fld grow"><span>Cari (nama / kode / toko / daerah / IP / device)</span>
      <input type="search" id="fQ" placeholder="ketik apa saja… mis. Ayu, CAHAYA, 103.111, Kediri">
    </label>
    <label class="fld small"><span>Baris</span>
      <select id="fPer"><option>25</option><option selected>50</option><option>100</option><option>200</option><option value="0">Semua</option></select>
    </label>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th class="sortable" data-sort="at">Waktu</th>
          <th class="sortable" data-sort="name">Nama</th>
          <th class="sortable" data-sort="type">Tipe</th>
          <th class="sortable" data-sort="status">Status</th>
          <th class="sortable" data-sort="store">Toko / daerah</th>
          <th class="sortable" data-sort="distance_m">Jarak</th>
          <th class="sortable" data-sort="acc">GPS (±m)</th>
          <th class="sortable" data-sort="ip">IP</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="rows"><tr><td colspan="9" class="empty">Memuat data…</td></tr></tbody>
    </table>
  </div>
  <div class="pager">
    <div id="pageInfo">—</div>
    <div style="display:flex;gap:8px;align-items:center">
      <button class="mini" id="prev">‹ Sebelumnya</button>
      <span id="pageNum" class="mono">1</span>
      <button class="mini" id="next">Berikutnya ›</button>
    </div>
  </div>
</section>

<div class="modal-bg" id="detailBg">
  <div class="modal">
    <h3><span id="dTitle">Detail absen</span><button class="close" id="dClose">×</button></h3>
    <div id="dBody"></div>
    <div class="mfoot">
      <button class="mini warn" id="dDel">Hapus record</button>
      <button class="mini" id="dOk">Tutup</button>
    </div>
  </div>
</div>

<div class="modal-bg" id="manualBg">
  <div class="modal">
    <h3><span>Input absen manual</span><button class="close" id="mClose">×</button></h3>
    <p class="tiny" style="margin:-6px 0 12px">Buat kasus SPG lupa absen / koreksi admin. Record ditandai “input manual admin”, titik lokasi mengikuti toko.</p>
    <div class="mgrid">
      <label class="fld"><span>Tanggal</span><input type="date" id="mDate"></label>
      <label class="fld"><span>Jam</span><input type="time" id="mTime"></label>
      <label class="fld"><span>Tipe</span>
        <select id="mType"><option value="masuk">Masuk</option><option value="pulang">Pulang</option></select>
      </label>
      <label class="fld"><span>Toko</span><select id="mStore"><option value="">Semua toko</option>@foreach ($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></label>
    </div>
    <label class="fld" style="margin-top:10px"><span>Karyawan</span><select id="mEmp"><option value="">Pilih toko dulu…</option></select></label>
    <label class="fld" style="margin-top:10px"><span>Catatan (opsional)</span><input type="text" id="mNote" maxlength="120" placeholder="mis. lupa absen pulang, HP mati"></label>
    <div class="mfoot">
      <button class="mini" id="mCancel">Batal</button>
      <button class="btn" id="mSave">Simpan absen</button>
    </div>
  </div>
</div>
@endsection


@push('scripts')
<script>
const RANGE = @json($range);
let ALL = [], VIEW = [], page = 1, perPage = 50, sortKey = 'at', sortDir = 'desc', current = null;

const SORTS = {
  at: (r) => r.at || '',
  name: (r) => (r.name || '').toLowerCase(),
  type: (r) => r.type || '',
  status: (r) => r.status || '',
  store: (r) => (r.store || '').toLowerCase(),
  distance_m: (r) => (r.distance_m == null ? 1e9 : r.distance_m),
  acc: (r) => (r.acc == null ? 1e9 : r.acc),
  ip: (r) => r.ip || '',
};

function typePill(r) {
  return r.type === 'pulang' ? '<span class="pill p-sky">pulang</span>' : '<span class="pill p-ok">masuk</span>';
}
function statusPill(r) {
  if (r.status === 'hadir') return '<span class="pill p-ok">hadir</span>';
  if (r.status === 'no_face') return '<span class="pill p-warn">no face</span>';
  return '<span class="pill p-mute">' + esc(r.status || 'unknown') + '</span>';
}
function withinPill(r) {
  if (r.within === true) return '<span class="pill p-ok">dalam radius</span>';
  if (r.within === false) return '<span class="pill p-warn">luar radius</span>';
  return '<span class="pill p-mute">radius ?</span>';
}
function isManual(r) { return String(r.device || '').indexOf('manual') !== -1; }
function num(v, suffix) { return v == null ? '—' : '±' + Math.round(v) + (suffix || ''); }

// ---------- muat data dari server ----------
async function load() {
  $('srcInfo').textContent = 'memuat…';
  const from = $('fFrom').value || 'all';
  const to = $('fTo').value || 'all';
  try {
    const r = await fetch('/admin/absensi/data?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to), { headers: { 'Accept': 'application/json' } });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    const j = await r.json();
    ALL = j.rows || [];
    $('srcInfo').textContent = 'sumber: ' + (j.from === 'all' ? 'semua tanggal' : j.from + ' → ' + (j.to === 'all' ? 'sekarang' : j.to)) + ' · ' + ALL.length + ' record dimuat';
    apply();
  } catch (e) {
    $('rows').innerHTML = '<tr><td colspan="9" class="empty">Gagal memuat data: ' + esc(e.message) + '</td></tr>';
    toast('Gagal memuat data: ' + e.message, 'err');
  }
}

// ---------- filter + pencarian (semuanya sisi klien) ----------
function apply() {
  const q = $('fQ').value.trim().toLowerCase();
  const type = $('fType').value, status = $('fStatus').value, city = $('fCity').value, store = $('fStore').value, rad = $('fRadius').value;

  VIEW = ALL.filter((r) => {
    if (type && r.type !== type) return false;
    if (status && r.status !== status) return false;
    if (city && r.city !== city) return false;
    if (store && String(r.store_id) !== store) return false;
    if (rad === 'in' && r.within !== true) return false;
    if (rad === 'out' && r.within !== false) return false;
    if (q) {
      const hay = [r.name, r.code, r.store, r.city, r.ip, r.device, r.reason, r.at, r.type, r.status].join(' ').toLowerCase();
      if (hay.indexOf(q) === -1) return false;
    }
    return true;
  });

  sortView();
  page = 1;
  render();
  renderKpi();
}

function sortView() {
  const get = SORTS[sortKey] || SORTS.at;
  VIEW.sort((a, b) => {
    const x = get(a), y = get(b);
    if (x < y) return sortDir === 'asc' ? -1 : 1;
    if (x > y) return sortDir === 'asc' ? 1 : -1;
    return 0;
  });
}
// ---------- render tabel ----------
function render() {
  const total = VIEW.length;
  const size = perPage > 0 ? perPage : (total || 1);
  const pages = perPage > 0 ? Math.max(1, Math.ceil(total / perPage)) : 1;
  if (page > pages) page = pages;
  const rows = perPage > 0 ? VIEW.slice((page - 1) * perPage, page * perPage) : VIEW;

  $('countInfo').textContent = '· ' + total + ' record setelah filter (dari ' + ALL.length + ')';
  $('pageInfo').textContent = total === 0
    ? 'Tidak ada data'
    : 'Menampilkan ' + ((page - 1) * size + 1) + '–' + ((page - 1) * size + rows.length) + ' dari ' + total;
  $('pageNum').textContent = page + ' / ' + pages;
  $('prev').disabled = page <= 1;
  $('next').disabled = page >= pages;

  if (!rows.length) {
    $('rows').innerHTML = '<tr><td colspan="9" class="empty">Tidak ada record yang cocok. Coba longgarkan filter atau ubah rentang tanggal.</td></tr>';
    return;
  }

  $('rows').innerHTML = rows.map((r) => `
    <tr>
      <td class="mono nowrap">${esc(r.date)}<br><b>${esc(r.time)}</b></td>
      <td><b>${esc(r.name)}</b>${isManual(r) ? ' <span class="pill p-mid">manual</span>' : ''}${r.code ? '<br><span class="dim mono">' + esc(r.code) + '</span>' : ''}</td>
      <td>${typePill(r)}</td>
      <td>${statusPill(r)}</td>
      <td>${esc(r.store || '—')}<br><span class="dim">${esc(r.city || '—')}</span></td>
      <td class="mono nowrap">${num(r.distance_m, ' m')}<br>${withinPill(r)}</td>
      <td class="mono dim">${num(r.acc)}</td>
      <td class="mono dim">${esc(r.ip || '—')}</td>
      <td class="nowrap">
        <button class="mini" data-detail="${r.id}">Detail</button>
        <button class="mini warn" data-del="${r.id}">Hapus</button>
      </td>
    </tr>`).join('');
}

// ---------- KPI kecil (ikut hasil filter) ----------
function renderKpi() {
  const masuk = VIEW.filter((r) => r.type === 'masuk').length;
  const pulang = VIEW.filter((r) => r.type === 'pulang').length;
  const orang = new Set(VIEW.map((r) => (r.name || '') + '|' + (r.code || ''))).size;
  const luar = VIEW.filter((r) => r.within === false).length;

  const keyMasuk = new Set(), keyPulang = new Set();
  VIEW.forEach((r) => {
    const k = (r.name || '') + '|' + (r.date || '');
    if (r.type === 'masuk') keyMasuk.add(k);
    if (r.type === 'pulang') keyPulang.add(k);
  });
  let belum = 0;
  keyMasuk.forEach((k) => { if (!keyPulang.has(k)) belum++; });

  $('kpis').innerHTML = [
    ['k-txt', 'Total record', VIEW.length, 'setelah filter'],
    ['k-acc', 'Absen masuk', masuk, 'record'],
    ['k-sky', 'Absen pulang', pulang, 'record'],
    ['k-vio', 'Karyawan unik', orang, 'nama + kode'],
    ['k-mid', 'Belum pulang', belum, 'masuk tanpa pulang di hari sama'],
    ['k-warn', 'Di luar radius', luar, 'record di luar geofence'],
  ].map((k) => `<div class="kpi ${k[0]}"><span class="klab">${k[1]}</span><b class="kval">${k[2]}</b><span class="tiny">${k[3]}</span></div>`).join('');
}
// ---------- modal detail record ----------
function openDetail(id) {
  const r = ALL.find((x) => x.id === id);
  if (!r) return;
  current = r;
  $('dTitle').textContent = (r.name || 'Record') + ' · ' + r.type.toUpperCase() + ' · ' + r.date + ' ' + r.time;

  const maps = (r.lat != null && r.lon != null) ? 'https://maps.google.com/?q=' + r.lat + ',' + r.lon : null;
  const rows = [
    ['Waktu', r.at],
    ['Nama', r.name],
    ['Kode karyawan', r.code || '—'],
    ['Tipe absen', r.type],
    ['Status', r.status],
    ['Toko', r.store || '—'],
    ['Daerah / kota', r.city || '—'],
    ['Jarak ke titik toko', num(r.distance_m, ' m')],
    ['Radius toko', r.radius_m != null ? Math.round(r.radius_m) + ' m' : '— (pakai default)'],
    ['Dalam radius?', r.within === true ? 'Ya' : (r.within === false ? 'Tidak' : '—')],
    ['Akurasi GPS HP', num(r.acc, ' m')],
    ['IP', r.ip || '—'],
    ['Koordinat', r.lat != null && r.lon != null ? (r.lat + ', ' + r.lon + (maps ? ' · <a href="' + maps + '" target="_blank" rel="noopener">buka map</a>' : '')) : '—'],
    ['Device', r.device || '—'],
    ['Catatan', r.reason || '—'],
    ['Face key', r.face_key || '—'],
    ['Cosine', r.cosine != null ? String(r.cosine) : '—'],
    ['Liveness', r.liveness != null ? String(r.liveness) : '—'],
    ['Atribut wajah', r.attrs ? JSON.stringify(r.attrs) : '—'],
    ['Sumber', isManual(r) ? 'input manual admin' : 'absen dari HP SPG'],
  ];

  $('dBody').innerHTML = rows.map((x) => '<div class="mrow"><span>' + esc(x[0]) + '</span><span>' + (x[0] === 'Koordinat' ? x[1] : esc(x[1])) + '</span></div>').join('')
    + (r.has_thumb ? '<img class="thumb" src="/admin/absensi/' + r.id + '/foto" alt="Selfie absen">' : '');
  $('detailBg').classList.add('on');
}

async function del(id) {
  const r = ALL.find((x) => x.id === id);
  if (!r) return;
  if (!confirm('Hapus record ' + (r.name || '') + ' (' + r.type + ' ' + r.date + ' ' + r.time + ')? Tidak bisa dibatalkan.')) return;
  try {
    const j = await api('/admin/absensi/' + id + '/hapus');
    toast(j.message || 'Record dihapus.');
    $('detailBg').classList.remove('on');
    await load();
  } catch (e) {
    toast('Gagal hapus: ' + e.message, 'err');
  }
}

// ---------- interaksi tabel & filter ----------
['fQ', 'fType', 'fStatus', 'fStore', 'fRadius'].forEach((id) => {
  $(id).addEventListener('input', apply);
  $(id).addEventListener('change', apply);
});

// Daerah → toko: opsi toko ikut menyesuaikan daerah yang dipilih.
$('fCity').addEventListener('change', () => {
  const city = $('fCity').value;
  let stillValid = false;
  Array.from($('fStore').options).forEach((o, i) => {
    if (i === 0) return;
    const ok = !city || o.dataset.city === city;
    o.hidden = !ok;
    o.disabled = !ok;
    if (ok && o.selected) stillValid = true;
  });
  if (!stillValid) $('fStore').value = '';
  apply();
});

$('fFrom').addEventListener('change', load);
$('fTo').addEventListener('change', load);
$('btnReload').addEventListener('click', load);

$('fPer').addEventListener('change', () => {
  perPage = parseInt($('fPer').value, 10) || 0;
  page = 1;
  render();
});

document.querySelectorAll('th.sortable').forEach((th) => {
  th.addEventListener('click', () => {
    const key = th.dataset.sort;
    if (sortKey === key) {
      sortDir = sortDir === 'asc' ? 'desc' : 'asc';
    } else {
      sortKey = key;
      sortDir = key === 'at' ? 'desc' : 'asc';
    }
    sortView();
    page = 1;
    render();
  });
});

$('prev').addEventListener('click', () => { if (page > 1) { page--; render(); } });
$('next').addEventListener('click', () => { page++; render(); });

$('rows').addEventListener('click', (ev) => {
  const d = ev.target.closest('[data-detail]');
  const x = ev.target.closest('[data-del]');
  if (d) openDetail(parseInt(d.dataset.detail, 10));
  if (x) del(parseInt(x.dataset.del, 10));
});

document.getElementById('quick').addEventListener('click', (ev) => {
  const b = ev.target.closest('[data-q]');
  if (!b) return;
  const iso = localIso;
  const now = new Date();
  const q = b.dataset.q;
  if (q === 'all') {
    $('fFrom').value = RANGE.oldest || '';
    $('fTo').value = iso(now);
  } else if (q === 'today') {
    $('fFrom').value = iso(now);
    $('fTo').value = iso(now);
  } else if (q === 'month') {
    $('fFrom').value = iso(new Date(now.getFullYear(), now.getMonth(), 1));
    $('fTo').value = iso(now);
  } else {
    const days = parseInt(q, 10);
    $('fFrom').value = iso(new Date(now.getTime() - (days - 1) * 86400000));
    $('fTo').value = iso(now);
  }
  document.querySelectorAll('#quick .qbtn').forEach((x) => x.classList.toggle('on', x === b));
  load();
});
// ---------- helper export ----------
const XL_COLS = [
  ['Tanggal', (r) => r.date],
  ['Jam', (r) => r.time],
  ['Nama', (r) => r.name],
  ['Kode', (r) => r.code || ''],
  ['Tipe', (r) => r.type],
  ['Status', (r) => r.status],
  ['Toko', (r) => r.store || ''],
  ['Daerah', (r) => r.city || ''],
  ['Jarak ke toko (m)', (r) => (r.distance_m == null ? '' : Math.round(r.distance_m))],
  ['Radius toko (m)', (r) => (r.radius_m == null ? '' : Math.round(r.radius_m))],
  ['Dalam radius', (r) => (r.within === true ? 'ya' : (r.within === false ? 'tidak' : ''))],
  ['Akurasi GPS (m)', (r) => (r.acc == null ? '' : Math.round(r.acc))],
  ['IP', (r) => r.ip || ''],
  ['Latitude', (r) => (r.lat == null ? '' : r.lat)],
  ['Longitude', (r) => (r.lon == null ? '' : r.lon)],
  ['Device', (r) => r.device || ''],
  ['Catatan', (r) => r.reason || ''],
];

function localIso(d) {
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}
function stamp() {
  const d = new Date();
  return localIso(d).split('-').reverse().join('/') + ' ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
}
function filterInfo() {
  const parts = ['Rentang: ' + ($('fFrom').value || 'semua') + ' → ' + ($('fTo').value || 'sekarang')];
  if ($('fType').value) parts.push('Tipe: ' + $('fType').value);
  if ($('fCity').value) parts.push('Daerah: ' + $('fCity').value);
  if ($('fStore').value) parts.push('Toko: ' + $('fStore').options[$('fStore').selectedIndex].text);
  if ($('fStatus').value) parts.push('Status: ' + $('fStatus').value);
  if ($('fRadius').value) parts.push('Geofence: ' + ($('fRadius').value === 'in' ? 'dalam radius' : 'luar radius'));
  if ($('fQ').value.trim()) parts.push('Cari: "' + $('fQ').value.trim() + '"');
  return parts.join(' · ');
}
function summaryText() {
  const masuk = VIEW.filter((r) => r.type === 'masuk').length;
  const pulang = VIEW.filter((r) => r.type === 'pulang').length;
  const orang = new Set(VIEW.map((r) => (r.name || '') + '|' + (r.code || ''))).size;
  const luar = VIEW.filter((r) => r.within === false).length;
  return 'Total ' + VIEW.length + ' record · masuk ' + masuk + ' · pulang ' + pulang + ' · karyawan unik ' + orang + ' · luar radius ' + luar;
}
function download(blob, name) {
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = name;
  document.body.appendChild(a);
  a.click();
  setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 1500);
}

// ---------- export Excel (.xls — kebuka di Excel / Google Sheets) ----------
function exportXls() {
  if (!VIEW.length) return toast('Tidak ada baris untuk diekspor.', 'err');
  const head = XL_COLS.map((c) => '<th>' + esc(c[0]) + '</th>').join('');
  const body = VIEW.map((r) => '<tr>' + XL_COLS.map((c) => '<td>' + esc(c[1](r)) + '</td>').join('') + '</tr>').join('');
  const html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="utf-8"></head><body>'
    + '<table border="1"><tr><th colspan="' + XL_COLS.length + '">REKAP ABSEN MASUK &amp; PULANG SPG</th></tr>'
    + '<tr><td colspan="' + XL_COLS.length + '">' + esc(filterInfo()) + '</td></tr>'
    + '<tr><td colspan="' + XL_COLS.length + '">' + esc(summaryText()) + ' · diekspor ' + esc(stamp()) + '</td></tr>'
    + '<tr>' + head + '</tr>' + body + '</table></body></html>';

  download(new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel;charset=utf-8' }), 'rekap-absen-' + localIso(new Date()) + '.xls');
  toast(VIEW.length + ' baris diekspor ke Excel (.xls).');
}
// ---------- export PDF (jendela print → Save as PDF, A4 landscape) ----------
const PDF_COLS = [
  ['No', (r, i) => i + 1],
  ['Tanggal', (r) => r.date],
  ['Jam', (r) => r.time],
  ['Nama', (r) => r.name],
  ['Kode', (r) => r.code || ''],
  ['Tipe', (r) => r.type],
  ['Toko', (r) => r.store || ''],
  ['Daerah', (r) => r.city || ''],
  ['Jarak (m)', (r) => (r.distance_m == null ? '' : Math.round(r.distance_m))],
  ['GPS (m)', (r) => (r.acc == null ? '' : Math.round(r.acc))],
  ['IP', (r) => r.ip || ''],
];

function exportPdf() {
  if (!VIEW.length) return toast('Tidak ada baris untuk diekspor.', 'err');
  const w = window.open('', '_blank');
  if (!w) return toast('Popup diblokir browser — izinkan popup lalu klik Export PDF lagi.', 'err');

  const head = PDF_COLS.map((c) => '<th>' + esc(c[0]) + '</th>').join('');
  const body = VIEW.map((r, i) => '<tr>' + PDF_COLS.map((c) => '<td>' + esc(c[1](r, i)) + '</td>').join('') + '</tr>').join('');

  const css = [
    '@page{size:A4 landscape;margin:11mm}',
    'body{font:11px/1.4 "Segoe UI",Arial,sans-serif;color:#111;margin:0}',
    'h1{font-size:15px;margin:0 0 3px}',
    '.meta{font-size:10px;color:#444;margin-bottom:9px;line-height:1.5}',
    'table{width:100%;border-collapse:collapse}',
    'th,td{border:1px solid #98a2b3;padding:3px 5px;font-size:9.5px;text-align:left;vertical-align:top}',
    'th{background:#eef2f7}',
    'thead{display:table-header-group}',
    'tr{page-break-inside:avoid}',
    '.bar{margin-bottom:10px}',
    '.bar button{padding:8px 14px;border-radius:8px;border:1px solid #98a2b3;background:#f1f5f9;font:inherit;font-weight:700;cursor:pointer}',
    '@media print{.bar{display:none}}',
  ].join('');

  w.document.write('<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Rekap Absen SPG</title><style>' + css + '</style></head><body>'
    + '<div class="bar"><button onclick="window.print()">Cetak / Simpan sebagai PDF</button></div>'
    + '<h1>REKAP ABSEN MASUK &amp; PULANG SPG</h1>'
    + '<div class="meta">' + esc(filterInfo()) + '<br>' + esc(summaryText()) + ' · dicetak ' + esc(stamp()) + '</div>'
    + '<table><thead><tr>' + head + '</tr></thead><tbody>' + body + '</tbody></table>'
    + '<script>window.onload=function(){setTimeout(function(){window.print()},350)}<\/script>'
    + '</body></html>');
  w.document.close();
  toast('Jendela print dibuka — pilih "Save as PDF" untuk simpan sebagai PDF.');
}

// ---------- input absen manual ----------
let EMPLOYEES = null;

async function fillManualEmployees() {
  const sel = $('mEmp');
  if (EMPLOYEES === null) {
    try {
      const r = await fetch('/kelola-wajah/karyawan', { headers: { 'Accept': 'application/json' } });
      if (!r.ok) throw new Error('HTTP ' + r.status);
      EMPLOYEES = await r.json();
    } catch (e) {
      sel.innerHTML = '<option value="">Gagal memuat daftar karyawan</option>';
      return;
    }
  }
  const store = $('mStore').value;
  const list = (EMPLOYEES || []).filter((e) => !store || String(e.store_id) === store);
  sel.innerHTML = list.length
    ? '<option value="">Pilih karyawan…</option>' + list.map((e) => '<option value="' + e.id + '">' + esc(e.name) + (e.store ? ' · ' + esc(e.store.name) : '') + '</option>').join('')
    : '<option value="">Tidak ada karyawan di toko ini</option>';
}

function openManual() {
  const now = new Date();
  $('mDate').value = localIso(now);
  $('mTime').value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
  $('mStore').value = $('fStore').value || '';
  $('mNote').value = '';
  $('manualBg').classList.add('on');
  fillManualEmployees();
}

$('mStore').addEventListener('change', fillManualEmployees);
$('btnManual').addEventListener('click', openManual);
$('mCancel').addEventListener('click', () => $('manualBg').classList.remove('on'));
$('mClose').addEventListener('click', () => $('manualBg').classList.remove('on'));

$('mSave').addEventListener('click', async () => {
  if (!$('mEmp').value) return toast('Pilih karyawan dulu.', 'err');
  if (!$('mDate').value || !$('mTime').value) return toast('Isi tanggal dan jam absen.', 'err');
  $('mSave').disabled = true;
  try {
    const j = await api('/admin/absensi/manual', {
      employee_id: parseInt($('mEmp').value, 10),
      date: $('mDate').value,
      time: $('mTime').value,
      type: $('mType').value,
      note: $('mNote').value.trim(),
    });
    toast(j.message || 'Absen manual tersimpan.');
    $('manualBg').classList.remove('on');
    await load();
  } catch (e) {
    toast('Gagal simpan: ' + e.message, 'err');
  }
  $('mSave').disabled = false;
});

// ---------- modal detail: tombol & backdrop ----------
$('dClose').addEventListener('click', () => $('detailBg').classList.remove('on'));
$('dOk').addEventListener('click', () => $('detailBg').classList.remove('on'));
$('dDel').addEventListener('click', () => { if (current) del(current.id); });
$('detailBg').addEventListener('click', (ev) => { if (ev.target === $('detailBg')) $('detailBg').classList.remove('on'); });
$('manualBg').addEventListener('click', (ev) => { if (ev.target === $('manualBg')) $('manualBg').classList.remove('on'); });

$('btnXls').addEventListener('click', exportXls);
$('btnPdf').addEventListener('click', exportPdf);
document.addEventListener('keydown', (ev) => {
  if (ev.key === 'Escape') { $('detailBg').classList.remove('on'); $('manualBg').classList.remove('on'); }
});

// ---------- jalan ----------
load();
</script>
@endpush