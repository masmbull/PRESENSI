@extends('layouts.admin')

@section('title', 'Keamanan Akun')
@section('heading', 'Keamanan Akun')
@section('sub', 'Info akun yang lagi login + ganti password sendiri. Cocok dipakai manager/supervisor yang nggak punya akses halaman kelola akun.')

@section('content')
<div class="ak-grid">
  <section class="card">
    <h2><span>Info akun</span></h2>
    <div class="who">
      <div class="ava">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
      <div class="who-t">
        <b>{{ $user->name }}</b>
        <span class="mono">{{ $user->email }}</span>
      </div>
      <span class="pill {{ $user->isAdmin() ? 'p-ok' : ($user->isManager() ? 'p-sky' : 'p-mid') }}">{{ $user->role }}</span>
    </div>
    <div class="info">
      <div><span>Nama</span><b>{{ $user->name }}</b></div>
      <div><span>Email</span><b class="mono">{{ $user->email }}</b></div>
      <div><span>Role</span><b>{{ $user->role }}</b></div>
      <div><span>Dibuat</span><b class="mono">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</b></div>
    </div>
  </section>

  <section class="card">
    <h2><span>Ganti password</span><span class="tiny">min. 6 karakter</span></h2>
    <div class="fgrid">
      <label class="fld span2"><span>Password sekarang</span>
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
    <p class="tiny" style="margin:12px 0 0">Sesi login sekarang tetap jalan setelah ganti password. Kalau lupa password, minta admin lain reset lewat <b>Kelola akun admin</b>.</p>
  </section>
</div>
@endsection

@push('head')
<style>
.ak-grid{display:grid;grid-template-columns:1fr;gap:14px;align-items:start}
@media (min-width:1000px){.ak-grid{grid-template-columns:minmax(300px,.85fr) minmax(0,1.15fr)}}
.who{display:flex;align-items:center;gap:11px;padding:12px;border:1px solid var(--line);border-radius:var(--r2);background:var(--card2);margin-bottom:12px}
.ava{width:40px;height:40px;flex:none;border-radius:12px;background:rgba(52,211,153,.16);color:#a7f3d0;border:1px solid rgba(52,211,153,.32);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px}
.who-t{min-width:0;flex:1}
.who-t b{display:block;font-size:13.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.who-t span{display:block;font-size:11px;color:var(--dim);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.info>div{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:9px 0;border-bottom:1px solid rgba(151,181,217,.1);font-size:12.5px;min-width:0}
.info>div:last-child{border-bottom:0}
.info span{font-size:9.5px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--dim);flex:none}
.info b{font-weight:600;text-align:right;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pw{position:relative}
.pw input{padding-right:44px}
.eye{position:absolute;top:50%;right:4px;transform:translateY(-50%);width:34px;height:32px;border:0;border-radius:9px;background:transparent;color:var(--dim);font-size:14px;cursor:pointer}
.eye:hover,.eye[aria-pressed=true]{color:var(--txt);background:rgba(151,181,217,.12)}
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
