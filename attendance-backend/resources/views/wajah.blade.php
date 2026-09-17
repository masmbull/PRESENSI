<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#070b10">
<title>Kelola Wajah &amp; Lokasi — Admin</title>
<style>
:root { --bg:#070b10; --card:#101722; --card2:#0c121b; --line:#1c2735; --txt:#eaf1f8; --dim:#8296ab; --acc:#34d399; --acc-dk:#052e16; --warn:#f87171; --sky:#38bdf8; --r:16px; }
* { box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
body { margin:0; padding:14px; background:linear-gradient(180deg,#0c1522,#070b10 340px) no-repeat, var(--bg); color:var(--txt); font:15px/1.5 system-ui,"Segoe UI",Roboto,sans-serif; }
.wrap { max-width:560px; margin:0 auto; }
header.top { text-align:center; margin:6px 0 18px; }
.logo { width:46px; height:46px; margin:0 auto 10px; border-radius:14px; background:linear-gradient(135deg,#0ea56b,#34d399); display:flex; align-items:center; justify-content:center; box-shadow:0 6px 18px rgba(52,211,153,.25); }
.logo svg { width:26px; height:26px; }
h1 { font-size:19px; margin:0; }
.sub { margin:2px 0 0; font-size:12px; color:var(--dim); }
.stats { display:flex; gap:8px; justify-content:center; flex-wrap:wrap; margin-top:14px; }
.stat { background:var(--card); border:1px solid var(--line); border-radius:12px; padding:10px 0; flex:1; min-width:80px; max-width:120px; }
.stat .n { font-size:20px; font-weight:800; color:var(--acc); line-height:1.2; }
.stat .t { font-size:9px; color:var(--dim); text-transform:uppercase; letter-spacing:.12em; }
section.card { background:linear-gradient(180deg, rgba(255,255,255,.02), transparent 40%), var(--card); border:1px solid var(--line); border-radius:var(--r); padding:16px; margin-bottom:12px; box-shadow:0 10px 30px rgba(0,0,0,.25); }
h2 { display:flex; align-items:center; gap:8px; font-size:12px; margin:0 0 12px; color:var(--txt); font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
.hbadge { width:22px; height:22px; border-radius:7px; background:rgba(56,189,248,.12); color:var(--sky); display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex:none; }
label { display:block; font-size:10px; font-weight:700; color:var(--dim); margin:10px 0 5px; letter-spacing:.1em; text-transform:uppercase; }
input, select { width:100%; padding:12px 13px; border-radius:12px; border:1px solid var(--line); background:var(--card2); color:var(--txt); font-size:14px; }
input:focus, select:focus { outline:none; border-color:var(--sky); box-shadow:0 0 0 3px rgba(56,189,248,.15); }
select:disabled { opacity:.45; }
.row { display:flex; gap:8px; margin-top:10px; }
button { padding:12px 14px; border:0; border-radius:12px; font-size:14px; font-weight:700; cursor:pointer; transition:transform .1s, opacity .2s; }
button:active { transform:scale(.98); }
button:disabled { opacity:.4; cursor:not-allowed; }
.btn-main { width:100%; margin-top:12px; background:linear-gradient(135deg,#10b981,#34d399); color:#022c22; font-weight:800; }
.btn-sub { flex:1; background:rgba(56,189,248,.12); color:var(--sky); border:1px solid rgba(56,189,248,.3); }
.btn-warn { width:100%; margin-top:12px; background:transparent; color:var(--warn); border:1px solid rgba(248,113,113,.45); }
.cam-wrap { position:relative; width:100%; max-width:300px; margin:12px auto 0; aspect-ratio:3/4; border-radius:var(--r); overflow:hidden; background:var(--card2); border:1px solid var(--line); }
#cam { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transform:scaleX(-1); }
#preview { display:none; width:100%; max-width:300px; margin:12px auto 0; border-radius:var(--r); border:1px solid var(--line); }
#msg { display:none; margin-top:12px; padding:11px 13px; border-radius:12px; font-size:13px; line-height:1.5; }
#msg.ok { display:block; background:var(--acc-dk); color:#86efac; }
#msg.err { display:block; background:#2f0d0d; color:#fca5a5; }
.hint { font-size:11.5px; color:var(--dim); margin-top:8px; line-height:1.5; }
footer { color:var(--dim); font-size:11px; text-align:center; margin:6px 0 14px; }
footer a { color:var(--sky); text-decoration:none; }
.list-wrap { max-height:220px; overflow-y:auto; margin-top:10px; border:1px solid var(--line); border-radius:12px; background:var(--card2); }
.list-wrap:empty { display:none; }
.emp-row { display:flex; align-items:center; gap:10px; padding:9px 12px; border-bottom:1px solid var(--line); font-size:13px; }
.emp-row:last-child { border-bottom:0; }
.emp-name { flex:1; min-width:0; font-weight:600; }
.emp-id { font-size:10.5px; color:var(--sky); background:rgba(56,189,248,.08); border-radius:6px; padding:2px 7px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:45%; }
.emp-noface { font-size:10.5px; color:var(--warn); white-space:nowrap; }
</style>
</head>

<body>
<div class="wrap">
  <header class="top">
    <div class="logo"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.6" stroke="#03291d" stroke-width="2.2"/><path d="M5 19c.8-3.4 3.6-5.2 7-5.2s6.2 1.8 7 5.2" stroke="#03291d" stroke-width="2.2" stroke-linecap="round"/></svg></div>
    <h1>Kelola Wajah &amp; Lokasi</h1>
    <p class="sub">panel admin presensi</p>
    <div class="stats">
      <div class="stat"><div class="n">{{ $stats['employees'] }}</div><div class="t">Karyawan</div></div>
      <div class="stat"><div class="n">{{ $stats['linked'] }}</div><div class="t">Wajah</div></div>
      <div class="stat"><div class="n">{{ $stats['stores'] }}</div><div class="t">Toko</div></div>
      <div class="stat"><div class="n">{{ $stats['cities'] }}</div><div class="t">Kota</div></div>
    </div>
  </header>

  <section class="card">
    <h2><span class="hbadge">1</span> Tambah kota &amp; toko</h2>
    <label for="city">Nama kota</label>
    <input id="city" placeholder="cth: Jakarta">
    <label for="store">Nama toko</label>
    <input id="store" placeholder="cth: Toko Mahakarya Thamrin">
    <label for="address">Alamat (opsional)</label>
    <input id="address" placeholder="cth: Jl. MH Thamrin No. 1">
    <div class="row">
      <div style="flex:1"><label for="lat">Latitude</label><input id="lat" inputmode="decimal" placeholder="-6.19"></div>
      <div style="flex:1"><label for="lon">Longitude</label><input id="lon" inputmode="decimal" placeholder="106.82"></div>
      <div style="flex:1"><label for="radius">Radius (m)</label><input id="radius" inputmode="number" placeholder="150"></div>
    </div>
    <button class="btn-main" id="btnLokasi" type="button">Simpan Kota &amp; Toko</button>
    <div class="hint">💡 Copy lat/lon dari URL Google Maps setelah klik lokasi toko.</div>
  </section>

  <section class="card">
    <h2><span class="hbadge">2</span> Tambah karyawan</h2>
    <label for="empName">Nama karyawan</label>
    <input id="empName" maxlength="120" placeholder="cth: Rina Melati">
    <label for="empStore">Toko (opsional)</label>
    <select id="empStore"><option value="">— pilih toko —</option></select>
    <button class="btn-main" id="btnKaryawan" type="button">Tambah Karyawan</button>
    <div class="list-wrap" id="empList"></div>
  </section>

  <section class="card">
    <h2><span class="hbadge">3</span> Daftarkan wajah</h2>
    <label for="faceEmp">Pilih karyawan</label>
    <select id="faceEmp"><option value="">— pilih karyawan —</option></select>
    <label for="photo">Foto wajah</label>
    <input id="photo" type="file" accept="image/jpeg,image/png,image/webp" capture="user">
    <div class="row">
      <button class="btn-sub" id="btnCam" type="button">📷 Buka Kamera</button>
      <button class="btn-sub" id="btnSnap" type="button" disabled>✓ Ambil Foto</button>
    </div>
    <div class="cam-wrap" id="camBox" style="display:none"><video id="cam" playsinline muted></video></div>
    <img id="preview" alt="pratinjau foto wajah">
    <button class="btn-main" id="btnDaftar" type="button">Daftarkan ke Engine</button>
    <div class="hint">💡 Foto terbaik: wajah menghadap depan, pencahayaan cukup, tanpa masker.</div>
  </section>

  <section class="card">
    <h2><span class="hbadge">4</span> Bersihkan data wajah</h2>
    <button class="btn-warn" id="btnHapus" type="button">Hapus Semua Wajah Engine</button>
    <div class="hint">Hapus semua wajah di engine + lepas face_key; data karyawan &amp; absen tetap aman.</div>
  </section>

  <div id="msg"></div>
  <footer><a href="/">← kembali ke halaman absen</a></footer>
</div>
<canvas id="shot" style="display:none"></canvas>
<script>
const $ = (id) => document.getElementById(id);
let stream = null, snapped = null;

function msg(k, t) { const m = $("msg"); m.className = k; m.textContent = t; }

async function post(url, body, isForm) {
  const opt = { method: "POST", headers: {}, credentials: "same-origin", body };
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  if (isForm) opt.headers["X-CSRF-TOKEN"] = csrf;
  else { opt.headers = { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf }; }
  const r = await fetch(url, opt);
  const j = await r.json().catch(() => ({}));
  if (!r.ok) throw new Error(j.message || ("HTTP " + r.status));
  return j;
}

async function getJSON(url) {
  const r = await fetch(url, { credentials: "same-origin" });
  return await r.json().catch(() => []);
}

async function loadStores() {
  const sel = $("empStore");
  try {
    const rows = await getJSON("/kelola-wajah/toko");
    sel.innerHTML = '<option value="">— pilih toko —</option>';
    rows.forEach((s) => sel.add(new Option(s.name, s.id)));
  } catch (e) { sel.innerHTML = '<option value="">— gagal muat —</option>'; }
}

async function loadEmployees() {
  const list = $("empList");
  const sel = $("faceEmp");
  try {
    const rows = await getJSON("/kelola-wajah/karyawan");
    list.innerHTML = "";
    sel.innerHTML = '<option value="">— pilih karyawan —</option>';
    rows.forEach((e) => {
      const tr = document.createElement("div");
      tr.className = "emp-row";
      tr.innerHTML = '<div class="emp-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + e.name + '</div>' +
        (e.face_key ? '<span class="emp-id">wajah ✓</span>' : '<span class="emp-noface">belum ada wajah</span>');
      list.appendChild(tr);
      const opt = document.createElement("option");
      opt.value = e.id;
      opt.textContent = e.name + (e.face_key ? " (ada wajah)" : "");
      sel.appendChild(opt);
    });
  } catch (e) { list.innerHTML = '<div class="hint" style="padding:10px 12px">Gagal muat karyawan</div>'; }
}


$("btnLokasi").onclick = async () => {
  $("btnLokasi").disabled = true;
  try {
    const j = await post("/kelola-wajah/lokasi", JSON.stringify({
      city: $("city").value, store: $("store").value, address: $("address").value,
      lat: parseFloat($("lat").value), lon: parseFloat($("lon").value),
      radius_m: $("radius").value ? parseInt($("radius").value, 10) : undefined,
    }));
    msg("ok", "✅ Tersimpan: " + j.store.name + " (" + j.city.name + ").");
    loadStores(); loadEmployees();
  } catch (e) { msg("err", e.message); }
  $("btnLokasi").disabled = false;
};

$("btnKaryawan").onclick = async () => {
  if (!$("empName").value.trim()) return msg("err", "Isi nama karyawan dulu.");
  $("btnKaryawan").disabled = true;
  try {
    const j = await post("/kelola-wajah/karyawan", JSON.stringify({
      name: $("empName").value.trim(), store_id: $("empStore").value || undefined,
    }));
    msg("ok", "✅ Karyawan ditambah: " + j.employee.name);
    $("empName").value = "";
    loadEmployees();
  } catch (e) { msg("err", e.message); }
  $("btnKaryawan").disabled = false;
};

$("photo").onchange = () => {
  const f = $("photo").files[0];
  if (!f) return;
  snapped = f;
  $("preview").src = URL.createObjectURL(f);
  $("preview").style.display = "block";
};

$("btnCam").onclick = async () => {
  try {
    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false });
    $("cam").srcObject = stream;
    await $("cam").play();
    $("camBox").style.display = "block";
    $("btnSnap").disabled = false;
  } catch (e) { msg("err", "Kamera tidak bisa dibuka: " + e.message); }
};

$("btnSnap").onclick = () => {
  const v = $("cam"), c = $("shot");
  c.width = v.videoWidth; c.height = v.videoHeight;
  c.getContext("2d").drawImage(v, 0, 0);
  c.toBlob((b) => {
    snapped = new File([b], "wajah.jpg", { type: "image/jpeg" });
    $("preview").src = URL.createObjectURL(snapped);
    $("preview").style.display = "block";
    msg("ok", "📸 Foto dari kamera siap. Klik Daftarkan ke Engine.");
  }, "image/jpeg", 0.92);
};

$("btnDaftar").onclick = async () => {
  if (!$("faceEmp").value) return msg("err", "Pilih karyawan dulu.");
  if (!snapped) return msg("err", "Pilih foto atau ambil dari kamera dulu.");
  $("btnDaftar").disabled = true;
  msg("ok", "⏳ Mengirim ke engine… (bisa beberapa detik)");
  try {
    const fd = new FormData();
    fd.append("employee_id", $("faceEmp").value);
    fd.append("photo", snapped, snapped.name || "wajah.jpg");
    const j = await post("/kelola-wajah/daftar", fd, true);
    msg("ok", "✅ Wajah " + j.employee.name + " terdaftar. Silakan tes absen.");
  } catch (e) { msg("err", e.message); }
  $("btnDaftar").disabled = false;
};

$("btnHapus").onclick = async () => {
  const t = prompt("Ketik persis: HAPUS SEMUA WAJAH");
  if (t !== "HAPUS SEMUA WAJAH") return;
  if (!confirm("Yakin? Semua wajah engine akan dihapus.")) return;
  $("btnHapus").disabled = true;
  try {
    const j = await post("/kelola-wajah/hapus", JSON.stringify({ confirm: "HAPUS SEMUA WAJAH" }));
    msg("ok", j.message);
    loadEmployees();
  } catch (e) { msg("err", e.message); }
  $("btnHapus").disabled = false;
};

loadStores();
loadEmployees();
</script>
</body>
</html>
