@php
    // Dipakai buat nandain baris akun sendiri ("kamu") + gate tombol hapus.
    $cu = request()->attributes->get('admin_user');
@endphp
@extends('layouts.admin')

@section('title', 'Kelola Akun')
@section('heading', 'Kelola Akun Admin')
@section('sub', 'Tambah / ubah / hapus akun pengelola: admin · manager · supervisor. Password dikosongkan saat tambah = dibikin otomatis dan ditampilkan sekali.')

@section('actions')
  <button class="btn" id="btnAdd">+ Tambah akun</button>
  <button class="btn ghost" id="btnReload">Muat ulang</button>
@endsection

@push('head')
<style>
.fld{display:flex;flex-direction:column;gap:4px;min-width:0}
.fld label,.fld>span{font-size:9.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--dim)}
input,select{padding:10px 12px;border-radius:11px;border:1px solid var(--line);background:var(--card2);color:var(--txt);font:inherit;font-size:13px;width:100%}
input:focus,select:focus{outline:none;border-color:var(--sky);box-shadow:0 0 0 3px rgba(56,189,248,.15)}
select option{background:var(--card2)}
.mini{padding:6px 9px;border-radius:9px;border:1px solid var(--line);background:var(--card2);color:var(--dim);font:inherit;font-size:11px;font-weight:700;cursor:pointer}
.mini:hover{color:var(--txt)}
.mini.warn{border-color:rgba(248,113,113,.3);color:#fca5a5}
.modal-bg{position:fixed;inset:0;background:rgba(2,5,9,.74);z-index:150;display:none;align-items:flex-start;justify-content:center;padding:26px 14px;overflow-y:auto}
.modal-bg.on{display:flex}
.modal{width:100%;max-width:520px;background:#0c131f;border:1px solid var(--line);border-radius:18px;padding:18px;box-shadow:0 30px 70px rgba(0,0,0,.65)}
.modal h3{margin:0 0 12px;font-size:15px;display:flex;justify-content:space-between;gap:10px;align-items:center}
.close{background:none;border:0;color:var(--dim);font-size:20px;cursor:pointer;line-height:1}
.mgrid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media (max-width:640px){.mgrid{grid-template-columns:1fr}}
.mfoot{display:flex;gap:8px;justify-content:flex-end;margin-top:16px}
.tiny{font-size:11px;color:var(--dim)}
.gen{margin-top:10px;padding:11px 13px;border-radius:11px;border:1px solid rgba(52,211,153,.32);background:rgba(52,211,153,.1);color:#a7f3d0;font-size:12.5px;display:none;cursor:pointer}
.gen.on{display:block}
.gen code{font-family:var(--mono);font-size:14px;color:#fff}
</style>
@endpush

@section('content')
<section class="card">
  <h2><span>Daftar akun</span><span class="tiny">{{ $users->count() }} akun</span></h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Dibuat</th><th>Password</th></tr></thead>
      <tbody>
        @foreach ($users as $u)
        <tr data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-email="{{ $u->email }}" data-role="{{ $u->role }}">
          <td><b>{{ $u->name }}</b> @if ($cu && $cu->id === $u->id) <span class="pill p-mute">kamu</span> @endif</td>
          <td class="mono dim">{{ $u->email }}</td>
          <td><span class="pill {{ $u->isAdmin() ? 'p-ok' : ($u->isManager() ? 'p-sky' : 'p-mid') }}">{{ $u->role }}</span></td>
          <td class="mono dim nowrap">{{ $u->created_at?->format('d/m/Y') ?? '—' }}</td>
          <td class="nowrap">
            <button class="mini" data-edit="{{ $u->id }}">Ubah</button>
            <button class="mini" data-reset="{{ $u->id }}">Reset</button>
            @unless ($cu && $cu->id === $u->id)
            <button class="mini warn" data-del="{{ $u->id }}">Hapus</button>
            @endunless
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <p class="tiny" style="margin:12px 0 0">Role: <b>admin</b> = akses penuh (termasuk halaman ini) · <b>manager</b> = presensi + kelola wajah · <b>supervisor</b> = presensi saja.</p>
</section>

{{-- Modal tambah akun --}}
<div class="modal-bg" id="addBg">
  <div class="modal">
    <h3><span>Tambah akun</span><button class="close" id="aClose">×</button></h3>
    <div class="mgrid">
      <label class="fld"><span>Nama</span><input type="text" id="aName" maxlength="80" placeholder="mis. Budi SPV"></label>
      <label class="fld"><span>Role</span>
        <select id="aRole">@foreach ($roles as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach</select>
      </label>
    </div>
    <label class="fld" style="margin-top:10px"><span>Email</span><input type="email" id="aEmail" maxlength="120" placeholder="nama@presensi.local"></label>
    <label class="fld" style="margin-top:10px"><span>Password (kosongkan = otomatis)</span><input type="text" id="aPass" maxlength="120" placeholder="min. 6 karakter, atau biarkan kosong"></label>
    <div class="gen" id="aGen">Password sementara: <code id="aGenVal"></code> — klik buat salin.</div>
    <div class="mfoot">
      <button class="mini" id="aCancel">Batal</button>
      <button class="btn" id="aSave">Simpan akun</button>
    </div>
  </div>
</div>

{{-- Modal ubah akun --}}
<div class="modal-bg" id="editBg">
  <div class="modal">
    <h3><span>Ubah akun</span><button class="close" id="eClose">×</button></h3>
    <input type="hidden" id="eId">
    <div class="mgrid">
      <label class="fld"><span>Nama</span><input type="text" id="eName" maxlength="80"></label>
      <label class="fld"><span>Role</span>
        <select id="eRole">@foreach ($roles as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach</select>
      </label>
    </div>
    <label class="fld" style="margin-top:10px"><span>Email</span><input type="email" id="eEmail" maxlength="120"></label>
    <label class="fld" style="margin-top:10px"><span>Password baru (kosongkan = tidak diubah)</span><input type="text" id="ePass" maxlength="120" placeholder="min. 6 karakter"></label>
    <div class="mfoot">
      <button class="mini" id="eCancel">Batal</button>
      <button class="btn" id="eSave">Simpan perubahan</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const ROLES = @json($roles);
function openModal(id) { $(id).classList.add('on'); }
function closeModal(id) { $(id).classList.remove('on'); }
function row(id) { return document.querySelector('tr[data-id="' + id + '"]'); }

function openAdd() {
  $('aName').value = ''; $('aEmail').value = ''; $('aPass').value = ''; $('aRole').value = ROLES[0];
  $('aGen').classList.remove('on'); $('aGenVal').textContent = '';
  openModal('addBg'); $('aName').focus();
}

function openEdit(id) {
  const r = row(id); if (!r) return;
  $('eId').value = r.dataset.id; $('eName').value = r.dataset.name; $('eEmail').value = r.dataset.email;
  $('eRole').value = r.dataset.role; $('ePass').value = '';
  openModal('editBg'); $('eName').focus();
}

const API = '{{ url('admin/pengguna') }}';

$('btnAdd').addEventListener('click', openAdd);
$('aCancel').addEventListener('click', () => closeModal('addBg'));
$('aClose').addEventListener('click', () => closeModal('addBg'));
$('eCancel').addEventListener('click', () => closeModal('editBg'));
$('eClose').addEventListener('click', () => closeModal('editBg'));
$('btnReload').addEventListener('click', () => location.reload());

$('aSave').addEventListener('click', async () => {
  const name = $('aName').value.trim(), email = $('aEmail').value.trim(), role = $('aRole').value, password = $('aPass').value;
  if (!name || !email) return toast('Nama & email wajib diisi.', 'err');
  try {
    const j = await api(API, { name, email, role, password });
    toast(j.message);
    if (j.password_plain) {
      // Tahan reload bentar biar admin sempat salin password sementara.
      $('aGenVal').textContent = j.password_plain;
      $('aGen').classList.add('on');
      setTimeout(() => location.reload(), 6000);
    } else {
      setTimeout(() => location.reload(), 500);
    }
  } catch (e) { toast(e.message, 'err'); }
});

$('eSave').addEventListener('click', async () => {
  const id = $('eId').value;
  const payload = { name: $('eName').value.trim(), email: $('eEmail').value.trim(), role: $('eRole').value };
  if ($('ePass').value) payload.password = $('ePass').value;
  try {
    const j = await api(API + '/' + id, payload);
    toast(j.message);
    setTimeout(() => location.reload(), 500);
  } catch (e) { toast(e.message, 'err'); }
});

$('aGen').addEventListener('click', () => {
  const t = $('aGenVal').textContent;
  if (t && navigator.clipboard) navigator.clipboard.writeText(t).then(() => toast('Password disalin.'));
});

document.addEventListener('click', async (ev) => {
  const e = ev.target.closest('[data-edit]');
  const rp = ev.target.closest('[data-reset]');
  const dl = ev.target.closest('[data-del]');

  if (e) return openEdit(parseInt(e.dataset.edit, 10));

  if (rp) {
    const id = parseInt(rp.dataset.reset, 10);
    const pw = prompt('Password baru untuk ' + (row(id)?.dataset.email || '') + ' (min. 6 karakter):');
    if (pw === null || pw === '') return;
    try {
      const j = await api(API + '/' + id, { password: pw });
      toast(j.message + ' Password baru: ' + pw);
    } catch (err) { toast(err.message, 'err'); }
    return;
  }

  if (dl) {
    const id = parseInt(dl.dataset.del, 10);
    if (!confirm('Hapus akun ' + (row(id)?.dataset.email || '') + '? Tidak bisa dibatalkan.')) return;
    try {
      const j = await api(API + '/' + id + '/hapus', {});
      toast(j.message);
      setTimeout(() => location.reload(), 500);
    } catch (err) { toast(err.message, 'err'); }
  }
});

['addBg', 'editBg'].forEach((id) => {
  $(id).addEventListener('click', (ev) => { if (ev.target === $(id)) closeModal(id); });
});
</script>
@endpush
