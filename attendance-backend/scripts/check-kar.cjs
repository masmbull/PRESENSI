// Ambil /admin/karyawan hasil render, ekstrak tiap <script>, node --check satu-satu.
const fs = require('fs');
const { execFileSync } = require('child_process');
const base = 'http://localhost:8000';
const env = fs.readFileSync(__dirname + '/../.env', 'utf8');
const pass = (env.match(/^FACEID_ADMIN_PASSWORD=(.*)$/m) || [])[1] || '';
const auth = 'Basic ' + Buffer.from('admin:' + pass.trim()).toString('base64');

(async () => {
  const r = await fetch(base + '/admin/karyawan', { headers: { Authorization: auth } });
  const html = await r.text();
  console.log('page=' + r.status + ' len=' + html.length);
  for (const s of ['iCity', 'fillStores', 'STORE_LIST', 'pilih kota dulu']) {
    console.log(s + '=' + (html.includes(s) ? 'ADA' : 'HILANG'));
  }
  const scripts = [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].map((m) => m[1]);
  let ok = 0, fail = 0;
  scripts.forEach((js, i) => {
    const f = process.env.TEMP + '\\karjs' + i + '.js';
    fs.writeFileSync(f, js);
    try { execFileSync('node', ['--check', f], { stdio: 'pipe' }); ok++; }
    catch (e) { fail++; console.log('SCRIPT-' + i + ' GAGAL:\n' + e.stderr.toString().split('\n').slice(0, 4).join('\n')); }
  });
  console.log('scripts=' + scripts.length + ' ok=' + ok + ' gagal=' + fail);
  process.exit(fail ? 1 : 0);
})();
