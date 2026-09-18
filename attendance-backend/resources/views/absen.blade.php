<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#05080d">
<title>Presensi SPG</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<style>
:root{
--bg:#05080d; --card:rgba(15,22,34,.62); --card2:#0a111b; --line:rgba(148,178,214,.14);
--txt:#e8f0f9; --dim:#7f93a9; --acc:#34d399; --acc-dk:#052e16; --warn:#f87171; --mid:#fbbf24; --sky:#38bdf8; --vio:#a78bfa;
--mono:ui-monospace,SFMono-Regular,Menlo,Consolas,"Liberation Mono",monospace;
--r:18px; --glass-blur:blur(16px) saturate(1.5);
}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html{-webkit-text-size-adjust:100%}
body{margin:0;min-height:100vh;padding:14px 14px 112px;background:var(--bg);color:var(--txt);font:15px/1.5 system-ui,"Segoe UI",Roboto,sans-serif;overflow-x:hidden}
/* ---------- aurora + grid backdrop ---------- */
.bgfx{position:fixed;inset:0;z-index:-1;overflow:hidden;pointer-events:none}
.bgfx .blob{position:absolute;border-radius:50%;filter:blur(70px);opacity:.5}
.blob-a{width:340px;height:340px;background:radial-gradient(circle,#0ea56b,transparent 65%);top:-120px;left:-90px;animation:drift 16s ease-in-out infinite alternate}
.blob-b{width:300px;height:300px;background:radial-gradient(circle,#2563eb,transparent 65%);top:8%;right:-110px;animation:drift 20s ease-in-out infinite alternate-reverse}
.blob-c{width:260px;height:260px;background:radial-gradient(circle,#7c3aed,transparent 65%);bottom:-100px;left:30%;opacity:.32;animation:drift 24s ease-in-out infinite alternate}
@keyframes drift{from{transform:translate(0,0) scale(1)}to{transform:translate(30px,42px) scale(1.15)}}
.bgfx .grid{position:absolute;inset:0;background-image:linear-gradient(rgba(148,178,214,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(148,178,214,.05) 1px,transparent 1px);background-size:34px 34px;mask-image:radial-gradient(ellipse 90% 55% at 50% 0%,#000 35%,transparent 78%);-webkit-mask-image:radial-gradient(ellipse 90% 55% at 50% 0%,#000 35%,transparent 78%)}
.wrap{max-width:440px;margin:0 auto}
/* ---------- header ---------- */
header.top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:4px 0 14px}
.brand{display:flex;align-items:center;gap:11px;min-width:0}
.logo{width:42px;height:42px;border-radius:13px;background:linear-gradient(135deg,#0ea56b,#34d399);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 22px rgba(52,211,153,.3),inset 0 1px 0 rgba(255,255,255,.35);flex:none}
.logo svg{width:23px;height:23px}
h1{font-size:17px;margin:0;letter-spacing:.01em}
.sub{margin:0;font-size:10px;color:var(--dim);font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase}
.chip{font-size:11px;color:var(--dim);border:1px solid var(--line);border-radius:999px;padding:6px 11px;background:var(--card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);white-space:nowrap}
.chip b{color:var(--acc);font-weight:700;font-family:var(--mono)}
/* ---------- status bar (GPS / IP / NET) ---------- */
.statusbar{display:flex;gap:8px;margin-bottom:12px}
.stchip{flex:1;display:flex;align-items:center;gap:7px;min-width:0;padding:9px 11px;border-radius:12px;background:var(--card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);border:1px solid var(--line);box-shadow:inset 0 1px 0 rgba(255,255,255,.05)}
.stchip .v{color:var(--txt);font-family:var(--mono);font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:0}
.sdot{width:7px;height:7px;border-radius:50%;background:var(--dim);flex:none;transition:background .3s}
.sdot.ok{background:var(--acc);box-shadow:0 0 8px rgba(52,211,153,.8)}
.sdot.mid{background:var(--mid);box-shadow:0 0 8px rgba(251,191,36,.7)}
.sdot.bad{background:var(--warn);box-shadow:0 0 8px rgba(248,113,113,.7)}
.sdot.live{animation:blink 1.6s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
/* ---------- card glass ---------- */
section.card{position:relative;background:var(--card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);border:1px solid var(--line);border-radius:var(--r);padding:16px;margin-bottom:12px;box-shadow:0 18px 44px rgba(0,0,0,.4)}
section.card::before{content:"";position:absolute;top:0;left:12%;right:12%;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.28),transparent);border-radius:1px}
h2{font-size:11px;margin:0 0 12px;color:var(--dim);font-weight:700;text-transform:uppercase;letter-spacing:.14em}

/* ---------- stepper ---------- */
.steps{display:flex;align-items:center;gap:7px;margin-bottom:14px}
.step{display:flex;align-items:center;gap:6px}
.snum{width:24px;height:24px;border-radius:50%;border:1.5px solid var(--line);background:var(--card2);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--dim);transition:all .25s;flex:none}
.slbl{font-size:11px;font-weight:600;color:var(--dim)}
.step.active .snum{border-color:var(--sky);color:var(--sky);box-shadow:0 0 0 3px rgba(56,189,248,.15)}
.step.active .slbl{color:var(--sky)}
.step.done .snum{background:var(--acc);border-color:var(--acc);color:var(--acc-dk);font-size:0}
.step.done .snum::after{content:"✓";font-size:12px}
.step.done .slbl{color:var(--txt)}
.sline{height:2px;flex:1;background:var(--line);border-radius:2px;transition:background .25s}
.sline.done{background:var(--acc)}
/* ---------- dropdown futuristis ---------- */
.fld{display:block;margin-bottom:12px}
.dd-lbl{display:block;font-size:10px;font-weight:700;color:var(--dim);letter-spacing:.14em;margin:0 0 6px 2px;text-transform:uppercase}
.dd{position:relative}
.dd-btn{width:100%;display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:14px;border:1px solid var(--line);background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.015)),var(--card2);color:var(--txt);font-size:14px;font-family:inherit;cursor:pointer;text-align:left;transition:border-color .2s,box-shadow .2s}
.dd-btn:disabled{opacity:.45;cursor:not-allowed}
.dd-btn:not(:disabled):hover{border-color:rgba(148,178,214,.32)}
.dd.open .dd-btn{border-color:rgba(56,189,248,.55);box-shadow:0 0 0 1px rgba(56,189,248,.35),0 0 26px rgba(56,189,248,.16)}
.dd-ico{width:34px;height:34px;border-radius:10px;background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.22);display:flex;align-items:center;justify-content:center;font-size:15px;flex:none}
.dd-val{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dd.empty .dd-val{color:var(--dim)}
.dd-chev{flex:none;color:var(--dim);transition:transform .25s,color .25s}
.dd.open .dd-chev{transform:rotate(180deg);color:var(--sky)}
.dd-panel{position:absolute;left:0;right:0;top:calc(100% + 8px);z-index:400;border-radius:16px;border:1px solid rgba(148,178,214,.22);background:rgba(9,15,25,.94);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);box-shadow:0 26px 60px rgba(0,0,0,.55),inset 0 1px 0 rgba(255,255,255,.07);overflow:hidden;transform-origin:top center;transform:scale(.97) translateY(-4px);opacity:0;transition:transform .16s ease,opacity .16s ease}
.dd-panel.in{transform:none;opacity:1}
.dd-panel::before{content:"";position:absolute;top:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,rgba(56,189,248,.75),transparent);z-index:1}
.dd-searchwrap{padding:8px 8px 6px;border-bottom:1px solid var(--line)}
.dd-search{width:100%;padding:9px 11px;border-radius:9px;border:1px solid var(--line);background:var(--card2);color:var(--txt);font-size:13px;font-family:var(--mono)}
.dd-search:focus{outline:none;border-color:rgba(56,189,248,.5)}
.dd-list{max-height:262px;overflow-y:auto;padding:6px}
.dd-list::-webkit-scrollbar{width:4px}
.dd-list::-webkit-scrollbar-thumb{background:rgba(148,178,214,.25);border-radius:4px}
.dd-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;cursor:pointer;position:relative;animation:ddin .18s both}
@keyframes ddin{from{opacity:0;transform:translateX(-6px)}to{opacity:1;transform:none}}
.dd-item::before{content:"";position:absolute;left:0;top:20%;bottom:20%;width:2px;border-radius:2px;background:transparent;transition:background .15s}
.dd-item:hover,.dd-item.hl{background:rgba(56,189,248,.08)}
.dd-item:hover::before,.dd-item.hl::before{background:var(--sky)}
.dd-item.sel{background:rgba(52,211,153,.1)}
.dd-item.sel::before{background:var(--acc)}
.dd-item .li{flex:1;min-width:0}
.dd-item .nm{font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dd-item .sb{font-size:10px;color:var(--dim);font-family:var(--mono);margin-top:1px}
.dd-item .tick{color:var(--acc);font-weight:800;flex:none}
.dd-empty{padding:18px;text-align:center;color:var(--dim);font-size:12px}

/* ---------- skeleton shimmer ---------- */
.sk{height:38px;border-radius:10px;margin:6px;background:linear-gradient(90deg,rgba(148,178,214,.07) 25%,rgba(148,178,214,.16) 50%,rgba(148,178,214,.07) 75%);background-size:200% 100%;animation:shim 1.1s infinite}
@keyframes shim{from{background-position:200% 0}to{background-position:-200% 0}}
/* ---------- ID card ---------- */
#idcard{display:none;align-items:center;gap:12px;margin-top:14px;padding:12px 14px;background:linear-gradient(135deg,rgba(52,211,153,.14),rgba(52,211,153,.03));border:1px solid rgba(52,211,153,.35);border-radius:14px;animation:slidein .25s ease}
@keyframes slidein{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.ava{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#10b981,#34d399);color:#022c22;font-weight:800;font-size:15px;display:flex;align-items:center;justify-content:center;flex:none;box-shadow:0 4px 14px rgba(52,211,153,.3)}
.idc{flex:1;min-width:0}
.idc-name{font-size:15px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.idc-store{font-size:11px;color:var(--dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.idc-code{display:flex;align-items:baseline;gap:8px;margin-top:3px}
.idc-code .t{font-size:9px;color:var(--dim);text-transform:uppercase;letter-spacing:.12em}
.idc-code .code{font-size:17px;font-weight:800;color:var(--acc);letter-spacing:.06em;font-family:var(--mono)}
/* ---------- kamera & wajah ---------- */
.cam-wrap{position:relative;width:100%;max-width:320px;margin:0 auto;aspect-ratio:3/4;border-radius:var(--r);overflow:hidden;background:var(--card2);border:1px solid var(--line)}
#cam{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transform:scaleX(-1)}
.face-guide{position:absolute;inset:0;width:100%;height:100%;pointer-events:none}
.face-guide .face-outline{stroke:#7dd3fc;transition:stroke .2s}
.face-guide.ok .face-outline{stroke:var(--acc);filter:drop-shadow(0 0 6px rgba(52,211,153,.85))}
#camState{position:absolute;bottom:8px;left:0;right:0;text-align:center;color:#cbd5e1;font-size:11px;text-shadow:0 1px 3px #000}
#faceResult{display:none;margin-top:10px;padding:9px 12px;border-radius:10px;font-size:12px;font-weight:600}
#faceResult.ok{display:block;background:var(--acc-dk);color:#86efac}
#btnScan{width:100%;margin-top:10px;padding:13px;border:0;border-radius:12px;font-size:14px;font-weight:700;cursor:pointer;background:var(--sky);color:#082f49;transition:transform .1s,opacity .2s}
#btnScan:active{transform:scale(.98)}
#btnScan:disabled{opacity:.5;cursor:not-allowed}
/* ---------- peta & zona ---------- */
#map{height:240px;border-radius:12px;overflow:hidden;background:var(--card2);border:1px solid var(--line)}
#mapFallback{display:flex;align-items:center;justify-content:center;height:100%;color:var(--dim);font-size:12px}
#zone{display:none;margin-top:8px;font-size:12px;font-weight:600;padding:9px 12px;border-radius:10px}
.zone-in{display:block;background:var(--acc-dk);color:#86efac;border:1px solid rgba(52,211,153,.3)}
.zone-out{display:block;background:#2f0d0d;color:#fca5a5;border:1px solid rgba(248,113,113,.3)}
#loc{color:var(--dim);font-size:11px;margin-top:7px;font-family:var(--mono)}
details.tes{margin-top:10px}
details.tes summary{color:var(--dim);font-size:11px;cursor:pointer}
.row{display:flex;gap:8px;margin-top:10px}
.mini{flex:1;font-size:11px;font-weight:600;padding:9px;background:var(--card2);color:var(--dim);border:1px solid var(--line);border-radius:9px;cursor:pointer}
.mini:hover{color:var(--txt)}
/* ---------- msg & riwayat ---------- */
#msg{display:none;margin-top:10px;padding:10px 12px;border-radius:10px;font-size:12px;line-height:1.45}
.ok{background:var(--acc-dk);color:#86efac}
.err{background:#2f0d0d;color:#fca5a5}
#hist{margin-top:10px;font-size:12px}
#hist .h{color:var(--dim);font-size:10px;text-transform:uppercase;letter-spacing:.14em;margin-bottom:4px}
#hist div.r{padding:7px 0;border-bottom:1px solid var(--line);color:var(--dim);display:flex;align-items:center;gap:8px}
#hist div.r:last-child{border-bottom:0}
#hist b{color:var(--txt);font-family:var(--mono);font-size:12px;font-weight:600}
.badge{font-size:9px;font-weight:800;letter-spacing:.08em;padding:3px 7px;border-radius:6px;font-family:var(--mono)}
.b-masuk{background:rgba(52,211,153,.14);color:#6ee7b7}
.b-pulang{background:rgba(251,191,36,.14);color:#fcd34d}
.rip{margin-left:auto;font-family:var(--mono);font-size:10px;color:var(--dim);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:45%}

/* ---------- action bar (sticky) ---------- */
.actionbar{position:fixed;left:0;right:0;bottom:0;z-index:1500;display:flex;gap:10px;padding:12px 14px calc(12px + env(safe-area-inset-bottom));background:rgba(5,8,13,.88);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border-top:1px solid var(--line)}
.actionbar::before{content:"";position:absolute;top:-1px;left:14%;right:14%;height:1px;background:linear-gradient(90deg,transparent,rgba(52,211,153,.5),transparent)}
.cta{flex:1;padding:14px;border:0;border-radius:14px;font-size:15px;font-weight:800;cursor:pointer;transition:transform .1s,opacity .2s}
.cta:active{transform:scale(.97)}
.cta:disabled{opacity:.3;cursor:not-allowed}
#btnMasuk{background:linear-gradient(135deg,#10b981,#34d399);color:#022c22;box-shadow:0 6px 22px rgba(52,211,153,.25)}
#btnPulang{background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#422006;box-shadow:0 6px 22px rgba(251,191,36,.2)}
/* ---------- modal + struk ---------- */
footer{color:var(--dim);font-size:10px;text-align:center;margin:4px 0 10px;line-height:1.6}
#modal{position:fixed;inset:0;background:rgba(2,5,9,.8);display:none;align-items:center;justify-content:center;z-index:2000;padding:20px}
#modal.show{display:flex}
.modal{background:rgba(13,20,31,.97);backdrop-filter:var(--glass-blur);border:1px solid var(--line);border-radius:20px;padding:28px 20px;max-width:340px;width:100%;text-align:center;animation:popin .18s ease}
.modal.success{border-color:rgba(52,211,153,.5)}
.modal.error{border-color:rgba(248,113,113,.5)}
.modal.info{border-color:rgba(251,191,36,.5)}
@keyframes popin{from{transform:scale(.85);opacity:0}to{transform:scale(1);opacity:1}}
.micon{width:64px;height:64px;margin:0 auto;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px;background:var(--card2);border:1px solid var(--line)}
.modal.success .micon{background:var(--acc-dk);border-color:rgba(52,211,153,.4)}
.modal.error .micon{background:#2f0d0d;border-color:rgba(248,113,113,.4)}
.modal.info .micon{background:#3a2a05;border-color:rgba(251,191,36,.4)}
#mTitle{font-size:17px;font-weight:800;margin-top:14px}
#mDesc{color:var(--dim);font-size:13px;margin-top:6px;line-height:1.55}
.mrows{margin-top:14px;border:1px solid var(--line);border-radius:12px;overflow:hidden;background:rgba(10,17,27,.8);text-align:left}
.mrow{display:flex;justify-content:space-between;align-items:baseline;gap:12px;padding:8px 12px;border-bottom:1px solid var(--line);font-size:12px}
.mrow:last-child{border-bottom:0}
.mrow .k{color:var(--dim);flex:none}
.mrow .v{font-family:var(--mono);font-size:11px;text-align:right;word-break:break-all;color:var(--txt)}
#mOk{margin-top:20px;width:100%;padding:13px;background:var(--acc);color:var(--acc-dk);border:0;border-radius:12px;font-weight:800;cursor:pointer}
/* ---------- pins peta ---------- */
.store-pin,.user-pin{background:transparent;border:0}
.store-pin-dot{width:13px;height:13px;border-radius:50%;background:var(--acc);border:3px solid #064e3b;box-shadow:0 0 8px rgba(52,211,153,.8)}
.user-dot{width:13px;height:13px;border-radius:50%;background:var(--sky);border:3px solid #e0f2fe;animation:pulse 1.6s infinite}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(56,189,248,.55)}70%{box-shadow:0 0 0 12px rgba(56,189,248,0)}100%{box-shadow:0 0 0 0 rgba(56,189,248,0)}}
.leaflet-container{background:var(--card2)}
.leaflet-control-attribution{font-size:8px;background:rgba(5,8,13,.72);color:var(--dim)}
.leaflet-control-attribution a{color:var(--dim)}
@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms !important;animation-iteration-count:1 !important;transition-duration:.01ms !important}}
</style>
</head>

<body>
<div class="bgfx"><div class="blob blob-a"></div><div class="blob blob-b"></div><div class="blob blob-c"></div><div class="grid"></div></div>
<div class="wrap">
  <header class="top">
    <div class="brand">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9.2" stroke="#03291d" stroke-width="2.2"/><path d="M8 12.5l3 3 5.5-6" stroke="#03291d" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
      <div>
        <h1>Presensi SPG</h1>
        <p class="sub">face id · geofence · realtime</p>
      </div>
    </div>
    <span class="chip">Face ID <b id="faceState">…</b></span>
  </header>

  <div class="statusbar">
    <div class="stchip"><span class="sdot live" id="gpsDot"></span><span class="v" id="gpsVal">GPS…</span></div>
    <div class="stchip"><span class="sdot" id="ipDot"></span><span class="v" id="ipVal">IP…</span></div>
    <div class="stchip"><span class="sdot ok" id="netDot"></span><span class="v" id="netVal">ONLINE</span></div>
  </div>

  <section class="card">
    <div class="steps">
      <div class="step active" id="st1"><span class="snum">1</span><span class="slbl">Kota</span></div>
      <div class="sline" id="sl1"></div>
      <div class="step" id="st2"><span class="snum">2</span><span class="slbl">Toko</span></div>
      <div class="sline" id="sl2"></div>
      <div class="step" id="st3"><span class="snum">3</span><span class="slbl">Nama</span></div>
    </div>

    <label class="fld"><span class="dd-lbl">Kota</span>
      <div class="dd empty" id="ddCity">
        <button type="button" class="dd-btn" id="ddCityBtn" disabled>
          <span class="dd-ico">🏙️</span><span class="dd-val" id="ddCityVal">memuat…</span>
          <svg class="dd-chev" viewBox="0 0 12 8" width="12" height="8" fill="none"><path d="M1 1.5l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="dd-panel" id="ddCityPanel" hidden>
          <div class="dd-searchwrap" style="display:none"><input id="ddCitySearch" class="dd-search" placeholder="cari kota…" autocomplete="off"></div>
          <div class="dd-list" id="ddCityList" role="listbox"></div>
        </div>
      </div>
    </label>

    <label class="fld"><span class="dd-lbl">Toko</span>
      <div class="dd empty" id="ddStore">
        <button type="button" class="dd-btn" id="ddStoreBtn" disabled>
          <span class="dd-ico">🏬</span><span class="dd-val" id="ddStoreVal">pilih kota dulu…</span>
          <svg class="dd-chev" viewBox="0 0 12 8" width="12" height="8" fill="none"><path d="M1 1.5l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="dd-panel" id="ddStorePanel" hidden>
          <div class="dd-searchwrap" style="display:none"><input id="ddStoreSearch" class="dd-search" placeholder="cari toko…" autocomplete="off"></div>
          <div class="dd-list" id="ddStoreList" role="listbox"></div>
        </div>
      </div>
    </label>

    <label class="fld" style="margin-bottom:0"><span class="dd-lbl">Nama Kamu</span>
      <div class="dd empty" id="ddEmp">
        <button type="button" class="dd-btn" id="ddEmpBtn" disabled>
          <span class="dd-ico">🙋</span><span class="dd-val" id="ddEmpVal">pilih toko dulu…</span>
          <svg class="dd-chev" viewBox="0 0 12 8" width="12" height="8" fill="none"><path d="M1 1.5l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="dd-panel" id="ddEmpPanel" hidden>
          <div class="dd-searchwrap" style="display:none"><input id="ddEmpSearch" class="dd-search" placeholder="cari nama…" autocomplete="off"></div>
          <div class="dd-list" id="ddEmpList" role="listbox"></div>
        </div>
      </div>
    </label>

    <div id="idcard">
      <div class="ava" id="empAva">–</div>
      <div class="idc">
        <div class="idc-name" id="empNameTxt">—</div>
        <div class="idc-store" id="empMeta">—</div>
        <div class="idc-code"><span class="t">ID Karyawan</span><span class="code" id="empCode">—</span></div>
      </div>
    </div>
  </section>

  <section class="card" id="faceCard" style="display:none">
    <h2>Verifikasi wajah</h2>
    <div class="cam-wrap">
      <video id="cam" autoplay playsinline muted></video>
      <svg id="faceRing" class="face-guide" viewBox="0 0 400 533" preserveAspectRatio="none">
        <path fill-rule="evenodd" d="M0 0 H400 V533 H0 Z M200 105 C275 105 315 162 315 235 C315 308 272 385 200 415 C128 385 85 308 85 235 C85 162 125 105 200 105 Z" fill="rgba(5,8,13,0.6)"></path>
        <path class="face-outline" d="M200 105 C275 105 315 162 315 235 C315 308 272 385 200 415 C128 385 85 308 85 235 C85 162 125 105 200 105 Z" fill="none" stroke-width="3"></path>
      </svg>
      <div id="camState">menyalakan kamera…</div>
    </div>
    <div id="faceResult"></div>
    <button id="btnScan" type="button">📷 Scan Wajah</button>
  </section>

  <section class="card">
    <h2>Lokasi &amp; zona absen</h2>
    <div id="map"></div>
    <div id="zone"></div>
    <div id="loc">Mencari sinyal GPS…</div>
    <details class="tes">
      <summary>🧪 mode tes lokasi</summary>
      <div class="row" style="margin-top:8px">
        <button class="mini" id="btnCenter" type="button">🎯 tengah-kan</button>
        <button class="mini" id="btnSimIn" type="button">dalam zona</button>
        <button class="mini" id="btnSimOut" type="button">luar zona &gt;1km</button>
      </div>
    </details>
  </section>

  <section class="card">
    <div id="msg"></div>
    <div id="hist"></div>
  </section>

  <footer>Absen cuma bisa dari dalam zona (bulatan) di peta<span id="faceNote" style="display:none"> + verifikasi wajah</span>. Butuh izin lokasi &amp; kamera di browser.</footer>
</div>

<div class="actionbar">
  <button id="btnMasuk" class="cta" type="button" disabled>🌅 Absen Masuk</button>
  <button id="btnPulang" class="cta" type="button" disabled>🌙 Absen Pulang</button>
</div>

<div id="modal">
  <div class="modal" id="modalBox">
    <div class="micon" id="mIcon">✅</div>
    <div id="mTitle">—</div>
    <div id="mDesc" style="display:none">—</div>
    <div class="mrows" id="mRows" style="display:none"></div>
    <button id="mOk" type="button">OK</button>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
const $ = (id) => document.getElementById(id);
// API key diinject dari config server (FACEID_API_KEY) — kosong = mode dev terbuka
const API_KEY = @json((string) config('faceid.api_key'));
const API_HEADERS = API_KEY ? { "X-Api-Key": API_KEY } : {};
const state = { city: null, store: null, stores: [], employees: [], employee: null, coords: null, faceEnabled: false, face: null, camStarted: false, ip: null };

async function jget(url) {
  const r = await fetch(url, { headers: API_HEADERS });
  if (!r.ok) throw new Error((await r.text()).slice(0, 200));
  return r.json();
}

function vibrate(ms) { try { navigator.vibrate && navigator.vibrate(ms); } catch (e) {} }

function flash(kind, text) {
  const m = $("msg");
  m.className = kind === "ok" ? "ok" : "err";
  m.textContent = text;
  m.style.display = "block";
}

function popup(kind, title, desc, rows) {
  $("mIcon").textContent = kind === "success" ? "✅" : (kind === "error" ? "🚫" : "⏳");
  $("modalBox").className = "modal " + kind;
  $("mTitle").textContent = title;
  const d = $("mDesc");
  d.textContent = desc || "";
  d.style.display = desc ? "block" : "none";
  const mr = $("mRows");
  if (rows && rows.length) {
    mr.style.display = "block";
    mr.innerHTML = rows.map((r) => '<div class="mrow"><span class="k">' + r[0] + '</span><span class="v">' + r[1] + '</span></div>').join("");
  } else { mr.style.display = "none"; mr.innerHTML = ""; }
  $("modal").classList.add("show");
}
$("mOk").onclick = () => $("modal").classList.remove("show");

function setNet(on) {
  $("netDot").className = "sdot" + (on ? " ok" : " bad");
  $("netVal").textContent = on ? "ONLINE" : "OFFLINE";
}
window.addEventListener("online", () => setNet(true));
window.addEventListener("offline", () => setNet(false));

function updateBtns() {
  const ok = !!state.employee && (!state.faceEnabled || !!state.face);
  $("btnMasuk").disabled = !ok;
  $("btnPulang").disabled = !ok;
}

function setStepper() {
  const d1 = !!state.city, d2 = !!state.store, d3 = !!state.employee;
  $("st1").classList.toggle("done", d1); $("st1").classList.toggle("active", !d1);
  $("sl1").classList.toggle("done", d1);
  $("st2").classList.toggle("done", d2); $("st2").classList.toggle("active", d1 && !d2);
  $("sl2").classList.toggle("done", d2);
  $("st3").classList.toggle("done", d3); $("st3").classList.toggle("active", d2 && !d3);
}

function resetEmp() {
  state.employee = null;
  $("idcard").style.display = "none";
  $("hist").innerHTML = "";
  resetFace();
  $("faceCard").style.display = "none";
  updateBtns();
  setStepper();
}
</script>

<script>
// ---------- dropdown futuristis (glass panel + search + keyboard) ----------
const DDS = [];
function closeAllDD(except) { DDS.forEach((d) => { if (d !== except) d._close(); }); }

function makeDD(id, opts) {
  const root = $(id), btn = $(id + "Btn"), valEl = $(id + "Val"), panel = $(id + "Panel"), list = $(id + "List"), search = $(id + "Search");
  const sw = search.parentNode;
  let items = [], value = null, open = false, hl = -1, view = null, disabled = true;
  const api = {};

  function renderList() {
    const src = view ?? items;
    sw.style.display = items.length >= 8 ? "block" : "none";
    if (!src.length) {
      list.innerHTML = '<div class="dd-empty">' + (items.length ? "gak ketemu — coba kata lain" : "tidak ada data") + "</div>";
      return;
    }
    list.innerHTML = "";
    src.forEach((it, i) => {
      const el = document.createElement("div");
      el.className = "dd-item" + (it.v == value ? " sel" : "") + (i === hl ? " hl" : "");
      el.setAttribute("role", "option");
      el.style.animationDelay = (Math.min(i, 14) * 22) + "ms";
      el.innerHTML = '<div class="li"><div class="nm">' + it.label + '</div>' + (it.sub ? '<div class="sb">' + it.sub + '</div>' : "") + '</div>' + (it.v == value ? '<span class="tick">✓</span>' : "");
      el.onclick = () => pick(it);
      list.appendChild(el);
    });
  }

  function onKey(e) {
    const src = view ?? items;
    if (e.key === "Escape") { _close(); return; }
    if (e.key === "ArrowDown" || e.key === "ArrowUp") {
      e.preventDefault();
      if (!src.length) return;
      hl = e.key === "ArrowDown" ? (hl + 1) % src.length : (hl - 1 + src.length) % src.length;
      renderList();
      if (list.children[hl]) list.children[hl].scrollIntoView({ block: "nearest" });
    } else if (e.key === "Enter") {
      e.preventDefault();
      if (src[hl]) pick(src[hl]);
    }
  }

  function openPanel() {
    if (disabled || open) return;
    closeAllDD(api);
    open = true; hl = -1; view = null;
    search.value = "";
    panel.hidden = false;
    root.classList.add("open");
    requestAnimationFrame(() => panel.classList.add("in"));
    renderList();
    if (items.length >= 8) setTimeout(() => search.focus(), 60);
    document.addEventListener("keydown", onKey);
  }

  function _close() {
    if (!open) return;
    open = false;
    root.classList.remove("open");
    panel.classList.remove("in");
    setTimeout(() => { panel.hidden = true; }, 160);
    document.removeEventListener("keydown", onKey);
  }

  function pick(it) {
    value = it.v;
    root.classList.remove("empty");
    valEl.textContent = it.label;
    _close();
    renderList();
    if (opts.onChange) opts.onChange(it.v, it);
  }

  btn.addEventListener("click", () => { open ? _close() : openPanel(); });
  search.addEventListener("input", () => {
    const q = search.value.trim().toLowerCase();
    view = q ? items.filter((it) => (it.label + " " + (it.sub || "")).toLowerCase().includes(q)) : null;
    hl = -1;
    renderList();
  });
  document.addEventListener("click", (e) => { if (open && !root.contains(e.target)) _close(); });

  Object.assign(api, {
    get value() { return value; },
    get item() { return items.find((x) => x.v == value) || null; },
    setItems(arr) {
      items = arr; view = null; value = null; hl = -1;
      root.classList.add("empty");
      valEl.textContent = opts.emptyText || "pilih…";
      if (open) _close();
      renderList();
    },
    setDisabled(b) { disabled = b; btn.disabled = b; if (b) _close(); },
    setBusy(b) { btn.disabled = b || disabled; if (b) { root.classList.add("empty"); valEl.textContent = "memuat…"; } },
    setValueLabel(t) { valEl.textContent = t; },
    _close,
  });
  DDS.push(api);
  return api;
}
</script>

<script>
// ---------- dropdown kota → toko → karyawan ----------
const ddCity = makeDD("ddCity", { emptyText: "pilih kota…", onChange: (v) => {
  state.city = v;
  state.store = null;
  ddStore.setItems([]); ddStore.setBusy(true); ddStore.setDisabled(true);
  ddEmp.setItems([]); ddEmp.setDisabled(true);
  ddEmp.setValueLabel("pilih toko dulu…");
  resetEmp(); clearZone();
  loadStores(v);
  setStepper();
}});

const ddStore = makeDD("ddStore", { emptyText: "pilih toko…", onChange: (v) => {
  state.store = state.stores.find((s) => s.id == v) || null;
  showZone(state.store);
  ddEmp.setItems([]); ddEmp.setBusy(true); ddEmp.setDisabled(true);
  ddEmp.setValueLabel("memuat…");
  resetEmp();
  loadEmployees(v);
  setStepper();
}});

const ddEmp = makeDD("ddEmp", { emptyText: "pilih nama kamu…", onChange: (v) => {
  state.employee = state.employees.find((e) => e.id == v) || null;
  if (!state.employee) { resetEmp(); return; }
  resetFace();
  $("empAva").textContent = initials(state.employee.name);
  $("empNameTxt").textContent = state.employee.name;
  $("empCode").textContent = state.employee.employee_code || "—";
  $("empMeta").textContent = state.store ? state.store.name : "—";
  $("idcard").style.display = "flex";
  $("msg").style.display = "none";
  updateFaceCard();
  updateBtns();
  setStepper();
  loadHistory();
}});

function initials(name) {
  return (name || "?").trim().split(/\s+/).map((w) => w[0]).slice(0, 2).join("").toUpperCase();
}

async function loadCities() {
  ddCity.setBusy(true);
  try {
    const rows = await jget("/api/cities");
    ddCity.setItems(rows.map((c) => ({ v: c.id, label: c.name, sub: c.stores_count + " toko" })));
    ddCity.setDisabled(false);
  } catch (e) {
    ddCity.setItems([]);
    ddCity.setValueLabel("gagal memuat — refresh halaman");
  }
  ddCity.setBusy(false);
}

async function loadStores(cityId) {
  try {
    const rows = await jget("/api/stores?city_id=" + cityId);
    state.stores = rows;
    ddStore.setItems(rows.map((s) => ({ v: s.id, label: s.name, sub: s.employees_count + " spg · radius " + s.radius_m + " m" })));
    ddStore.setDisabled(false);
  } catch (e) {
    ddStore.setItems([]);
    ddStore.setValueLabel("gagal memuat");
  }
  ddStore.setBusy(false);
}

async function loadEmployees(storeId) {
  try {
    const rows = await jget("/api/employees?store_id=" + storeId);
    state.employees = rows;
    ddEmp.setItems(rows.map((e) => ({ v: e.id, label: e.name, sub: e.employee_code || "tanpa kode" })));
    ddEmp.setDisabled(false);
  } catch (e) {
    ddEmp.setItems([]);
    ddEmp.setValueLabel("gagal memuat");
  }
  ddEmp.setBusy(false);
}
</script>

<script>
// ---------- peta + zona absen ----------
const geo = { map: null, circle: null, storePin: null, userPin: null, watchId: null, ready: false };

function zoneRadius() {
  return (state.store && state.store.radius_m) ? state.store.radius_m : 150;
}

function distanceMeters(a, b) {
  const R = 6371000, P = Math.PI / 180;
  const dp = (b.lat - a.lat) * P, dl = (b.lon - a.lon) * P;
  const h = Math.sin(dp / 2) ** 2 + Math.cos(a.lat * P) * Math.cos(b.lat * P) * Math.sin(dl / 2) ** 2;
  return 2 * R * Math.asin(Math.min(1, Math.sqrt(h)));
}

function initMap() {
  if (typeof L === "undefined") {
    $("map").innerHTML = '<div id="mapFallback">peta gak termuat — butuh internet</div>';
    return;
  }
  $("map").innerHTML = "";
  geo.map = L.map("map", { zoomControl: false });
  L.tileLayer("https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png", {
    maxZoom: 19,
    attribution: "&copy; OpenStreetMap &copy; CARTO",
  }).addTo(geo.map);
  geo.map.setView([-2.5, 118], 4); // Indonesia
  geo.ready = true;
}

function storeIcon() {
  return L.divIcon({ className: "store-pin", html: '<div class="store-pin-dot"></div>', iconSize: [20, 20], iconAnchor: [10, 10] });
}

function userIcon() {
  return L.divIcon({ className: "user-pin", html: '<div class="user-dot"></div>', iconSize: [20, 20], iconAnchor: [10, 10] });
}

function showZone(store) {
  if (!geo.ready) return;
  if (!store) { clearZone(); return; }
  if (geo.circle) geo.circle.remove();
  if (geo.storePin) geo.storePin.remove();
  geo.circle = L.circle([store.lat, store.lon], {
    radius: zoneRadius(),
    color: "#7dd3fc",
    weight: 3,
    opacity: 0.95,
    fillColor: "#38bdf8",
    fillOpacity: 0.13,
  }).addTo(geo.map);
  geo.storePin = L.marker([store.lat, store.lon], { icon: storeIcon() }).addTo(geo.map);
  geo.map.fitBounds(geo.circle.getBounds().pad(0.35));
  refreshZone();
}

function clearZone() {
  if (!geo.ready) return;
  if (geo.circle) { geo.circle.remove(); geo.circle = null; }
  if (geo.storePin) { geo.storePin.remove(); geo.storePin = null; }
  $("zone").className = "";
  $("zone").textContent = "";
}

function gpsChip(acc) {
  const dot = $("gpsDot");
  if (acc == null) { dot.className = "sdot live"; return; }
  dot.className = "sdot " + (acc <= 20 ? "ok" : (acc <= 60 ? "mid" : "bad"));
}

function setUser(lat, lon, acc) {
  state.coords = { lat: lat, lon: lon, acc: acc };
  if (geo.ready) {
    if (!geo.userPin) geo.userPin = L.marker([lat, lon], { icon: userIcon() }).addTo(geo.map);
    else geo.userPin.setLatLng([lat, lon]);
  }
  refreshZone();
}

function refreshZone() {
  if (!state.coords) return;
  gpsChip(state.coords.acc);
  $("gpsVal").textContent = "±" + Math.round(state.coords.acc || 0) + " m";
  if (state.store) {
    const d = distanceMeters(state.coords, state.store);
    const inside = d <= zoneRadius();
    $("zone").className = inside ? "zone-in" : "zone-out";
    $("zone").textContent = inside
      ? "✅ Kamu di dalam zona — absen bisa diproses"
      : "🚫 Kamu di luar zona absen — deketin tokonya dulu";
  } else {
    $("zone").className = "";
    $("zone").textContent = "";
  }
  $("loc").textContent = "GPS aktif · IP " + (state.ip || "…");
}

function startGps() {
  if (!navigator.geolocation) { $("gpsVal").textContent = "GPS N/A"; $("loc").textContent = "GPS gak tersedia di browser ini."; return; }
  if (geo.watchId !== null) return;
  geo.watchId = navigator.geolocation.watchPosition(
    (p) => setUser(p.coords.latitude, p.coords.longitude, p.coords.accuracy),
    (err) => {
      $("gpsVal").textContent = "GPS GAGAL";
      $("gpsDot").className = "sdot bad";
      $("loc").textContent = "GPS gagal (" + err.message + ") — cek izin lokasi browser, atau pakai tombol tes.";
      geo.watchId = null;
    },
    { enableHighAccuracy: true, maximumAge: 2000, timeout: 15000 }
  );
}

function sim(delta) {
  if (!state.store) { flash("err", "Pilih toko dulu — titiknya ikut toko."); return; }
  setUser(
    state.store.lat + delta * (Math.random() - 0.5),
    state.store.lon + delta * (Math.random() - 0.5),
    5
  );
}

function simFar() {
  if (!state.store) { flash("err", "Pilih toko dulu — titiknya ikut toko."); return; }
  setUser(state.store.lat + 0.015, state.store.lon + 0.012, 10);
}

$("btnCenter").onclick = () => {
  if (!geo.ready || !state.coords) { flash("err", "Lokasi belum kebaca."); return; }
  geo.map.setView([state.coords.lat, state.coords.lon], Math.max(geo.map.getZoom(), 16));
};
$("btnSimIn").onclick = () => sim(0.0001);   // ±5 m dari titik → dalam zona
$("btnSimOut").onclick = simFar;             // jauh ±1.6-1.9 km dari titik → pasti luar zona
</script>

<script>
// ---------- verifikasi wajah (face ID) ----------
function updateFaceCard() {
  const show = state.faceEnabled && !!state.employee;
  $("faceCard").style.display = show ? "block" : "none";
  if (show) ensureCam();
}

function resetFace() {
  state.face = null;
  $("faceResult").style.display = "none";
  $("faceRing").classList.remove("ok");
  updateBtns();
}

function ensureCam() {
  if (state.camStarted) return;
  state.camStarted = true;
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    $("camState").textContent = "kamera gak didukung browser ini";
    $("btnScan").disabled = true;
    return;
  }
  navigator.mediaDevices.getUserMedia({
    video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } },
    audio: false,
  }).then((stream) => {
    $("cam").srcObject = stream;
    $("camState").textContent = "posisikan wajah kamu di dalam lingkaran";
  }).catch((err) => {
    state.camStarted = false;
    $("camState").textContent = "kamera gagal (" + err.name + ") — izinkan akses kamera di browser";
    $("btnScan").disabled = true;
  });
}

async function scanFace() {
  const v = $("cam");
  if (!v.videoWidth) { popup("error", "Kamera belum siap", "Tunggu sebentar atau cek izin kamera di browser."); return; }
  $("btnScan").disabled = true;
  $("btnScan").textContent = "Memindai…";
  try {
    // crop potret 3:4 dari tengah video (sesuai frame guide) → jpeg
    const vw = v.videoWidth, vh = v.videoHeight;
    let cw = vw, ch = Math.round(vw * 4 / 3);
    if (ch > vh) { ch = vh; cw = Math.round(vh * 3 / 4); }
    const c = document.createElement("canvas");
    c.width = 480; c.height = 640;
    const ctx = c.getContext("2d");
    ctx.translate(480, 0); ctx.scale(-1, 1); // mirror, sama dengan preview
    ctx.drawImage(v, (vw - cw) / 2, (vh - ch) / 2, cw, ch, 0, 0, 480, 640);
    const blob = await new Promise((res) => c.toBlob(res, "image/jpeg", 0.9));

    const r = await fetch("/api/face/verify", {
      method: "POST",
      headers: { "Content-Type": "application/octet-stream", ...API_HEADERS },
      body: blob,
    });
    const out = await r.json();
    if (!r.ok || !out.ok) {
      popup("error", "Wajah gak dikenali", "Cahaya kurang atau posisi gak pas. Deketin muka ke lingkaran, buka mata, terus scan ulang.");
      return;
    }
    state.face = out;
    $("faceRing").classList.add("ok");
    $("faceResult").className = "ok";
    $("faceResult").style.display = "block";
    $("faceResult").textContent = "✅ Wajah cocok: " + (out.name || "-") + (out.cosine ? " (skor " + Number(out.cosine).toFixed(2) + ")" : "");
    popup("success", "Wajah terverifikasi!", "Halo " + (out.name || "") + "! Sekarang kamu bisa absen.");
    vibrate(30);
    updateBtns();
  } catch (e) {
    popup("error", "Scan gagal", e.message);
  } finally {
    $("btnScan").disabled = false;
    $("btnScan").textContent = "📷 Scan Wajah";
  }
}
$("btnScan").onclick = scanFace;
</script>

<script>
// ---------- absen ----------
async function absen(type) {
  if (!state.employee) return;
  if (state.faceEnabled && !state.face) { popup("error", "Scan wajah dulu", "Verifikasi wajah wajib sebelum absen — posisikan muka di lingkaran lalu tekan Scan Wajah."); return; }
  if (!state.coords) { popup("error", "Lokasi belum kebaca", "Izinkan GPS di browser kamu, atau pakai tombol tes."); return; }
  $("btnMasuk").disabled = $("btnPulang").disabled = true;
  try {
    const r = await fetch("/api/attendances", {
      method: "POST",
      headers: { "Content-Type": "application/json", ...API_HEADERS },
      body: JSON.stringify({
        employee_id: state.employee.id,
        type: type,
        lat: state.coords.lat,
        lon: state.coords.lon,
        acc: state.coords.acc,
        face_key: state.face ? state.face.face_key : null,
        device: navigator.userAgent,
      }),
    });
    const out = await r.json();
    if (!r.ok) {
      if (out.distance_m !== undefined) {
        popup("error", "Gagal — kamu di luar zona", "Posisimu gak ada di dalam bulatan toko. Deketin titik tokonya, pastikan titik birumu masuk lingkaran, terus coba absen lagi.");
      } else {
        popup("error", "Absen gagal", out.message || "Coba lagi sebentar.");
      }
      return;
    }
    if (!out.logged) {
      popup("info", "Barusan aja absen", "Absen " + type + " kamu udah kecatat beberapa detik lalu — gak perlu dobel.");
      return;
    }
    // struk absen (receipt) — data dari record yang baru disimpan server
    const rec = out.record || {};
    popup("success",
      type === "masuk" ? "Absen Masuk Berhasil!" : "Absen Pulang Berhasil!",
      null,
      [
        ["Nama", (rec.employee && rec.employee.name) || state.employee.name],
        ["Waktu", rec.created_at ? new Date(rec.created_at).toLocaleTimeString("id-ID") : new Date().toLocaleTimeString("id-ID")],
        ["Toko", (rec.store && rec.store.name) || (state.store ? state.store.name : "—")],
        ["Jarak", rec.distance_m != null ? "±" + Math.round(rec.distance_m) + " m" : "—"],
        ["IP", state.ip || "—"],
        ["GPS", state.coords.acc != null ? "±" + Math.round(state.coords.acc) + " m" : "—"],
      ]);
    vibrate([40, 60, 40]);
    loadHistory();
  } catch (e) {
    popup("error", "Absen gagal", e.message);
  } finally {
    updateBtns();
  }
}

$("btnMasuk").onclick = () => absen("masuk");
$("btnPulang").onclick = () => absen("pulang");

// ---------- riwayat hari ini ----------
async function loadHistory() {
  if (!state.employee) { $("hist").innerHTML = ""; return; }
  const d = new Date();
  const today = d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0");
  try {
    const rows = await jget("/api/attendances?employee_id=" + state.employee.id + "&date=" + today + "&limit=10");
    $("hist").innerHTML = rows.length
      ? '<div class="h">Riwayat hari ini</div>' + rows.map((r) =>
          '<div class="r"><span class="badge ' + (r.type === "masuk" ? "b-masuk" : "b-pulang") + '">' +
          (r.type === "masuk" ? "MASUK" : "PULANG") + '</span><b>' +
          new Date(r.created_at).toLocaleTimeString("id-ID") + '</b>' +
          (r.ip ? '<span class="rip">' + r.ip + '</span>' : "") + '</div>'
        ).join("")
      : "";
  } catch (e) { /* diemin aja */ }
}

// ---------- init ----------
(async () => {
  setNet(navigator.onLine);

  try {
    const h = await jget("/api/healthz");
    state.faceEnabled = !!h.face_id;
    $("faceState").textContent = state.faceEnabled ? "AKTIF" : "OFF";
    if (state.faceEnabled) $("faceNote").style.display = "inline";
  } catch (e) { $("faceState").textContent = "?"; }

  initMap();
  setStepper();

  // GPS cuma jalan di secure context (https / localhost).
  if (!window.isSecureContext) {
    $("gpsVal").textContent = "NO HTTPS";
    $("gpsDot").className = "sdot bad";
    $("loc").textContent = "⚠️ GPS diblokir browser — halaman gak secure. Buka halaman absen via HTTPS.";
  } else {
    startGps();
  }

  // chip IP realtime (IP yang terlihat server, bukan yang di-spoof klien)
  try {
    const j = await jget("/api/my-ip");
    state.ip = j.ip || null;
    $("ipVal").textContent = state.ip || "—";
    $("ipDot").className = "sdot ok";
  } catch (e) {
    $("ipVal").textContent = "IP: ?";
  }

  await loadCities();
})();
</script>
</body>
</html>

