<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#05080d">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Masuk · Admin Presensi SPG</title>
<style>
  :root{--bg:#05080d;--card:#0a111b;--line:rgba(148,178,214,.14);--txt:#e8f0f9;--dim:#7f93a9;--acc:#34d399;--sky:#38bdf8;--warn:#f87171;--mono:ui-monospace,SFMono-Regular,Menlo,Consolas,"Liberation Mono",monospace}
  *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
  html,body{margin:0;padding:0}
  body{min-height:100dvh;display:flex;align-items:center;justify-content:center;background:radial-gradient(1200px 620px at 12% -12%,rgba(52,211,153,.12),transparent 60%),radial-gradient(900px 520px at 100% 0,rgba(56,189,248,.10),transparent 55%),var(--bg);font:15px/1.5 system-ui,"Segoe UI",Roboto,sans-serif;color:var(--txt);padding:20px}
  .card{width:min(420px,100%);background:rgba(15,22,34,.72);border:1px solid var(--line);border-radius:18px;padding:26px 24px 22px;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);box-shadow:0 22px 50px rgba(0,0,0,.45)}
  .brand{display:flex;align-items:center;gap:11px;margin-bottom:18px}
  .logo{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,#0ea56b,#34d399);display:flex;align-items:center;justify-content:center;font-size:21px;flex:none;box-shadow:0 6px 18px rgba(52,211,153,.28)}
  h1{font-size:17px;margin:0 0 4px}
  .sub{margin:0;font-size:12px;color:var(--dim)}
  label.fld{display:block;font-size:11.5px;color:var(--dim);margin:14px 0 6px;text-transform:uppercase;letter-spacing:.08em}
  input[type=text],input[type=email],input[type=password]{width:100%;padding:11px 13px;border-radius:11px;border:1px solid var(--line);background:#0a111b;color:var(--txt);font:inherit;font-size:14.5px;outline:none;transition:border-color .14s,box-shadow .14s}
  input[type=text]:focus,input[type=email]:focus,input[type=password]:focus{border-color:rgba(56,189,248,.55);box-shadow:0 0 0 3px rgba(56,189,248,.12)}
  .err{border-color:rgba(248,113,113,.55)!important;box-shadow:0 0 0 3px rgba(248,113,113,.14)!important}
  .pw{position:relative}
  .pw input{padding-right:46px}
  .eye{position:absolute;top:50%;right:5px;transform:translateY(-50%);width:36px;height:34px;border:0;border-radius:9px;background:transparent;color:var(--dim);font-size:15px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center}
  .eye:hover,.eye[aria-pressed=true]{color:var(--txt);background:rgba(148,178,214,.12)}
  .msg{padding:11px 13px;border-radius:11px;background:rgba(248,113,113,.10);border:1px solid rgba(248,113,113,.3);color:#fecaca;font-size:13px;margin:6px 0 4px}
  .btn{width:100%;margin-top:8px;padding:12px;border:0;border-radius:12px;font:inherit;font-size:14px;font-weight:700;cursor:pointer;background:linear-gradient(180deg,#0ea56b,#059669);color:#022c22;box-shadow:0 6px 18px rgba(52,211,153,.28)}
  .btn:hover{filter:brightness(1.06)}
  .foot{margin-top:18px;font-size:11.5px;color:var(--dim);line-height:1.6}
  .foot b{color:var(--dim);font-weight:600}
</style>
</head>
<body>
  <div class="card">
    <div class="brand">
      <div class="logo">🪪</div>
      <div>
        <h1>Presensi SPG</h1>
        <p class="sub">Admin panel</p>
      </div>
    </div>

    <h1>Masuk ke admin</h1>
    <p class="sub">Hanya untuk staf pengelola absensi.</p>

    @if ($errors->has('email') || Session::has('login_error'))
      <div class="msg">{{ $errors->first('email') ?? Session::pull('login_error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}" novalidate>
      @csrf
      <label class="fld" for="email">Alamat email</label>
      <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required>

      <label class="fld" for="password">Kata sandi</label>
      <div class="pw">
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="button" class="eye" id="eye" aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi">👁️</button>
      </div>

      <button type="submit" class="btn">Masuk</button>
    </form>

    <div class="foot">
      Bantuan admin: hubungi penanggung jawab sistem.<br>
      <b>Role akses:</b> admin · manager · supervisor
      {{-- Hint kredensial cuma muncul di dev (APP_DEBUG=true). Di produksi baris ini hilang. --}}
      @if (config('app.debug'))
        <br><b>Dev:</b> {{ config('faceid.admin_email') }} / {{ config('faceid.admin_password') ?: 'admin123' }}
      @endif
    </div>
  </div>
  <script>
    (function () {
      var pw = document.getElementById('password'), eye = document.getElementById('eye');
      // Takarir: mata nggak boleh ngilangin fokus input (biar alur isi form gak keganggu).
      eye.addEventListener('mousedown', function (e) { e.preventDefault(); });
      eye.addEventListener('click', function () {
        var tampil = pw.type === 'password';
        pw.type = tampil ? 'text' : 'password';
        eye.setAttribute('aria-pressed', String(tampil));
        eye.title = tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';
        eye.textContent = tampil ? '🙈' : '👁️';
        pw.focus();
      });
    })();
  </script>
</body>
</html>
