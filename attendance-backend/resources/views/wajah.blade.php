<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#070b10">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Admin · Presensi SPG</title>
<style>
:root { --bg:#070b10; --card:#101722; --card2:#0c121b; --line:#1c2735; --txt:#eaf1f8; --dim:#8296ab; --acc:#34d399; --acc-dk:#052e16; --warn:#f87171; --mid:#fbbf24; --sky:#38bdf8; --r:16px; }
* { box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
body { margin:0; padding:14px 14px 40px; background:linear-gradient(180deg,#0c1522,#070b10 340px) no-repeat, var(--bg); color:var(--txt); font:15px/1.5 system-ui,"Segoe UI",Roboto,sans-serif; }
.wrap { max-width:520px; max-width:min(520px, 100%); margin:0 auto; }
header.top { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:4px 0 14px; }
.brand { display:flex; align-items:center; gap:10px; min-width:0; }
.logo { width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg,#0ea56b,#34d399); display:flex; align-items:center; justify-content:center; box-shadow:0 6px 18px rgba(52,211,153,.25); flex:none; }
h1 { font-size:16px; margin:0; }
.sub { margin:0; font-size:11px; color:var(--dim); }
.chip { font-size:11px; color:var(--dim); border:1px solid var(--line); border-radius:999px; padding:6px 11px; background:rgba(16,23,34,.6); white-space:nowrap; }
.chip b { color:var(--acc); font-weight:600; }
/* ---------- tabs ---------- */
.tabs { display:flex; gap:6px; margin-bottom:12px; background:var(--card); border:1px solid var(--line); border-radius:14px; padding:5px; position:sticky; top:8px; z-index:900; backdrop-filter:blur(10px); }
.tab { flex:1; padding:9px 4px; text-align:center; font-size:11.5px; font-weight:700; color:var(--dim); border-radius:10px; cursor:pointer; border:1px solid transparent; transition:all .18s; white-space:nowrap; }
.tab:hover { color:var(--txt); }
.tab.on { background:linear-gradient(135deg, rgba(52,211,153,.18), rgba(56,189,248,.12)); color:var(--acc); border-color:rgba(52,211,153,.35); }
.tab .n { display:inline-block; min-width:16px; padding:0 4px; margin-left:3px; border-radius:999px; background:var(--card2); font-size:10px; color:var(--dim); }
.tab.on .n { background:rgba(52,211,153,.18); color:var(--acc); }
/* ---------- panel & form ---------- */
.panel { display:none; }
.panel.on { display:block; }
section.card { background:linear-gradient(180deg, rgba(255,255,255,.02), transparent 40%), var(--card); border:1px solid var(--line); border-radius:var(--r); padding:16px; margin-bottom:12px; box-shadow:0 10px 30px rgba(0,0,0,.25); }
h2 { font-size:11px; margin:0 0 12px; color:var(--dim); font-weight:700; text-transform:uppercase; letter-spacing:.12em; display:flex; align-items:center; gap:8px; }
h2 .no { width:20px; height:20px; border-radius:7px; background:rgba(56,189,248,.12); border:1px solid rgba(56,189,248,.3); color:var(--sky); font-size:10px; display:flex; align-items:center; justify-content:center; flex:none; }
label.fld { display:block; margin-bottom:10px; }
.flbl { display:block; font-size:10px; font-weight:700; color:var(--dim); margin-bottom:5px; letter-spacing:.1em; }
input, select { width:100%; padding:11px 13px; border-radius:12px; border:1px solid var(--line); background:var(--card2); color:var(--txt); font-size:14px; }
input:focus, select:focus { outline:none; border-color:var(--sky); box-shadow:0 0 0 3px rgba(56,189,248,.15); }
input::placeholder { color:#4a5c70; }
select:disabled, input:disabled { opacity:.45; }
select option { background:var(--card); }
.grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; }
.row { display:flex; gap:8px; margin-top:10px; }

</style>
</head>
<body>
<div class="wrap">
  <header class="top">
    <div class="brand">
      <div class="logo"><svg viewBox="0 0 24 24" width="22" height="22" fill="none"><circle cx="12" cy="12" r="9.2" stroke="#03291d" stroke-width="2.2"/><path d="M8 12.5l3 3 5.5-6" stroke="#03291d" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
      <div><h1>Admin Presensi</h1><p class="sub">kelola toko &amp; wajah</p></div>
    </div>
    <span class="chip"><b>{{ $stats['employees'] }}</b> karyawan &middot; <b>{{ $stats['linked'] }}</b> wajah</span>
  </header>

  <nav class="tabs">
    <div class="tab on" data-p="pLokasi">📍 Toko</div>
    <div class="tab" data-p="pKaryawan">👥 Karyawan<span class="n" id="tEmp">…</span></div>
    <div class="tab" data-p="pWajah">🙂 Wajah</div>
    <div class="tab" data-p="pDanger">🗑️</div>
  </nav>

  <!-- ================= PANEL TOKO ================= -->
  <div class="panel on" id="pLokasi">
    <section class="card">
      <h2><span class="no">1</span> Tambah kota</h2>
      <label class="fld"><span class="flbl">NAMA KOTA</span><input id="city" placeholder="cth: Jakarta"></label>
      <button class="btn-main" id="btnKota" type="button">Simpan Kota</button>
      <div class="hint">Kota = pengelompokan toko. Kalau nama udah ada, sistem pakai yang lama (gak bikin dobel).</div>
    </section>

    <section class="card">
      <h2><span class="no">2</span> Tambah toko</h2>
      <label class="fld"><span class="flbl">KOTA</span><select id="storeCity"><option value="">-- pilih kota --</option></select></label>
      <label class="fld"><span class="flbl">NAMA TOKO</span><input id="store" placeholder="cth: Toko Mahakarya Thamrin"></label>
      <label class="fld"><span class="flbl">ALAMAT (OPSIONAL)</span><input id="address" placeholder="cth: Jl. MH Thamrin No. 1"></label>
      <div class="grid3">
        <div><label class="fld"><span class="flbl">LAT</span><input id="lat" inputmode="decimal" placeholder="-6.19"></label></div>
        <div><label class="fld"><span class="flbl">LON</span><input id="lon" inputmode="decimal" placeholder="106.82"></label></div>
        <div><label class="fld"><span class="flbl">RADIUS M</span><input id="radius" inputmode="numeric" placeholder="150"></label></div>
      </div>
      <button class="btn-main" id="btnLokasi" type="button">Simpan Toko</button>
      <div class="hint">Copy lat/lon dari URL Google Maps setelah klik lokasi toko. Radius = jarak maks absen dari titik toko.</div>
    </section>
  </div>

  <!-- ================= PANEL KARYAWAN ================= -->
  <div class="panel" id="pKaryawan">
    <section class="card">
      <h2><span class="no">1</span> Tambah karyawan</h2>
      <label class="fld"><span class="flbl">NAMA KARYAWAN</span><input id="empName" maxlength="120" placeholder="cth: Rina Melati"></label>
      <label class="fld"><span class="flbl">TOKO</span><select id="empStore"><option value="">-- pilih toko --</option></select></label>
      <button class="btn-main" id="btnKaryawan" type="button">Tambah Karyawan</button>
      <div class="hint">Nama sama di toko yang sama otomatis digabung (gak dobel). Satu nama boleh nongol di toko berbeda.</div>
    </section>

    <section class="card">
      <h2><span class="no">2</span> Daftar karyawan</h2>
      <div class="count-bar">
        <div class="count-box s"><div class="n" id="cTotal">—</div><div class="l">Total</div></div>
        <div class="count-box g"><div class="n" id="cFace">—</div><div class="l">Ada wajah</div></div>
        <div class="count-box r"><div class="n" id="cNoFace">—</div><div class="l">Belum wajah</div></div>
      </div>
      <div class="list-tools">
        <input id="empSearch" placeholder="cari nama…">
        <select id="fltStore"><option value="">semua toko</option></select>
      </div>
      <div class="list-wrap" id="empList"></div>
    </section>
  </div>

  <!-- ================= PANEL WAJAH ================= -->
  <div class="panel" id="pWajah">
    <section class="card">
      <h2><span class="no">1</span> Daftarkan wajah</h2>
      <label class="fld"><span class="flbl">KARYAWAN</span><select id="faceEmp"><option value="">-- pilih karyawan --</option></select></label>
      <label class="fld"><span class="flbl">FOTO WAJAH</span><input id="photo" type="file" accept="image/jpeg,image/png,image/webp" capture="user"></label>
      <div class="row">
        <button class="btn-sub" id="btnCam" type="button">📷 Kamera</button>
        <button class="btn-sub" id="btnSnap" type="button" disabled>📸 Ambil Foto</button>
      </div>
      <div class="cam-wrap" id="camBox" style="display:none"><video id="cam" playsinline muted></video></div>
      <img id="preview" alt="pratinjau foto wajah">
      <button class="btn-main" id="btnDaftar" type="button">Daftarkan ke Engine</button>
      <div class="hint">Foto terbaik: wajah depan, terang, tidak tertutup. Nama di engine otomatis "nama — toko" biar gak tabrakan antar toko.</div>
    </section>
  </div>

  <!-- ================= PANEL DANGER ================= -->
  <div class="panel" id="pDanger">
    <section class="card">
      <h2><span class="no">!</span> Zone bahaya</h2>
      <button class="btn-warn" id="btnHapus" type="button">Hapus Semua Wajah Engine</button>
      <div class="hint">Hapus semua wajah di engine + lepas face_key; karyawan &amp; absen tetap ada. Data absen gak tersentuh.</div>
    </section>
  </div>

  <div id="msg"></div>
  <footer><a href="/">← kembali ke halaman absen</a></footer>
</div>
<canvas id="shot" style="display:none"></canvas>

<script>
const $ = (id) => document.getElementById(id);
let stream = null, snapped = null, EMPLOYEES = [], STORES = [], CITIES = [];

function msg(k, t) { const m = $("msg"); m.className = k; m.textContent = t; if (t) { clearTimeout(msg._t); msg._t = setTimeout(() => { m.className = ""; }, 6000); } }

async function post(url, body, isForm) {
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const opt = isForm
    ? { method: "POST", headers: { "X-CSRF-TOKEN": csrf }, body }
    : { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf }, body };
  const r = await fetch(url, opt);
  const j = await r.json().catch(() => ({}));
  if (!r.ok) throw new Error(j.message || ("HTTP " + r.status));
  return j;
}

async function getJSON(url) {
  const r = await fetch(url, { credentials: "same-origin" });
  return await r.json().catch(() => []);
}

// ---------- tabs ----------
document.querySelectorAll(".tab").forEach((t) => {
  t.onclick = () => {
    document.querySelectorAll(".tab").forEach((x) => x.classList.remove("on"));
    document.querySelectorAll(".panel").forEach((x) => x.classList.remove("on"));
    t.classList.add("on");
    $(t.dataset.p).classList.add("on");
    if (t.dataset.p === "pKaryawan") renderEmpList();
  };
});

function initials(name) {
  return (name || "?").trim().split(/\s+/).map((w) => w[0]).slice(0, 2).join("").toUpperCase();
}

// ---------- muat master data ----------
async function loadStores() {
  try {
    STORES = await getJSON("/kelola-wajah/toko");
    for (const sel of [$("empStore"), $("fltStore")]) {
      const cur = sel.value;
      sel.innerHTML = sel.id === "fltStore" ? '<option value="">semua toko</option>' : '<option value="">-- pilih toko --</option>';
      STORES.forEach((s) => sel.add(new Option(s.name + (s.city ? " · " + s.city.name : ""), s.id)));
      if (cur) sel.value = cur;
    }
    renderEmpList();
  } catch (e) { msg("err", "Gagal muat toko: " + e.message); }
}

async function loadCities() {
  try {
    CITIES = await getJSON("/kelola-wajah/kota");
    const sel = $("storeCity");
    sel.innerHTML = '<option value="">-- pilih kota --</option>';
    CITIES.forEach((c) => sel.add(new Option(c.name + " (" + c.stores_count + " toko)", c.id)));
  } catch (e) { msg("err", "Gagal muat kota: " + e.message); }
}

async function loadEmployees() {
  const list = $("empList");
  const sel = $("faceEmp");
  try {
    EMPLOYEES = await getJSON("/kelola-wajah/karyawan");
    $("tEmp").textContent = EMPLOYEES.length;
    const nFace = EMPLOYEES.filter((e) => e.face_key).length;
    $("cTotal").textContent = EMPLOYEES.length;
    $("cFace").textContent = nFace;
    $("cNoFace").textContent = EMPLOYEES.length - nFace;
    sel.innerHTML = '<option value="">-- pilih karyawan --</option>';
    EMPLOYEES.forEach((e) => sel.add(new Option(e.name + (e.store ? " · " + e.store.name + (e.store.city ? ", " + e.store.city.name : "") : "") + (e.face_key ? " ✓wajah" : ""), e.id)));
    renderEmpList();
  } catch (e) { list.innerHTML = '<div class="emp-row"><span class="hint">Gagal muat karyawan</span></div>'; }
}

// ---------- daftar karyawan (search + filter + highlight belum-wajah) ----------
function renderEmpList() {
  const q = $("empSearch").value.trim().toLowerCase();
  const fs = $("fltStore").value;
  let rows = EMPLOYEES;
  if (fs) rows = rows.filter((e) => e.store_id == fs);
  if (q) rows = rows.filter((e) => e.name.toLowerCase().includes(q));
  // yang belum ada wajah nongol duluan
  rows = [...rows].sort((a, b) => (a.face_key ? 1 : 0) - (b.face_key ? 1 : 0) || a.name.localeCompare(b.name));
  const list = $("empList");
  if (!rows.length) { list.innerHTML = '<div class="emp-row"><span class="hint">gak ada yang cocok</span></div>'; return; }
  list.innerHTML = "";
  rows.slice(0, 200).forEach((e) => {
    const el = document.createElement("div");
    el.className = "emp-row";
    el.innerHTML =
      '<div class="ava ' + (e.face_key ? "ok" : "no") + '">' + (e.face_key ? "✓" : initials(e.name)) + '</div>' +
      '<div class="emp-mid"><div class="emp-name">' + e.name + '</div>' +
      '<div class="emp-store">' + (e.store ? e.store.name + (e.store.city ? " · " + e.store.city.name : "") : "— tanpa toko —") + (e.employee_code ? " · " + e.employee_code : "") + '</div></div>' +
      '<span class="st ' + (e.face_key ? "ok" : "no") + '">' + (e.face_key ? "WAJAH ✓" : "BELUM") + '</span>';
    // klik → langsung lompat ke panel wajah dengan karyawan ini kepilih
    el.onclick = () => {
      $("faceEmp").value = e.id;
      document.querySelector('[data-p="pWajah"]').click();
      msg("ok", "Daftarkan wajah buat " + e.name + (e.store ? " @ " + e.store.name : "") + " → pilih foto, terus Daftarkan.");
    };
    list.appendChild(el);
  });
}
$("empSearch").oninput = renderEmpList;
$("fltStore").onchange = renderEmpList;
</script>

<script>
// ---------- tambah kota ----------
$("btnKota").onclick = async () => {
  const name = $("city").value.trim();
  if (!name) return msg("err", "Isi nama kota dulu.");
  $("btnKota").disabled = true;
  try {
    const j = await post("/kelola-wajah/kota", JSON.stringify({ name }));
    msg("ok", "Kota tersimpan: " + j.city.name + (j.created ? " (baru)" : " (udah ada — dipakai yang lama)"));
    $("city").value = "";
    await loadCities();
    if (j.city.id) $("storeCity").value = j.city.id;
  } catch (e) { msg("err", e.message); }
  $("btnKota").disabled = false;
};

// ---------- tambah toko ----------
$("btnLokasi").onclick = async () => {
  if (!$("storeCity").value) return msg("err", "Pilih kota dulu.");
  if (!$("store").value.trim()) return msg("err", "Isi nama toko.");
  if ($("lat").value === "" || $("lon").value === "") return msg("err", "Isi lat & lon toko (dari Google Maps).");
  $("btnLokasi").disabled = true;
  try {
    const j = await post("/kelola-wajah/lokasi", JSON.stringify({
      city_id: parseInt($("storeCity").value, 10),
      store: $("store").value.trim(),
      address: $("address").value.trim() || undefined,
      lat: parseFloat($("lat").value),
      lon: parseFloat($("lon").value),
      radius_m: $("radius").value ? parseInt($("radius").value, 10) : undefined,
    }));
    msg("ok", "Toko tersimpan: " + j.store.name + " (" + (j.store.city ? j.store.city.name : j.city.name) + ")");
    $("store").value = ""; $("address").value = ""; $("lat").value = ""; $("lon").value = ""; $("radius").value = "";
    await loadStores();
    await loadEmployees();
  } catch (e) { msg("err", e.message); }
  $("btnLokasi").disabled = false;
};

// ---------- tambah karyawan ----------
$("btnKaryawan").onclick = async () => {
  if (!$("empName").value.trim()) return msg("err", "Isi nama karyawan dulu.");
  $("btnKaryawan").disabled = true;
  try {
    const j = await post("/kelola-wajah/karyawan", JSON.stringify({
      name: $("empName").value.trim(),
      store_id: $("empStore").value || undefined,
    }));
    msg("ok", (j.created === false ? "Udah ada, dipakai: " : "Karyawan ditambah: ") + j.employee.name +
      (j.employee.store ? " @ " + j.employee.store.name : ""));
    $("empName").value = "";
    await loadEmployees();
  } catch (e) { msg("err", e.message); }
  $("btnKaryawan").disabled = false;
};

// ---------- kamera & daftar wajah ----------
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
    msg("ok", "Foto dari kamera siap. Klik Daftarkan.");
  }, "image/jpeg", 0.92);
};

$("btnDaftar").onclick = async () => {
  if (!$("faceEmp").value) return msg("err", "Pilih karyawan dulu.");
  if (!snapped) return msg("err", "Pilih foto atau ambil dari kamera dulu.");
  $("btnDaftar").disabled = true;
  msg("ok", "Mengirim ke engine… (bisa beberapa detik)");
  try {
    const fd = new FormData();
    fd.append("employee_id", $("faceEmp").value);
    fd.append("photo", snapped, snapped.name || "wajah.jpg");
    const j = await post("/kelola-wajah/daftar", fd, true);
    msg("ok", "Wajah " + j.employee.name + " terdaftar ✓ — silakan tes absen.");
    snapped = null;
    $("photo").value = "";
    $("preview").style.display = "none";
    if (stream) { stream.getTracks().forEach((t) => t.stop()); stream = null; $("camBox").style.display = "none"; $("btnSnap").disabled = true; }
    await loadEmployees();
  } catch (e) { msg("err", e.message); }
  $("btnDaftar").disabled = false;
};

// ---------- danger zone ----------
$("btnHapus").onclick = async () => {
  const t = prompt("Ketik persis: HAPUS SEMUA WAJAH");
  if (t !== "HAPUS SEMUA WAJAH") return;
  if (!confirm("Yakin? Semua wajah engine akan dihapus.")) return;
  $("btnHapus").disabled = true;
  try {
    const j = await post("/kelola-wajah/hapus", JSON.stringify({ confirm: "HAPUS SEMUA WAJAH" }));
    msg("ok", j.message);
    await loadEmployees();
  } catch (e) { msg("err", e.message); }
  $("btnHapus").disabled = false;
};

loadCities();
loadStores();
loadEmployees();
</script>
</body>
</html>

