<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Kelola Wajah & Lokasi</title>
<style>
:root { --bg:#0a0e13; --card:#121a26; --line:#1f2b3a; --txt:#e8eef5; --dim:#8ba0b5; --acc:#4ade80; --warn:#f87171; --sky:#38bdf8; }
* { box-sizing:border-box; }
body { margin:0; padding:14px; background:var(--bg); color:var(--txt); font:15px/1.5 system-ui,"Segoe UI",Roboto,sans-serif; }
.wrap { max-width:520px; margin:0 auto; }
header.top { display:flex; align-items:center; justify-content:center; margin:2px 0 12px; }
h1 { font-size:17px; margin:0; }
.chip { font-size:11px; color:var(--dim); border:1px solid var(--line); border-radius:999px; padding:4px 10px; }
section { background:var(--card); border:1px solid var(--line); border-radius:14px; padding:14px; margin-bottom:12px; }
h2 { font-size:11px; margin:0 0 10px; color:var(--dim); font-weight:600; text-transform:uppercase; letter-spacing:.1em; }
label { display:block; font-size:12px; color:var(--dim); margin:8px 0 4px; }
input, select { width:100%; padding:11px 12px; border-radius:10px; border:1px solid var(--line); background:#0c121b; color:var(--txt); font-size:14px; }
input:focus, select:focus { outline:1px solid var(--sky); }
.row { display:flex; gap:8px; margin-top:10px; }
button { padding:12px; border:0; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; }
button:disabled { opacity:.4; cursor:not-allowed; }
.btn-main { width:100%; margin-top:10px; background:var(--acc); color:#052e16; }
.btn-sub { flex:1; background:var(--sky); color:#082f49; }
.btn-warn { width:100%; margin-top:10px; background:transparent; color:var(--warn); border:1px solid var(--warn); }
.cam-wrap { position:relative; width:100%; max-width:300px; margin:10px auto 0; aspect-ratio:3/4; border-radius:14px; overflow:hidden; background:#0c121b; }
#cam { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transform:scaleX(-1); }
#preview { display:none; width:100%; max-width:300px; margin:10px auto 0; border-radius:12px; }
#msg { display:none; margin-top:10px; padding:10px 12px; border-radius:10px; font-size:13px; }
#msg.ok { display:block; background:#052e16; color:#86efac; }
#msg.err { display:block; background:#2f0d0d; color:#fca5a5; }
.hint { font-size:12px; color:var(--dim); margin-top:8px; }
footer { color:var(--dim); font-size:11px; text-align:center; margin:4px 0 12px; }
footer a { color:var(--sky); }
.list-wrap { max-height:200px; overflow-y:auto; }
.emp-row { display:flex; align-items:center; gap:8px; padding:8px 0; border-bottom:1px solid var(--line); }
.emp-name { flex:1; min-width:0; }
.emp-id { font-size:11px; color:var(--sky); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.emp-noface { font-size:11px; color:var(--warn); }
</style>
</head>
<body>
<div class="wrap">
  <header class="top">
    <h1>Kelola Wajah & Lokasi</h1>
    <span class="chip">{{ $stats['employees'] }} karyawan &middot; {{ $stats['linked'] }} wajah &middot; {{ $stats['stores'] }} toko &middot; {{ $stats['cities'] }} kota</span>
  </header>

  <section>
    <h2>1 &middot; Tambah kota & toko</h2>
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
    <button class="btn-main" id="btnLokasi">Simpan Kota & Toko</button>
        <div class="hint">Copy lat/lon dari URL Google Maps setelah klik lokasi toko.</div>
  </section>

  <!-- SECTION 2: Tambah Karyawan -->
  <section>
    <h2>2 &middot; Tambah karyawan (belum ada wajah)</h2>
    <label for="empName">Nama karyawan</label>
    <input id="empName" maxlength="120" placeholder="cth: Rina Melati">
    <label for="empStore">Toko (opsional)</label>
    <select id="empStore"><option value="">-- pilih toko --</option></select>
    <button class="btn-main" id="btnKaryawan">Tambah Karyawan</button>
    <div class="hint">Karyawan yang sudah ditambah tampil di bawah.</div>
    <div class="list-wrap" id="empList"></div>
  </section>

  <!-- SECTION 3: Tambah Wajah -->
    <label for="faceEmp">Pilih karyawan</label>
    <select id="faceEmp"><option value="">-- pilih karyawan --</option></select>
    <label for="photo">Foto wajah</label>
    <input id="photo" type="file" accept="image/jpeg,image/png,image/webp" capture="user">
    <div class="row">
      <button class="btn-sub" id="btnCam" type="button">Kamera</button>
      <button class="btn-sub" id="btnSnap" type="button" disabled>Ambil Foto</button>
    </div>
    <div class="cam-wrap" id="camBox" style="display:none"><video id="cam" playsinline muted></video></div>
    <img id="preview" alt="pratinjau foto wajah">
    <button class="btn-main" id="btnDaftar">Daftarkan ke Engine</button>
    <div class="hint">Foto terbaik: wajah depan, terang, tidak tertutup.</div>
  </section>

  <section>
    <h2>4 &middot; Bersihkan data wajah lama</h2>
    <button class="btn-warn" id="btnHapus">Hapus Semua Wajah Engine</button>
    <div class="hint">Hapus semua wajah di engine + lepas face_key; karyawan & absen tetap ada.</div>
  </section>

  <div id="msg"></div>
  <footer><a href="/">kembali ke halaman absen</a></footer>
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
    sel.innerHTML = '<option value="">-- pilih toko --</option>';
    rows.forEach((s) => sel.add(new Option(s.name, s.id)));
  } catch (e) { sel.innerHTML = '<option value="">-- gagal muat --</option>'; }
}

async function loadEmployees() {
  const list = $("empList");
  const sel = $("faceEmp");
  try {
    const rows = await getJSON("/kelola-wajah/karyawan");
    list.innerHTML = "";
    sel.innerHTML = '<option value="">-- pilih karyawan --</option>';
    rows.forEach((e) => {
      const tr = document.createElement("div");
      tr.className = "emp-row";
      tr.innerHTML = '<div class="emp-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + e.name + '</div>' +
        (e.face_key ? '<span class="emp-id">wajah: ' + e.face_key + '</span>' : '<span class="emp-noface">belum wajah</span>');
      list.appendChild(tr);
      const opt = document.createElement("option");
      opt.value = e.id;
      opt.textContent = e.name + (e.face_key ? " (ada wajah)" : "");
      sel.appendChild(opt);
    });
  } catch (e) { list.innerHTML = '<div class="hint">Gagal muat karyawan</div>'; }
}

$("btnLokasi").onclick = async () => {
  $("btnLokasi").disabled = true;
  try {
    const j = await post("/kelola-wajah/lokasi", JSON.stringify({
      city: $("city").value, store: $("store").value, address: $("address").value,
      lat: parseFloat($("lat").value), lon: parseFloat($("lon").value),
      radius_m: $("radius").value ? parseInt($("radius").value, 10) : undefined,
    }));
    msg("ok", "Tersimpan: " + j.store.name + " (" + j.city.name + ").");
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
    msg("ok", "Karyawan ditambah: " + j.employee.name);
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
    msg("ok", "Foto dari kamera siap. Klik Daftarkan.");
  }, "image/jpeg", 0.92);
};

$("btnDaftar").onclick = async () => {
  if (!$("faceEmp").value) return msg("err", "Pilih karyawan dulu.");
    if (!snapped) return msg("err", "Pilih foto atau ambil dari kamera dulu.");
  $("btnDaftar").disabled = true;
  try {
    const fd = new FormData();
    fd.append("employee_id", $("faceEmp").value);
    fd.append("photo", snapped, snapped.name || "wajah.jpg");
    const j = await post("/kelola-wajah/daftar", fd, true);
    msg("ok", "Wajah " + j.employee.name + " terdaftar. Silakan tes absen.");
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

