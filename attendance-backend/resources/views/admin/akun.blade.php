@extends('layouts.admin')

@section('title', 'Keamanan Akun')
@section('heading', 'Keamanan Akun')
@section('sub', 'Info akun yang lagi login + ganti password sendiri. Cocok dipakai manager/supervisor yang nggak punya akses halaman kelola akun.')

@section('content')
<section class="card">
  <h2><span>Info akun</span></h2>
  <div class="mgrid">
    <div class="mrow"><span>Nama</span><span>{{ $user->name }}</span></div>
    <div class="mrow"><span>Email</span><span class="mono">{{ $user->email }}</span></div>
    <div class="mrow"><span>Role</span><span class="pill {{ $user->isAdmin() ? 'p-ok' : ($user->isManager() ? 'p-sky' : 'p-mid') }}">{{ $user->role }}</span></div>
    <div class="mrow"><span>Dibuat</span><span class="mono">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</span></div>
  </div>
</section>

<section class="card">
  <h2><span>Ganti password</span><span class="tiny">min. 6 karakter</span></h2>
  <div class="mgrid">
    <label class="fld"><span>Password sekarang</span>
      <div class="pw"><input type="password" id="pOld" autocomplete="current-password"><button type="button" class="eye" data-for="pOld">👁️</button></div>
    </label>
    <label class="fld"><span>Password baru</span>
      <div class="pw"><input type="password" id="pNew" autocomplete="new-password"><button type="button" class="eye" data-for="pNew">👁️</button></div>
    </label>
    <label class="fld"><span>Ulangi password baru</span>
      <div class="pw"><input type="password" id="pNew2" autocomplete="new-password"><button type="button" class="eye" data-for="pNew2">👁️</button></div>
    </label>
  </div>
  <div class="mfoot">
    <button class="btn" id="btnSave">Simpan password baru</button>
  </div>
  <p class="tiny" style="margin:12px 0 0">Setelah password diganti, sesi login sekarang tetap jalan. Kalau lupa password, minta admin lain reset lewat <b>Kelola akun admin</b>.</p>
</section>
@endsection

@push('head')
<style>
.fld{display:flex;flex-direction:column;gap:4px;min-width:0}
.fld label,.fld>span{font-size:9.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--dim)}
.mgrid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media (max-width:900px){.mgrid{grid-template-columns:1fr}}
input{width:100%}
.pw{position:relative}
.pw input{padding-right:44px}
.eye{position:absolute;top:50%;right:4px;transform:translateY(-50%);width:34px;height:32px;border:0;border-radius:9px;background:transparent;color:var(--dim);font-size:14px;cursor:pointer}
.eye:hover,.eye[aria-pressed=true]{color:var(--txt);background:rgba(148,178,214,.12)}
.mrow{display:flex;justify-content:space-between;gap:14px;padding:9px 0;border-bottom:1px solid rgba(148,178,214,.08);font-size:12.5px}
.mrow span:first-child{color:var(--dim)}
.mfoot{display:flex;gap:8px;justify-content:flex-end;margin-top:14px}
.tiny{font-size:11px;color:var(--dim)}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.eye').forEach((b) => {
  const inp = document.getElementById(b.dataset.for);
  b.addEventListener('mousedown', (e) => e.preventDefault()); // jangan ilangin fokus input
  b.addEventListener('click', () => {
    const tampil = inp.type === 'password';
    inp.type = tampil ? 'text' : 'password';
    b.setAttribute('aria-pressed', String(tampil));
    b.textContent = tampil ? '🙈' : '👁️';
    inp.focus();
  });
});

$('btnSave').addEventListener('click', async () => {
  const current_password = $('pOld').value, password = $('pNew').value, password_confirmation = $('pNew2').value;
  if (!current_password || !password) return toast('Isi password sekarang & password baru.', 'err');
  if (password !== password_confirmation) return toast('Ulangi password baru belum sama.', 'err');
  try {
    const j = await api('{{ route('admin.akun.password') }}', { current_password, password, password_confirmation });
    toast(j.message);
    $('pOld').value = ''; $('pNew').value = ''; $('pNew2').value = '';
  } catch (e) { toast(e.message, 'err'); }
});
</script>
@endpush
