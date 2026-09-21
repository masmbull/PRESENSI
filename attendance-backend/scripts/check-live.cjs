// Verifikasi live /kelola-wajah via Basic auth (baca FACEID_ADMIN_PASSWORD dari .env):
// GET halaman + endpoint JSON. (POST gak diuji di sini — butuh token CSRF sesi browser;
// alur browser aman karena api() di layout kirim X-CSRF-TOKEN + retry 419 otomatis.)
const fs = require('fs');
const base = 'http://localhost:8000';
const env = fs.readFileSync(__dirname + '/../.env', 'utf8');
const pass = (env.match(/^FACEID_ADMIN_PASSWORD=(.*)$/m) || [])[1] || '';
const auth = 'Basic ' + Buffer.from('admin:' + pass.trim()).toString('base64');
const H = { Authorization: auth, Accept: 'application/json' };
(async () => {
  let r = await fetch(base + '/kelola-wajah', { headers: { Authorization: auth } });
  const pg = await r.text();
  console.log('kelola=' + r.status + ' len=' + pg.length);
  for (const s of ['fltActive', 'data-toggleemp', 'data-delstore', 'toggleEmp', 'hapusToko']) {
    console.log(s + '=' + (pg.includes(s) ? 'ADA' : 'HILANG'));
  }
  r = await fetch(base + '/kelola-wajah/karyawan', { headers: H });
  const ct = r.headers.get('content-type') || '';
  console.log('karyawan http=' + r.status + ' ct=' + ct.split(';')[0]);
  const emps = ct.includes('json') ? await r.json() : [];
  console.log('karyawan_n=' + emps.length + ' sample_active=' + (emps[0] ? emps[0].active : '?'));
  r = await fetch(base + '/kelola-wajah/toko', { headers: H });
  const stores = await r.json();
  const isi = stores.find((s) => (s.employees_count || 0) > 0);
  console.log('toko_n=' + stores.length + ' toko_isi=' + (isi ? isi.id + ':' + isi.name : 'TIDAK-ADA'));
  console.log('SELESAI-OK (tanpa mutasi data)');
})().catch((e) => { console.error('GAGAL: ' + e.message); process.exit(1); });
