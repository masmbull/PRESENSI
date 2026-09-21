@php
    // Menu sidebar dibikin di sini biar gampang di-gate per role.
    $cu = request()->attributes->get('admin_user');

    $nav = [
        [
            'label' => 'Presensi',
            'items' => [
                ['label' => 'Ringkasan hari ini', 'href' => route('admin.dashboard'), 'ico' => '📊', 'on' => request()->routeIs('admin.dashboard')],
                ['label' => 'Riwayat absen & export', 'href' => route('admin.absensi'), 'ico' => '🗂️', 'on' => request()->routeIs('admin.absensi')],
            ],
        ],
        [
            'label' => 'Master & alat',
            'items' => [
                ['label' => 'Kelola wajah & karyawan', 'href' => '/kelola-wajah', 'ico' => '🧑‍💼', 'on' => request()->is('kelola-wajah*')],
                ['label' => 'Halaman absen SPG', 'href' => '/', 'ico' => '📱', 'on' => false, 'blank' => true],
            ],
        ],
    ];

    // Menu khusus role admin — manager/supervisor nggak lihat ini.
    if ($cu && $cu->isAdmin()) {
        $nav[] = [
            'label' => 'Khusus admin',
            'items' => [
                ['label' => 'Kelola akun admin', 'href' => route('admin.pengguna'), 'ico' => '👥', 'on' => request()->routeIs('admin.pengguna')],
            ],
        ];
    }

    // Semua role yang bisa login boleh ganti password sendiri.
    $nav[] = [
        'label' => 'Akun saya',
        'items' => [
            ['label' => 'Keamanan akun', 'href' => route('admin.akun'), 'ico' => '🔒', 'on' => request()->routeIs('admin.akun')],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#05080d">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') · Admin Presensi SPG</title>
<style>
:root{--bg:#05080d;--card:#0d1521;--card2:#0a111b;--line:rgba(151,181,217,.18);--line2:rgba(151,181,217,.32);--txt:#e8f0f9;--dim:#8ba0b8;--dim2:#647a92;--acc:#34d399;--acc-dk:#052e16;--warn:#f87171;--mid:#fbbf24;--sky:#38bdf8;--vio:#a78bfa;--mono:ui-monospace,SFMono-Regular,Menlo,Consolas,"Liberation Mono",monospace;--r:14px;--r2:11px}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
body{margin:0;background:radial-gradient(900px 420px at 50% -8%,rgba(56,189,248,.07),transparent 65%),var(--bg);color:var(--txt);font:15px/1.5 system-ui,"Segoe UI",Roboto,sans-serif;min-height:100dvh}
a{color:var(--sky);text-decoration:none}
/* ---------- sidebar ---------- */
.side{position:fixed;top:0;left:0;bottom:0;width:248px;padding:18px 14px;background:#080d15;border-right:1px solid var(--line);display:flex;flex-direction:column;gap:4px;z-index:60;overflow-y:auto}
.brand{display:flex;align-items:center;gap:11px;padding:4px 8px 14px;border-bottom:1px solid var(--line);margin-bottom:10px}
.logo{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#0ea56b,#34d399);display:flex;align-items:center;justify-content:center;font-size:19px;flex:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.3)}
.brand b{display:block;font-size:13.5px}
.brand span{font-size:10px;color:var(--dim);letter-spacing:.14em;text-transform:uppercase}
.navlab{font-size:9.5px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:var(--dim);padding:14px 10px 6px}
.nav{display:flex;align-items:center;gap:11px;padding:11px 12px;border-radius:12px;color:var(--dim);font-size:13.5px;font-weight:600;border:1px solid transparent;transition:all .16s}
.nav i{font-style:normal;font-size:15px;width:20px;text-align:center;flex:none;opacity:.72}
.nav:hover{color:var(--txt);background:rgba(151,181,217,.08)}
.nav.on{color:var(--acc);background:rgba(52,211,153,.1);border-color:rgba(52,211,153,.32)}
.nav.on i{opacity:1}
/* saklar fitur di sidebar */
button.nav.feat{width:100%;font:inherit;text-align:left;cursor:pointer;background:transparent}
button.nav.feat .fnm{flex:1;min-width:0;display:flex;flex-direction:column;line-height:1.25}
button.nav.feat .fnm small{font-size:9.5px;letter-spacing:.08em;text-transform:uppercase;color:var(--dim);font-weight:700}
button.nav.feat[data-on="1"] .fnm small{color:var(--acc)}
button.nav.feat[data-on="1"]{color:var(--txt);border-color:rgba(52,211,153,.22);background:rgba(52,211,153,.06)}
.fsw{position:relative;width:34px;height:19px;border-radius:999px;background:rgba(151,181,217,.2);border:1px solid var(--line);flex:none;transition:background .18s}
.fsw::after{content:"";position:absolute;top:2px;left:2px;width:13px;height:13px;border-radius:50%;background:var(--dim);transition:transform .18s,background .18s}
button.nav.feat[data-on="1"] .fsw{background:rgba(52,211,153,.28)}
button.nav.feat[data-on="1"] .fsw::after{transform:translateX(15px);background:var(--acc)}
button.nav.feat[disabled]{opacity:.55;cursor:wait}
.side footer{margin-top:auto;padding:14px 10px 0;border-top:1px solid var(--line);font-size:10.5px;color:var(--dim);line-height:1.65}
/* ---------- main ---------- */
.main{margin-left:248px;padding:0 24px 70px;max-width:1440px}
.top{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;flex-wrap:wrap;position:sticky;top:0;z-index:40;margin:0 -24px 18px;padding:16px 24px 12px;background:linear-gradient(180deg,var(--bg) 72%,rgba(5,8,13,0));border-bottom:1px solid var(--line)}
h1{font-size:19px;margin:0}
.sub{margin:4px 0 0;font-size:12px;color:var(--dim)}
.acts{display:flex;gap:8px;flex-wrap:wrap}
.btn{padding:10px 16px;border-radius:var(--r2);border:1px solid rgba(52,211,153,.35);background:rgba(52,211,153,.13);color:#a7f3d0;font:inherit;font-size:12.5px;font-weight:700;cursor:pointer;white-space:nowrap;transition:background .15s,border-color .15s}
.btn:hover{background:rgba(52,211,153,.2);border-color:rgba(52,211,153,.5)}
.btn:disabled{opacity:.5;cursor:not-allowed}
.btn.ghost{border-color:var(--line);background:var(--card2);color:var(--dim)}
.btn.ghost:hover{color:var(--txt);border-color:var(--line2);background:var(--card)}
.btn.sky{border-color:rgba(56,189,248,.35);background:rgba(56,189,248,.12);color:#bae6fd}
.btn.sky:hover{background:rgba(56,189,248,.18)}
.btn.warn{border-color:rgba(248,113,113,.35);background:rgba(248,113,113,.12);color:#fca5a5}
.btn.warn:hover{background:rgba(248,113,113,.18)}
.card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:16px;margin-bottom:14px}
.card h2{margin:0 0 12px;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--dim);display:flex;align-items:center;justify-content:space-between;gap:10px}
.burger{position:fixed;top:12px;left:12px;z-index:80;display:none;width:42px;height:42px;border-radius:12px;border:1px solid var(--line);background:rgba(9,15,25,.94);color:var(--txt);font-size:17px;cursor:pointer}
.backdrop{display:none;position:fixed;inset:0;background:rgba(2,5,9,.62);z-index:55}
.table-wrap{overflow-x:auto;margin:0 -4px}
table{width:100%;border-collapse:collapse;font-size:12.5px}
th{text-align:left;font-size:9.5px;letter-spacing:.1em;text-transform:uppercase;color:var(--dim);padding:8px 9px;border-bottom:1px solid var(--line);white-space:nowrap}
td{padding:9px;border-bottom:1px solid rgba(151,181,217,.1);vertical-align:middle}
tbody tr:hover{background:rgba(151,181,217,.06)}
.mono{font-family:var(--mono)}
.dim{color:var(--dim)}
.nowrap{white-space:nowrap}
.pill{display:inline-block;font-size:9.5px;font-weight:800;letter-spacing:.05em;padding:3px 8px;border-radius:999px;border:1px solid transparent;white-space:nowrap;text-transform:uppercase}
.p-ok{background:rgba(52,211,153,.14);color:#6ee7b7;border-color:rgba(52,211,153,.3)}
.p-sky{background:rgba(56,189,248,.14);color:#7dd3fc;border-color:rgba(56,189,248,.3)}
.p-warn{background:rgba(248,113,113,.14);color:#fca5a5;border-color:rgba(248,113,113,.3)}
.p-mid{background:rgba(251,191,36,.14);color:#fcd34d;border-color:rgba(251,191,36,.3)}
.p-mute{background:rgba(151,181,217,.13);color:var(--dim);border-color:var(--line)}
.empty{padding:26px;text-align:center;color:var(--dim);font-size:12.5px}
.toast{position:fixed;right:18px;bottom:18px;z-index:200;padding:12px 15px;border-radius:var(--r2);background:#101a28;border:1px solid var(--line2);font-size:12.5px;max-width:360px;display:none}
.toast.ok{border-color:rgba(52,211,153,.4);color:#bbf7d0}
.toast.err{border-color:rgba(248,113,113,.45);color:#fecaca}
/* ---------- form & modal (dipakai bareng semua halaman admin) ---------- */
.fld{display:flex;flex-direction:column;gap:5px;min-width:0}
.fld>span,.fld label{font-size:9.5px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--dim)}
.fld input,.fld select,.fld textarea{width:100%;padding:10px 12px;border-radius:var(--r2);border:1px solid var(--line);background:var(--card2);color:var(--txt);font:inherit;font-size:13px}
.fld input:focus,.fld select:focus,.fld textarea:focus{outline:none;border-color:rgba(56,189,248,.55);box-shadow:0 0 0 3px rgba(56,189,248,.12)}
.fld input::placeholder,.fld textarea::placeholder{color:#4a5c70}
.fld select option{background:var(--card2);color:var(--txt)}
.fgrid{display:grid;grid-template-columns:1fr;gap:11px}
@media (min-width:820px){.fgrid{grid-template-columns:1fr 1fr}.fgrid .span2{grid-column:1/-1}}
.mfoot{display:flex;gap:8px;justify-content:flex-end;margin-top:15px}
.tiny{font-size:11px;color:var(--dim)}
.mini{padding:6px 10px;border-radius:8px;border:1px solid var(--line);background:var(--card2);color:var(--dim);font:inherit;font-size:11px;font-weight:700;cursor:pointer;transition:color .15s,border-color .15s;white-space:nowrap}
.mini:hover{color:var(--txt);border-color:var(--line2)}
.mini.warn{border-color:rgba(248,113,113,.3);color:#fca5a5}
.mini.warn:hover{background:rgba(248,113,113,.12)}
.modal-bg{position:fixed;inset:0;background:rgba(2,5,9,.74);z-index:150;display:none;align-items:flex-start;justify-content:center;padding:26px 14px;overflow-y:auto}
.modal-bg.on{display:flex}
.modal{width:100%;max-width:540px;background:#101a28;border:1px solid var(--line2);border-radius:var(--r);padding:18px}
.modal h3{margin:0 0 14px;font-size:15px;display:flex;justify-content:space-between;gap:10px;align-items:center}
.modal .close{background:none;border:0;color:var(--dim);font-size:20px;cursor:pointer;line-height:1}
.modal .close:hover{color:var(--txt)}
@media (max-width:980px){
.side{transform:translateX(-100%);transition:transform .22s;width:264px}
.side.open{transform:none}
.backdrop.on{display:block}
.burger{display:block}
.main{margin-left:0;padding:56px 14px 70px}
.top{margin:0 -14px 16px;padding:14px 14px 11px}
}
</style>
@stack('head')
</head>
<body>
<button class="burger" id="burger" aria-label="Buka menu">☰</button>
<aside class="side" id="side">
  <div class="brand">
    <div class="logo">🪪</div>
    <div><b>Presensi SPG</b><span>Admin panel</span></div>
  </div>
  @foreach ($nav as $group)
  <div class="navlab">{{ $group['label'] }}</div>
  @foreach ($group['items'] as $n)
  <a class="nav {{ $n['on'] ? 'on' : '' }}" href="{{ $n['href'] }}" @if (! empty($n['blank'])) target="_blank" rel="noopener" @endif><i>{{ $n['ico'] }}</i> {{ $n['label'] }}</a>
  @endforeach
  @endforeach
    @if ($cu && $cu->isAdmin())
    <div class="navlab">Fitur</div>
    @foreach (\App\Support\Features::list() as $f)
    <button type="button" class="nav feat" data-feat="{{ $f['key'] }}" data-on="{{ $f['on'] ? '1' : '0' }}" title="{{ $f['ket'] }}">
      <i>{{ $f['on'] ? '🟢' : '⚪' }}</i>
      <span class="fnm">{{ $f['label'] }}<small>{{ $f['on'] ? 'aktif' : 'nonaktif' }}</small></span>
      <span class="fsw" aria-hidden="true"></span>
    </button>
    @endforeach
    @endif
  <footer>
    @if ($cu)
      <div style="margin-bottom:10px;padding:10px 12px;border:1px solid var(--line);border-radius:12px;display:flex;align-items:center;gap:10px">
        <div class="logo" style="width:34px;height:34px;font-size:15px;border-radius:10px;flex:none">{{ strtoupper(substr($cu->name, 0, 1)) }}</div>
        <div style="min-width:0;flex:1">
          <div style="font-size:12.5px;font-weight:700;color:var(--txt);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cu->name }}</div>
          <div style="font-size:10px;color:var(--acc);text-transform:uppercase;letter-spacing:.08em">{{ $cu->role }}</div>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}" style="margin:0">
          @csrf
          <button type="submit" title="Keluar" style="padding:7px 9px;border-radius:9px;border:1px solid var(--line);background:transparent;color:var(--dim);cursor:pointer;font-size:13px;line-height:1">⏻</button>
        </form>
      </div>
    @endif
    Zona waktu: {{ config('app.timezone') }} (WIB UTC+7)<br>
    Radius default: {{ (int) config('faceid.radius') }} m<br>
    Face ID: {{ \App\Support\Features::on('faceid') ? 'aktif' : 'nonaktif' }}
  </footer>
</aside>
<div class="backdrop" id="backdrop"></div>
<main class="main">
  <header class="top">
    <div>
      <h1>@yield('heading', 'Dashboard')</h1>
      <p class="sub">@yield('sub')</p>
    </div>
    <div class="acts">@yield('actions')</div>
  </header>
  @yield('content')
</main>
<div class="toast" id="toast"></div>
<script>
const $ = (id) => document.getElementById(id);
let CSRF = document.querySelector('meta[name="csrf-token"]').content;
let toastTimer = null;
function toast(msg, kind) {
  const t = $('toast');
  t.textContent = msg;
  t.className = 'toast ' + (kind || 'ok');
  t.style.display = 'block';
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.style.display = 'none'; }, 4200);
}
function esc(s) {
  return String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}
async function api(url, body) {
  const send = () => fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    body: JSON.stringify(body || {}),
  });
  let r = await send();
  if (r.status === 419) {
    // Token CSRF ke-rotate sama POST terakhir -> ambil ulang dari halaman, lalu coba lagi.
    try {
      const html = await (await fetch(window.location.href, { headers: { Accept: 'text/html' } })).text();
      const m = html.match(/name="csrf-token" content="([^"]+)"/);
      if (m) {
        CSRF = m[1];
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', CSRF);
        r = await send();
      }
    } catch (e) { /* fallthrough ke error di bawah */ }
  }
  const j = await r.json().catch(() => ({}));
  if (!r.ok || j.ok === false) throw new Error(j.message || ('HTTP ' + r.status));
  return j;
}
$('burger').onclick = () => { $('side').classList.add('open'); $('backdrop').classList.add('on'); };
$('backdrop').onclick = () => { $('side').classList.remove('open'); $('backdrop').classList.remove('on'); };

// ---------- saklar fitur di sidebar (admin only) ----------
function paintFeatures(list) {
  (list || []).forEach((f) => {
    const b = document.querySelector('button.feat[data-feat="' + f.key + '"]');
    if (!b) return;
    b.dataset.on = f.on ? '1' : '0';
    b.querySelector('i').textContent = f.on ? '🟢' : '⚪';
    b.querySelector('.fnm small').textContent = f.on ? 'aktif' : 'nonaktif';
  });
}
document.querySelectorAll('button.feat').forEach((b) => {
  b.onclick = async () => {
    const on = b.dataset.on !== '1';
    b.disabled = true;
    try {
      const j = await api('/admin/fitur/' + b.dataset.feat, { on });
      paintFeatures(j.features);
      toast(j.message, 'ok');
    } catch (e) { toast(e.message, 'err'); }
    b.disabled = false;
  };
});
</script>
@stack('scripts')
</body>
</html>