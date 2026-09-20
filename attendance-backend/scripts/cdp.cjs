// cdp.js — driver headless Edge lewat DevTools Protocol (buat verifikasi DOM hasil render
// client-side + screenshot halaman yang butuh login). Tanpa dependensi npm.
// Pakai: node scripts/cdp.js <url> [--shot=out.png] [--eval="expr"] [--wait=4000] [--user=email:pass]
const fs = require('fs');
const { spawn } = require('child_process');

const args = process.argv.slice(2);
const url = args[0] || 'http://localhost:8000/kelola-wajah';
const opt = (k, d) => {
  const a = args.find((x) => x.startsWith('--' + k + '='));
  return a ? a.slice(k.length + 3) : d;
};
const shot = opt('shot', '');
const waitMs = parseInt(opt('wait', '5000'), 10);
const size = opt('size', '1600,2200');
const evals = args.filter((a) => a.startsWith('--eval=')).map((a) => a.slice(7));
const cred = opt('user', ''); // "email:password" → login dulu lewat fetch di halaman.

const EDGE = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const PORT = 9333 + (process.pid % 200);
const profile = require('os').tmpdir() + '\\edge-cdp-' + process.pid;

const edge = spawn(EDGE, [
  '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
  '--remote-debugging-port=' + PORT, '--user-data-dir=' + profile, '--window-size=' + size,
  'about:blank',
], { stdio: 'ignore' });

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function targets() {
  for (let i = 0; i < 60; i++) {
    try {
      const r = await fetch('http://127.0.0.1:' + PORT + '/json/list');
      const list = await r.json();
      const page = list.find((t) => t.type === 'page' && t.webSocketDebuggerUrl);
      if (page) return page.webSocketDebuggerUrl;
    } catch (e) { /* belum siap */ }
    await sleep(250);
  }
  throw new Error('Edge DevTools tidak siap di port ' + PORT);
}

function client(wsUrl) {
  return new Promise((resolve) => {
    const ws = new WebSocket(wsUrl);
    let id = 0;
    const pending = new Map();
    ws.addEventListener('message', (ev) => {
      const m = JSON.parse(ev.data);
      if (m.id && pending.has(m.id)) { pending.get(m.id)(m); pending.delete(m.id); }
    });
    const send = (method, params) => new Promise((res) => {
      const myId = ++id;
      pending.set(myId, res);
      ws.send(JSON.stringify({ id: myId, method, params: params || {} }));
    });
    ws.addEventListener('open', () => resolve({ send, close: () => ws.close(), on: (cb) => ws.addEventListener('message', cb) }));
  });
}

(async () => {
  const wsUrl = await targets();
  const cdp = await client(wsUrl);
  await cdp.send('Page.enable');
  await cdp.send('Runtime.enable');
  await cdp.send('Network.enable');

  const allCookies = async () => {
    const r = await cdp.send('Network.getAllCookies');
    return (r.result.cookies || []).map((c) => c.name + ' (httpOnly=' + c.httpOnly + ', secure=' + c.secure
      + ', sameSite=' + c.sameSite + ', exp=' + Math.round(c.expires) + ')').join(' | ') || '(kosong)';
  };
  const showCookies = () => allCookies().then((s) => console.log('cookies: ' + s));

  const evalJs = async (expr) => {
    const r = await cdp.send('Runtime.evaluate', { expression: expr, awaitPromise: true, returnByValue: true });
    const res = r.result || {};
    if (res.exceptionDetails) return 'ERROR: ' + JSON.stringify(res.exceptionDetails.exception || res.exceptionDetails.text);
    return res.result ? res.result.value : null;
  };

  await cdp.send('Page.navigate', { url: 'http://localhost:8000/' });
  await sleep(1200);

  // Pantau request yang kena redirect ke /admin/login → ketahuan mana yang dianggap
  // belum login (biar gak nebak-nebak dari hasil eval doang).
  const netlog = [];
  cdp.on((ev) => {
    const m = JSON.parse(ev.data);
    if (m.method === 'Network.responseReceived' && /\/kelola-wajah|\/admin/.test(m.params.response.url)) {
      netlog.push((m.params.response.status + ' ' + m.params.response.url).replace('http://localhost:8000', ''));
    }
    if (m.method === 'Network.requestWillBeSentExtraInfo' && /\/kelola-wajah\//.test(m.params.headers[':path'] || '')) {
      const ck = (m.params.headers.Cookie || '(tanpa cookie)');
      console.log('req ' + m.params.headers[':path'] + ' cookie=[' + ck.replace(/=(.{18}).*?(?=;|$)/g, '=$1…') + ']');
    }
  });

  if (cred) {
    const [email, pass] = cred.split(':');
    console.log('login: ' + await evalJs(`(async () => {
      const h = await (await fetch('/admin/login')).text();
      const t = (h.match(/name="_token" value="([^"]+)"/) || [])[1];
      const r = await fetch('/admin/login', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_token=' + encodeURIComponent(t) + '&email=' + encodeURIComponent(${JSON.stringify(email)}) + '&password=' + encodeURIComponent(${JSON.stringify(pass)}) });
      return r.status + ' → ' + r.url;
    })()`));
    await showCookies();
  }

  await cdp.send('Page.navigate', { url });
  await sleep(waitMs);

  console.log('net: ' + (netlog.join(' | ') || '(tidak ada)'));
  console.log('title: ' + await evalJs('document.title'));
  console.log('js errors: ' + await evalJs('window.__errs ? window.__errs.join(" | ") : "(tidak dipantau)"'));
  for (const e of evals) console.log('eval: ' + await evalJs(e));

  if (shot) {
    const r = await cdp.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true });
    fs.writeFileSync(shot, Buffer.from(r.result.data, 'base64'));
    console.log('shot: ' + shot + ' (' + fs.statSync(shot).size + ' byte)');
  }

  cdp.close();
  edge.kill();
  await sleep(300);
  process.exit(0);
})().catch((e) => { console.error('GAGAL: ' + e.message); edge.kill(); process.exit(1); });