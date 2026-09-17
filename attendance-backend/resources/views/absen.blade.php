<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#070b10">
<title>Presensi SPG</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<style>
:root { --bg:#070b10; --card:#101722; --card2:#0c121b; --line:#1c2735; --txt:#eaf1f8; --dim:#8296ab; --acc:#34d399; --acc-dk:#052e16; --warn:#f87171; --mid:#fbbf24; --sky:#38bdf8; --r:16px; }
* { box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
body { margin:0; padding:14px 14px 108px; background:linear-gradient(180deg,#0c1522,#070b10 340px) no-repeat, var(--bg); color:var(--txt); font:15px/1.5 system-ui,"Segoe UI",Roboto,sans-serif; }
.wrap { max-width:440px; margin:0 auto; }
header.top { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:4px 0 16px; }
.brand { display:flex; align-items:center; gap:10px; min-width:0; }
.logo { width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg,#0ea56b,#34d399); display:flex; align-items:center; justify-content:center; box-shadow:0 6px 18px rgba(52,211,153,.25); flex:none; }
.logo svg { width:22px; height:22px; }
h1 { font-size:17px; margin:0; letter-spacing:.01em; }
.sub { margin:0; font-size:11px; color:var(--dim); }
.chip { font-size:11px; color:var(--dim); border:1px solid var(--line); border-radius:999px; padding:6px 11px; background:rgba(16,23,34,.6); white-space:nowrap; }
.chip b { color:var(--acc); font-weight:600; }
section.card { background:linear-gradient(180deg, rgba(255,255,255,.02), transparent 40%), var(--card); border:1px solid var(--line); border-radius:var(--r); padding:16px; margin-bottom:12px; box-shadow:0 10px 30px rgba(0,0,0,.25); }
h2 { font-size:11px; margin:0 0 12px; color:var(--dim); font-weight:700; text-transform:uppercase; letter-spacing:.12em; }
/* ---------- stepper ---------- */
.steps { display:flex; align-items:center; gap:7px; margin-bottom:14px; }
.step { display:flex; align-items:center; gap:6px; }
.snum { width:24px; height:24px; border-radius:50%; border:1.5px solid var(--line); background:var(--card2); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:var(--dim); transition:all .2s; flex:none; }
.slbl { font-size:11px; font-weight:600; color:var(--dim); }
.step.active .snum { border-color:var(--sky); color:var(--sky); box-shadow:0 0 0 3px rgba(56,189,248,.15); }
.step.active .slbl { color:var(--sky); }
.step.done .snum { background:var(--acc); border-color:var(--acc); color:var(--acc-dk); font-size:0; }
.step.done .snum::after { content:"✓"; font-size:12px; }
.step.done .slbl { color:var(--txt); }
.sline { height:2px; flex:1; background:var(--line); border-radius:2px; transition:background .2s; }
.sline.done { background:var(--acc); }
/* ---------- fields ---------- */
.fld { display:block; margin-bottom:10px; }
.flbl { display:block; font-size:10px; font-weight:700; color:var(--dim); margin-bottom:5px; letter-spacing:.1em; }
select { width:100%; padding:12px 13px; border-radius:12px; border:1px solid var(--line); background:var(--card2); color:var(--txt); font-size:14px; appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%238296ab' stroke-width='1.6' fill='none' stroke-linecap='round'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 13px center; }
select:focus { outline:none; border-color:var(--sky); box-shadow:0 0 0 3px rgba(56,189,248,.15); }
select:disabled { opacity:.45; }
select option { background:var(--card); }
/* ---------- ID card ---------- */
#idcard { display:none; align-items:center; gap:12px; margin-top:14px; padding:12px 14px; background:linear-gradient(135deg, rgba(52,211,153,.14), rgba(52,211,153,.03)); border:1px solid rgba(52,211,153,.35); border-radius:14px; animation:slidein .25s ease; }
@keyframes slidein { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
.ava { width:44px; height:44px; border-radius:12px; background:var(--acc); color:var(--acc-dk); font-weight:800; font-size:15px; display:flex; align-items:center; justify-content:center; flex:none; letter-spacing:.02em; }
.idc { flex:1; min-width:0; }
.idc-name { font-size:15px; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.idc-store { font-size:11px; color:var(--dim); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.idc-code { display:flex; align-items:baseline; gap:8px; margin-top:3px; }
.idc-code .t { font-size:9px; color:var(--dim); text-transform:uppercase; letter-spacing:.12em; }
.idc-code .code { font-size:17px; font-weight:800; color:var(--acc); letter-spacing:.06em; }

/* ---------- kamera & wajah ---------- */
.cam-wrap { position:relative; width:100%; max-width:320px; margin:0 auto; aspect-ratio:3/4; border-radius:var(--r); overflow:hidden; background:var(--card2); border:1px solid var(--line); }
#cam { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transform:scaleX(-1); }
.face-guide { position:absolute; inset:0; width:100%; height:100%; pointer-events:none; }
.face-guide .face-outline { stroke:#7dd3fc; transition:stroke .2s; }
.face-guide.ok .face-outline { stroke:var(--acc); filter:drop-shadow(0 0 6px rgba(52,211,153,.85)); }
#camState { position:absolute; bottom:8px; left:0; right:0; text-align:center; color:#cbd5e1; font-size:11px; text-shadow:0 1px 3px #000; }
#faceResult { display:none; margin-top:10px; padding:9px 12px; border-radius:10px; font-size:12px; font-weight:600; }
#faceResult.ok { display:block; background:var(--acc-dk); color:#86efac; }
#btnScan { width:100%; margin-top:10px; padding:13px; border:0; border-radius:12px; font-size:14px; font-weight:700; cursor:pointer; background:var(--sky); color:#082f49; transition:transform .1s, opacity .2s; }
#btnScan:active { transform:scale(.98); }
#btnScan:disabled { opacity:.5; cursor:not-allowed; }
/* ---------- peta & zona ---------- */
#map { height:240px; border-radius:12px; overflow:hidden; background:var(--card2); border:1px solid var(--line); }
#mapFallback { display:flex; align-items:center; justify-content:center; height:100%; color:var(--dim); font-size:12px; }
#zone { display:none; margin-top:8px; font-size:12px; font-weight:600; padding:9px 12px; border-radius:10px; }
#zone.zone-in { display:block; background:var(--acc-dk); color:#86efac; }
#zone.zone-out { display:block; background:#2f0d0d; color:#fca5a5; }
#loc { color:var(--dim); font-size:11px; margin-top:7px; }
details.tes { margin-top:10px; }
details.tes summary { color:var(--dim); font-size:11px; cursor:pointer; }
.row { display:flex; gap:8px; margin-top:10px; }
.mini { flex:1; font-size:11px; font-weight:600; padding:9px; background:var(--card2); color:var(--dim); border:1px solid var(--line); border-radius:9px; cursor:pointer; }
.mini:hover { color:var(--txt); }
/* ---------- msg & riwayat ---------- */
#msg { display:none; margin-top:10px; padding:10px 12px; border-radius:10px; font-size:12px; line-height:1.45; }
.ok { background:var(--acc-dk); color:#86efac; }
.err { background:#2f0d0d; color:#fca5a5; }
#hist { margin-top:10px; font-size:12px; }
#hist .h { color:var(--dim); font-size:10px; text-transform:uppercase; letter-spacing:.12em; margin-bottom:2px; }
#hist div.r { padding:6px 0; border-bottom:1px solid var(--line); color:var(--dim); display:flex; justify-content:space-between; }
#hist b { color:var(--txt); }
/* ---------- action bar (sticky bawah) ---------- */
.actionbar { position:fixed; left:0; right:0; bottom:0; z-index:1500; display:flex; gap:10px; padding:12px 14px calc(12px + env(safe-area-inset-bottom)); background:rgba(7,11,16,.92); backdrop-filter:blur(8px); border-top:1px solid var(--line); }
.cta { flex:1; padding:14px; border:0; border-radius:14px; font-size:15px; font-weight:800; cursor:pointer; transition:transform .1s, opacity .2s; }
.cta:active { transform:scale(.97); }
.cta:disabled { opacity:.3; cursor:not-allowed; }
#btnMasuk { background:linear-gradient(135deg,#10b981,#34d399); color:#022c22; }
#btnPulang { background:linear-gradient(135deg,#f59e0b,#fbbf24); color:#422006; }
/* ---------- footer & modal ---------- */
footer { color:var(--dim); font-size:10px; text-align:center; margin:4px 0 10px; line-height:1.6; }
#modal { position:fixed; inset:0; background:rgba(4,8,12,.78); display:none; align-items:center; justify-content:center; z-index:2000; padding:20px; }
#modal.show { display:flex; }
.modal { background:var(--card); border:1px solid var(--line); border-radius:20px; padding:28px 20px; max-width:330px; width:100%; text-align:center; animation:popin .18s ease; }
.modal.success { border-color:var(--acc); }
.modal.error { border-color:var(--warn); }
.modal.info { border-color:var(--mid); }
@keyframes popin { from { transform:scale(.85); opacity:0; } to { transform:scale(1); opacity:1; } }
.micon { width:64px; height:64px; margin:0 auto; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:30px; background:var(--card2); border:1px solid var(--line); }
.modal.success .micon { background:var(--acc-dk); border-color:rgba(52,211,153,.4); }
.modal.error .micon { background:#2f0d0d; border-color:rgba(248,113,113,.4); }
.modal.info .micon { background:#3a2a05; border-color:rgba(251,191,36,.4); }
#mTitle { font-size:17px; font-weight:800; margin-top:14px; }
#mDesc { color:var(--dim); font-size:13px; margin-top:6px; line-height:1.55; }
#mOk { margin-top:20px; width:100%; padding:13px; background:var(--acc); color:var(--acc-dk); border:0; border-radius:12px; font-weight:800; cursor:pointer; }
/* ---------- pins peta ---------- */
.store-pin, .user-pin { background:transparent; border:0; }
.store-pin-dot { width:13px; height:13px; border-radius:50%; background:var(--acc); border:3px solid #064e3b; box-shadow:0 0 8px rgba(52,211,153,.8); }
.user-dot { width:13px; height:13px; border-radius:50%; background:var(--sky); border:3px solid #e0f2fe; animation:pulse 1.6s infinite; }
@keyframes pulse { 0% { box-shadow:0 0 0 0 rgba(56,189,248,.55); } 70% { box-shadow:0 0 0 12px rgba(56,189,248,0); } 100% { box-shadow:0 0 0 0 rgba(56,189,248,0); } }
.leaflet-container { background:var(--card2); }
.leaflet-control-attribution { font-size:8px; background:rgba(7,11,16,.72); color:var(--dim); }
.leaflet-control-attribution a { color:var(--dim); }

</style>
</head>
<body>
<div class="wrap">
  <header class="top">
    <div class="brand">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9.2" stroke="#03291d" stroke-width="2.2"/><path d="M8 12.5l3 3 5.5-6" stroke="#03291d" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
      <div>
        <h1>Presensi SPG</h1>
        <p class="sub">absen masuk &amp; pulang</p>
      </div>
    </div>
    <span class="chip">Face ID <b id="faceState">…</b></span>
  </header>

  <section class="card">
    <div class="steps">
      <div class="step active" id="st1"><span class="snum">1</span><span class="slbl">Kota</span></div>
      <div class="sline" id="sl1"></div>
      <div class="step" id="st2"><span class="snum">2</span><span class="slbl">Toko</span></div>
      <div class="sline" id="sl2"></div>
      <div class="step" id="st3"><span class="snum">3</span><span class="slbl">Nama</span></div>
    </div>
    <label class="fld"><span class="flbl">🏙️ KOTA</span>
      <select id="city"><option value="">Pilih kota…</option></select>
    </label>
    <label class="fld"><span class="flbl">🏬 TOKO</span>
      <select id="store" disabled><option value="">Pilih toko…</option></select>
    </label>
    <label class="fld" style="margin-bottom:0"><span class="flbl">🙋 NAMA KAMU</span>
      <select id="employee" disabled><option value="">Pilih nama kamu…</option></select>
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
        <path fill-rule="evenodd" d="M0 0 H400 V533 H0 Z M200 105 C275 105 315 162 315 235 C315 308 272 385 200 415 C128 385 85 308 85 235 C85 162 125 105 200 105 Z" fill="rgba(7,11,16,0.6)"></path>
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
    <div id="mDesc">—</div>
    <button id="mOk" type="button">OK</button>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
const $ = (id) => document.getElementById(id);
// API key diinject dari config server (FACEID_API_KEY) — kosong = mode dev terbuka
const API_KEY = @json((string) config('faceid.api_key'));
const API_HEADERS = API_KEY ? { "X-Api-Key": API_KEY } : {};
const state = { city: null, store: null, stores: [], employees: [], employee: null, coords: null, faceEnabled: false, face: null, camStarted: false };

async function jget(url) {
  const r = await fetch(url, { headers: API_HEADERS });
  if (!r.ok) throw new Error((await r.text()).slice(0, 200));
  return r.json();
}

function flash(kind, text) {
  const m = $("msg");
  m.className = kind === "ok" ? "ok" : "err";
  m.textContent = text;
  m.style.display = "block";
}

function popup(kind, title, desc) {
  $("mIcon").textContent = kind === "success" ? "✅" : (kind === "error" ? "🚫" : "⏳");
  $("modalBox").className = "modal " + kind;
  $("mTitle").textContent = title;
  $("mDesc").textContent = desc || "";
  $("modal").classList.add("show");
}
$("mOk").onclick = () => $("modal").classList.remove("show");

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

// ---------- dropdown berantai: kota → toko → karyawan ----------
async function loadCities() {
  const rows = await jget("/api/cities");
  rows.forEach((c) => $("city").add(new Option(c.name + " (" + c.stores_count + " toko)", c.id)));
}

$("city").onchange = async () => {
  state.city = $("city").value || null;
  $("store").innerHTML = '<option value="">Pilih toko…</option>';
  $("employee").innerHTML = '<option value="">Pilih nama kamu…</option>';
  $("employee").disabled = true;
  state.store = null; state.stores = [];
  resetEmp();
  clearZone();
  if (!state.city) { $("store").disabled = true; return; }
  state.stores = await jget("/api/stores?city_id=" + state.city);
  state.stores.forEach((s) => $("store").add(new Option(s.name + " (" + s.employees_count + " spg)", s.id)));
  $("store").disabled = false;
  setStepper();
};

$("store").onchange = async () => {
  const id = $("store").value;
  state.store = state.stores.find((s) => s.id == id) || null;
  showZone(state.store);
  $("employee").innerHTML = '<option value="">Pilih nama kamu…</option>';
  state.employee = null; state.employees = [];
  resetEmp();
  if (!id) { $("employee").disabled = true; return; }
  state.employees = await jget("/api/employees?store_id=" + id);
  state.employees.forEach((e) => $("employee").add(new Option(e.name, e.id)));
  $("employee").disabled = false;
  setStepper();
};

function initials(name) {
  return (name || "?").trim().split(/\s+/).map((w) => w[0]).slice(0, 2).join("").toUpperCase();
}

$("employee").onchange = () => {
  const id = $("employee").value;
  state.employee = state.employees.find((e) => e.id == id) || null;
  if (!state.employee) { resetEmp(); return; }
  resetFace();
  $("empAva").textContent = initials(state.employee.name);
  $("empNameTxt").textContent = state.employee.name;
  $("empCode").textContent = state.employee.employee_code || "—";
  $("empMeta").textContent = (state.store ? state.store.name : "—");
  $("idcard").style.display = "flex";
  $("msg").style.display = "none";
  updateFaceCard();
  updateBtns();
  setStepper();
  loadHistory();
};


// ---------- peta + zona absen (gaya zone) ----------
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
  $("loc").textContent = "GPS aktif" + (state.coords.acc ? " (akurasi ±" + Math.round(state.coords.acc) + " m)" : "");
}

function startGps() {
  if (!navigator.geolocation) { $("loc").textContent = "GPS gak tersedia di browser ini."; return; }
  if (geo.watchId !== null) return;
  geo.watchId = navigator.geolocation.watchPosition(
    (p) => setUser(p.coords.latitude, p.coords.longitude, p.coords.accuracy),
    (err) => {
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
    updateBtns();
  } catch (e) {
    popup("error", "Scan gagal", e.message);
  } finally {
    $("btnScan").disabled = false;
    $("btnScan").textContent = "📷 Scan Wajah";
  }
}
$("btnScan").onclick = scanFace;


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
    popup("success",
      type === "masuk" ? "Absen Masuk Berhasil!" : "Absen Pulang Berhasil!",
      state.employee.name + " · " + new Date().toLocaleTimeString("id-ID"));
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
          '<div class="r">' + (r.type === "masuk" ? "🌅 Masuk" : "🌙 Pulang") +
          " <b>" + new Date(r.created_at).toLocaleTimeString("id-ID") + "</b></div>"
        ).join("")
      : "";
  } catch (e) { /* diemin aja */ }
}

// ---------- init ----------
(async () => {
  try {
    const h = await jget("/api/healthz");
    state.faceEnabled = !!h.face_id;
    $("faceState").textContent = state.faceEnabled ? "AKTIF" : "nonaktif (config)";
    if (state.faceEnabled) $("faceNote").style.display = "inline";
  } catch (e) { /* server down */ }

  initMap();
  setStepper();

  // GPS cuma jalan di secure context (https / localhost).
  if (!window.isSecureContext) {
    $("loc").textContent = "⚠️ GPS diblokir browser — halaman gak secure. Buka halaman absen via HTTPS (lokal: https://<ip-laptop>:8444/)";
  } else {
    startGps();
  }
  await loadCities();
})();
</script>
</body>
</html>
