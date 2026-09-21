@extends('layouts.admin')

@section('title', 'Data Karyawan')
@section('heading', 'Data Karyawan')
@section('sub', 'Biodata lengkap ala HRD: identitas, kontak, kepegawaian, kontak darurat, bank & BPJS. Pendaftaran wajah tetap di menu Kelola Wajah.')

@section('actions')
  <button class="btn" id="btnAdd">+ Tambah karyawan</button>
  <button class="btn ghost" id="btnExport">Export CSV</button>
@endsection

@push('head')
<style>
.fbar{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px}
.fbar input,.fbar select{padding:8px 11px;border-radius:var(--r2);border:1px solid var(--line);background:var(--card2);color:var(--txt);font:inherit;font-size:12.5px}
.fbar input{flex:1;min-width:190px}
.avatar{width:34px;height:34px;border-radius:10px;object-fit:cover;border:1px solid var(--line);background:var(--card2);display:flex;align-items:center;justify-content:center;color:var(--dim);font-weight:700;flex:none}
.sect{grid-column:1/-1;font-size:9.5px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--sky);border-bottom:1px solid var(--line);padding-bottom:4px;margin-top:2px}
.chk{display:flex;align-items:center;gap:9px;grid-column:1/-1;font-size:12.5px;color:var(--txt);cursor:pointer}
.chk input{width:16px;height:16px;accent-color:var(--acc)}
.modal.wide{max-width:780px}
.dl{display:grid;grid-template-columns:135px 1fr;gap:6px 12px;font-size:13px;margin:0}
.dl dt{color:var(--dim);font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding-top:2px}
.dl dd{margin:0;overflow-wrap:anywhere}
.foto-big{width:92px;height:118px;border-radius:12px;object-fit:cover;border:1px solid var(--line2);flex:none}
.dhead{display:flex;gap:14px;align-items:flex-start;margin-bottom:14px}
.dinfo b{font-size:16px;display:block}
.dinfo .tiny{margin-top:2px}
.pwrap{display:flex;flex-direction:column;gap:12px;grid-column:1/-1}
@media (max-width:640px){.dl{grid-template-columns:1fr}.dl dt{margin-top:4px}}
.photo-row{display:flex;gap:14px;align-items:flex-start;grid-column:1/-1;flex-wrap:wrap}
.photo-prev{width:74px;height:94px;border-radius:10px;object-fit:cover;border:1px solid var(--line2);background:var(--card2)}
.photo-hint{font-size:11px;color:var(--dim);flex:1;min-width:140px}
</style>
@endpush

@section('content')
@php
    $storesByCity = $stores->groupBy(fn ($s) => $s->city?->name ?? '(tanpa kota)');
@endphp
<section class="card">
  <h2><span>Daftar karyawan</span><span class="tiny" id="cnt">memuat…</span></h2>
  <div class="fbar">
    <input id="fQ" type="search" placeholder="cari nama / kode / NIK / HP / email…" autocomplete="off">
    <select id="fStore"><option value="">semua toko</option>@foreach ($storesByCity as $cn => $list)<optgroup label="{{ $cn }}">@foreach ($list as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</optgroup>@endforeach</select>
    <select id="fStat"><option value="">semua status kerja</option>@foreach ($statuses as $st)<option value="{{ $st }}">{{ $st }}</option>@endforeach</select>
    <select id="fAkt"><option value="">aktif + nonaktif</option><option value="1">aktif saja</option><option value="0">nonaktif saja</option></select>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th></th><th>Nama</th><th>Toko</th><th>Jabatan</th><th>Kontak</th><th>Masuk / Kontrak</th><th></th></tr></thead>
      <tbody id="tb"></tbody>
    </table>
  </div>
  <p class="tiny" style="margin:12px 0 0">Karyawan nonaktif tetap tampil (badge abu) biar riwayat absennya nggak hilang — nonaktif = nggak muncul di halaman absen SPG.</p>
</section>

{{-- Modal detail profil --}}
<div class="modal-bg" id="dBg">
  <div class="modal">
    <h3><span id="dTitle">Profil karyawan</span><button class="close" data-close="dBg">&times;</button></h3>
    <div id="dBody"></div>
    <div class="mfoot"><button class="mini" data-close="dBg">Tutup</button><button class="mini" id="dEdit">Ubah data</button></div>
  </div>
</div>

{{-- Modal tambah/ubah --}}
<div class="modal-bg" id="fBg">
  <div class="modal wide">
    <h3><span id="fTitle">Tambah karyawan</span><button class="close" data-close="fBg">&times;</button></h3>
    <form id="frm" novalidate>
    <div class="fgrid">
      <div class="sect">Identitas</div>
      <label class="fld"><span>Nama lengkap *</span><input id="iName" maxlength="120" placeholder="mis. Ayu Lestari"></label>
      <label class="fld"><span>Kode karyawan</span><input id="iCode" maxlength="40" placeholder="mis. SPG-009"></label>
      <label class="fld"><span>NIK (16 digit)</span><input id="iNik" maxlength="20" inputmode="numeric" placeholder="mis. 3273015205910001"></label>
      <label class="fld"><span>Tempat lahir</span><input id="iBirthPlace" maxlength="80" placeholder="mis. Bandung"></label>
      <label class="fld"><span>Tanggal lahir</span><input id="iBirthDate" type="date"></label>
      <label class="fld"><span>Jenis kelamin</span><select id="iGender"><option value="">—</option>@foreach ($genders as $g)<option value="{{ $g }}">{{ $g }}</option>@endforeach</select></label>
      <label class="fld"><span>Status perkawinan</span><select id="iMarital"><option value="">—</option>@foreach ($maritals as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach</select></label>
      <label class="fld"><span>Agama</span><select id="iReligion"><option value="">—</option>@foreach ($religions as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach</select></label>
      <label class="fld"><span>Pendidikan</span><select id="iEducation"><option value="">—</option>@foreach ($educations as $ed)<option value="{{ $ed }}">{{ $ed }}</option>@endforeach</select></label>
    </div>
    <div class="fgrid" style="margin-top:12px">
      <div class="sect">Kontak &amp; domisili</div>
      <label class="fld"><span>No. HP</span><input id="iPhone" maxlength="20" placeholder="mis. 0812…"></label>
      <label class="fld"><span>Email</span><input id="iEmail" type="email" maxlength="120" placeholder="nama@mail.id"></label>
      <label class="fld span2"><span>Alamat KTP / domisili</span><textarea id="iAddress" rows="2" maxlength="2000" placeholder="Jl. … RT …/RW …, kota"></textarea></label>
    </div>
    <div class="fgrid" style="margin-top:12px">
      <div class="sect">Kepegawaian</div>
      <label class="fld"><span>Kota penempatan</span>
        <select id="iCity"><option value="">— pilih kota —</option>@foreach ($cities as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
      </label>
      <label class="fld"><span>Toko penempatan</span>
        <select id="iStore" disabled><option value="">— pilih kota dulu —</option></select>
      </label>
      <label class="fld"><span>Jabatan / posisi</span><input id="iPosition" maxlength="60" placeholder="mis. SPG"></label>
      <label class="fld"><span>Departemen</span><input id="iDepartment" maxlength="60" placeholder="mis. Sales"></label>
      <label class="fld"><span>Status kerja</span><select id="iStatus"><option value="">—</option>@foreach ($statuses as $st)<option value="{{ $st }}">{{ $st }}</option>@endforeach</select></label>
      <label class="fld"><span>Tanggal masuk</span><input id="iJoin" type="date"></label>
      <label class="fld"><span>Akhir kontrak</span><input id="iContractEnd" type="date"></label>
      <label class="fld"><span>Tanggal resign</span><input id="iResign" type="date"></label>
      <label class="fld"><span>Wajah terdaftar</span><input id="iFace" disabled placeholder="dikelola di Kelola Wajah"></label>
      <label class="chk"><input type="checkbox" id="iActive" checked> Masih aktif bekerja (nonaktif = hilang dari halaman absen SPG)</label>
    </div>
    <div class="fgrid" style="margin-top:12px">
      <div class="sect">Kontak darurat</div>
      <label class="fld"><span>Nama</span><input id="iEmName" maxlength="120" placeholder="mis. Rina (ibu)"></label>
      <label class="fld"><span>Hubungan</span><input id="iEmRel" maxlength="40" placeholder="mis. orang tua / suami"></label>
      <label class="fld"><span>No. HP</span><input id="iEmPhone" maxlength="20" inputmode="tel" placeholder="mis. 0813…"></label>
    </div>
    <div class="fgrid" style="margin-top:12px">
      <div class="sect">Bank &amp; penggajian</div>
      <label class="fld"><span>Nama bank</span><input id="iBankName" maxlength="40" placeholder="mis. BCA"></label>
      <label class="fld"><span>No. rekening</span><input id="iBankAcc" maxlength="40" inputmode="numeric" placeholder="mis. 1234567890"></label>
      <label class="fld"><span>Atas nama</span><input id="iBankHolder" maxlength="120" placeholder="mis. Ayu Lestari"></label>
    </div>
    <div class="fgrid" style="margin-top:12px">
      <div class="sect">BPJS &amp; catatan</div>
      <label class="fld"><span>No. BPJS Kesehatan</span><input id="iBpjsHealth" maxlength="30" inputmode="numeric" placeholder="13 digit"></label>
      <label class="fld"><span>No. BPJS Ketenagakerjaan</span><input id="iBpjsLabor" maxlength="30" inputmode="numeric" placeholder="mis. 000…"></label>
      <label class="fld span2"><span>Catatan</span><textarea id="iNotes" rows="2" maxlength="2000" placeholder="catatan HRD…"></textarea></label>
      <div class="photo-row">
        <img class="photo-prev" id="iPhotoPrev" alt="foto" hidden>
        <div style="flex:1;min-width:180px;display:flex;flex-direction:column;gap:6px">
          <label class="fld"><span>Foto profil (jpg/png, max 2 MB)</span><input id="iPhoto" type="file" accept="image/jpeg,image/png,image/webp"></label>
          <label class="chk" style="grid-column:auto"><input type="checkbox" id="iRemovePhoto"> Hapus foto yang sekarang</label>
          <span class="photo-hint">Foto doang buat profil — pendaftaran wajah buat absen tetap di menu Kelola Wajah.</span>
        </div>
      </div>
    </div>
    <div class="mfoot"><button class="mini" type="button" data-close="fBg">Batal</button><button class="mini" id="fSave" type="button">Simpan</button></div>
</form>
  </div>
</div>
@endsection

@push('scripts')
<script>
const API = '{{ url('admin/karyawan') }}';
let ROWS = [];

// ---------- modal helper ----------
function openM(id) { $(id).style.display = 'flex'; }
function closeM(id) { $(id).style.display = 'none'; }
document.addEventListener('click', (ev) => {
  const c = ev.target.closest('[data-close]');
  if (c) return closeM(c.dataset.close);
  const bg = ev.target.closest('.modal-bg');
  if (bg && ev.target === bg) closeM(bg.id);
});

// ---------- POST multipart (foto) + retry CSRF ----------
async function apiForm(url, fd) {
  const send = () => fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd });
  let r = await send();
  if (r.status === 419) {
    const html = await (await fetch(window.location.href, { headers: { Accept: 'text/html' } })).text();
    const m = html.match(/name="csrf-token" content="([^"]+)"/);
    if (m) { CSRF = m[1]; document.querySelector('meta[name="csrf-token"]').setAttribute('content', CSRF); r = await send(); }
  }
  const j = await r.json().catch(() => ({}));
  if (j.errors) throw new Error(Object.values(j.errors)[0][0]);
  if (!r.ok || j.ok === false) throw new Error(j.message || ('HTTP ' + r.status));
  return j;
}
</script>
<script>
// ---------- muat + render ----------
async function load() {
  $('cnt').textContent = 'memuat…';
  try {
    const j = await (await fetch(API + '/data', { headers: { Accept: 'application/json' } })).json();
    ROWS = j.rows || [];
    paint();
  } catch (e) { $('cnt').textContent = 'gagal memuat — muat ulang halaman'; toast(e.message, 'err'); }
}

function filtered() {
  const q = $('fQ').value.trim().toLowerCase(), st = $('fStore').value, w = $('fStat').value, ak = $('fAkt').value;
  return ROWS.filter((r) => {
    if (st && String(r.store_id) !== st) return false;
    if (w && r.employment_status !== w) return false;
    if (ak !== '' && String(r.active ? 1 : 0) !== ak) return false;
    if (!q) return true;
    return [r.name, r.employee_code, r.nik, r.phone, r.email, r.position].some((v) => v && String(v).toLowerCase().includes(q));
  });
}

const ini = (r) => (r.name || '?').trim().charAt(0).toUpperCase();

function paint() {
  const rows = filtered();
  $('cnt').textContent = rows.length + ' dari ' + ROWS.length + ' karyawan';
  const tb = $('tb');
  if (!rows.length) { tb.innerHTML = '<tr><td colspan="7" class="dim" style="text-align:center;padding:22px">' + (ROWS.length ? 'nggak ada yang cocok sama filter' : 'belum ada karyawan — tambah dulu') + '</td></tr>'; return; }
  tb.innerHTML = rows.map((r) => '<tr data-id="' + r.id + '">'
    + '<td>' + (r.photo_url ? '<img class="avatar" src="' + esc(r.photo_url) + '" alt="">' : '<div class="avatar">' + esc(ini(r)) + '</div>') + '</td>'
    + '<td><b>' + esc(r.name) + '</b>' + (r.active ? '' : ' <span class="pill p-mute">nonaktif</span>')
    + (r.employee_code ? '<div class="tiny mono">' + esc(r.employee_code) + '</div>' : '') + '</td>'
    + '<td>' + esc(r.store_name || '—') + '<div class="tiny dim">' + esc(r.city_name || '') + '</div></td>'
    + '<td>' + esc(r.position || '—') + '<div class="tiny dim">' + esc(r.employment_status || '') + '</div></td>'
    + '<td class="mono tiny">' + esc(r.phone || '—') + '<div class="tiny dim">' + esc(r.email || '') + '</div></td>'
    + '<td class="mono tiny nowrap">' + (r.join_date || '—') + '<div class="tiny dim">' + esc(r.tenure || '') + '</div></td>'
    + '<td class="nowrap"><button class="mini" data-detail="' + r.id + '">Detail</button> <button class="mini" data-edit="' + r.id + '">Ubah</button></td>'
    + '</tr>').join('');
}

const row = (id) => ROWS.find((r) => String(r.id) === String(id));
</script>
<script>
// ---------- modal profil (detail) ----------
function openDetail(r) {
  $('dTitle').textContent = r.name;
  const dl = (items) => '<dl class="dl">' + items.map(([k, v]) => '<dt>' + k + '</dt><dd>' + (v ? esc(v) : '<span class="dim">—</span>') + '</dd>').join('') + '</dl>';
  const foto = r.photo_url ? '<img class="foto-big" src="' + esc(r.photo_url) + '" alt="foto">' : '<div class="foto-big" style="display:flex;align-items:center;justify-content:center;font-size:30px;color:var(--dim)">' + esc(ini(r)) + '</div>';
  $('dBody').innerHTML =
    '<div class="dhead">' + foto
    + '<div class="dinfo"><b>' + esc(r.name) + '</b>'
    + '<div class="tiny">' + esc(r.position || '—') + ' · ' + esc(r.store_name || 'tanpa toko') + (r.city_name ? ' (' + esc(r.city_name) + ')' : '')
    + (r.active ? '' : ' · NONAKTIF') + '</div>'
    + '<div class="tiny dim">Bergabung ' + (r.join_date || '—') + (r.tenure ? ' · ' + esc(r.tenure) : '') + (r.age != null ? ' · ' + r.age + ' th' : '') + '</div></div></div>'
    + dl([['Kode', r.employee_code], ['NIK', r.nik], ['Jenis kelamin', r.gender], ['Tempat / tgl lahir', [r.birth_place, r.birth_date].filter(Boolean).join(', ') || ''],
          ['Status kawin', r.marital_status], ['Agama', r.religion], ['Pendidikan', r.education],
          ['HP', r.phone], ['Email', r.email], ['Alamat', r.address],
          ['Departemen', r.department], ['Status kerja', r.employment_status], ['Akhir kontrak', r.contract_end], ['Resign', r.resign_date],
          ['Wajah absen', r.has_face ? 'terdaftar' : 'belum'],
          ['Kontak darurat', [r.emergency_name, r.emergency_relation].filter(Boolean).join(' — ') || ''], ['HP darurat', r.emergency_phone],
          ['Bank', r.bank_name], ['Rekening', [r.bank_account, r.bank_holder].filter(Boolean).join(' — ') || ''],
          ['BPJS Kesehatan', r.bpjs_health], ['BPJS TK', r.bpjs_labor], ['Catatan', r.notes]]);
  $('dEdit').onclick = () => { closeM('dBg'); openForm(r); };
  openM('dBg');
}
</script>
<script>
// ---------- form tambah/ubah ----------
// Master kota → toko buat dropdown berjenjang di modal (pilih kota dulu, toko ke-filter).
const STORE_LIST = @json($stores->map(fn ($s) => ['id' => $s->id, 'city_id' => $s->city_id, 'name' => $s->name]));

function fillStores(cityId) {
  const sel = $('iStore');
  sel.innerHTML = '';
  const ph = document.createElement('option');
  ph.value = '';
  ph.textContent = cityId ? '— tanpa toko —' : '— pilih kota dulu —';
  sel.appendChild(ph);
  if (!cityId) { sel.disabled = true; return; }
  sel.disabled = false;
  STORE_LIST.filter((s) => String(s.city_id) === String(cityId)).forEach((s) => {
    const o = document.createElement('option');
    o.value = s.id;
    o.textContent = s.name;
    sel.appendChild(o);
  });
}

$('iCity').addEventListener('change', () => { fillStores($('iCity').value); $('iStore').value = ''; });

const TXT = [['iCode','employee_code'],['iNik','nik'],['iBirthPlace','birth_place'],['iPhone','phone'],['iEmail','email'],['iAddress','address'],
  ['iPosition','position'],['iDepartment','department'],['iJoin','join_date'],['iContractEnd','contract_end'],['iResign','resign_date'],
  ['iEmName','emergency_name'],['iEmRel','emergency_relation'],['iEmPhone','emergency_phone'],
  ['iBankName','bank_name'],['iBankAcc','bank_account'],['iBankHolder','bank_holder'],
  ['iBpjsHealth','bpjs_health'],['iBpjsLabor','bpjs_labor'],['iNotes','notes']];
const SEL = [['iGender','gender'],['iMarital','marital_status'],['iReligion','religion'],['iEducation','education'],['iStore','store_id'],['iStatus','employment_status']];

function openForm(r) {
  const edit = !!r;
  $('fTitle').textContent = edit ? 'Ubah data — ' + r.name : 'Tambah karyawan';
  $('iName').value = edit ? r.name : '';
  // Dropdown berjenjang: kota dulu, baru daftar tokonya keisi (urutan penting sebelum loop SEL).
  const cid = (edit && r.city_id) ? String(r.city_id) : '';
  $('iCity').value = cid;
  fillStores(cid);
  TXT.forEach(([i, k]) => { $(i).value = (r && r[k]) || ''; });
  SEL.forEach(([i, k]) => { $(i).value = (r && (r[k] == null ? '' : String(r[k]))) || ''; });
  $('iFace').value = (r && r.has_face) ? 'terdaftar' : (r ? 'belum' : '—');
  $('iActive').checked = !r || r.active;
  $('iRemovePhoto').checked = false;
  $('iPhoto').value = '';
  const prev = $('iPhotoPrev');
  if (prev.dataset.blob) { URL.revokeObjectURL(prev.dataset.blob); delete prev.dataset.blob; }
  if (r && r.photo_url) { prev.src = r.photo_url; prev.hidden = false; } else { prev.hidden = true; }
  $('fSave').dataset.id = edit ? r.id : '';
  openM('fBg');
  $('iName').focus();
}
</script>
<script>
$('fSave').addEventListener('click', async () => {
  const id = $('fSave').dataset.id;
  const name = $('iName').value.trim();
  if (!name) return toast('Nama lengkap wajib diisi.', 'err');
  const fd = new FormData();
  fd.append('name', name);
  TXT.forEach(([i, k]) => { if ($(i).value.trim() !== '') fd.append(k, $(i).value.trim()); });
  SEL.forEach(([i, k]) => { if ($(i).value !== '') fd.append(k, $(i).value); });
  fd.append('active', $('iActive').checked ? '1' : '0');
  if ($('iRemovePhoto').checked) fd.append('remove_photo', '1');
  const file = $('iPhoto').files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) return toast('Foto kegedean — maksimal 2 MB.', 'err');
    fd.append('photo', file);
  }
  $('fSave').disabled = true;
  try {
    const j = await apiForm(id ? API + '/' + id : API, fd);
    toast(j.message);
    closeM('fBg');
    await load();
  } catch (e) { toast(e.message, 'err'); }
  $('fSave').disabled = false;
});

$('iPhoto').addEventListener('change', () => {
  const f = $('iPhoto').files[0], prev = $('iPhotoPrev');
  if (!f) return;
  if (prev.dataset.blob) URL.revokeObjectURL(prev.dataset.blob);
  prev.src = URL.createObjectURL(f); prev.dataset.blob = prev.src; prev.hidden = false;
});

// ---------- aksi baris ----------
document.addEventListener('click', (ev) => {
  const d = ev.target.closest('[data-detail]');
  if (d) { const r = row(d.dataset.detail); if (r) openDetail(r); return; }
  const e = ev.target.closest('[data-edit]');
  if (e) { const r = row(e.dataset.edit); if (r) openForm(r); }
});
</script>
<script>
// ---------- export CSV ----------
$('btnExport').addEventListener('click', () => {
  const rows = filtered();
  if (!rows.length) return toast('Ngga ada baris buat di-export.', 'err');
  const cols = ['id','name','employee_code','nik','gender','birth_place','birth_date','age','marital_status','religion','education',
    'phone','email','address','store_name','city_name','position','department','employment_status','join_date','tenure',
    'contract_end','resign_date','emergency_name','emergency_relation','emergency_phone',
    'bank_name','bank_account','bank_holder','bpjs_health','bpjs_labor','notes','active','has_face'];
  const csv = [cols.join(';')].concat(rows.map((r) => cols.map((c) => {
    const v = r[c] == null ? '' : String(r[c]);
    return /[";\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v;
  }).join(';'))).join('\r\n');
  const a = document.createElement('a');
  a.href = URL.createObjectURL(new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' }));
  a.download = 'data-karyawan-' + new Date().toISOString().slice(0, 10) + '.csv';
  a.click(); URL.revokeObjectURL(a.href);
  toast(rows.length + ' baris di-export ke CSV.');
});

// ---------- filter + init ----------
['fQ', 'fStore', 'fStat', 'fAkt'].forEach((i) => { $(i).addEventListener('input', paint); $(i).addEventListener('change', paint); });
$('btnAdd').addEventListener('click', () => openForm(null));
load();
</script>
@endpush