<?php // mapdbg.php â€” screenshot peta live + dump state overlay via console
$base = 'http://127.0.0.1:8123/';
$html = @file_get_contents($base);
if (!$html) { echo "serve mati\n"; exit(1); }

$js = <<<'JS'
<script>
window.__dbg = [];
window.addEventListener('error', e => window.__dbg.push('JSERR: ' + e.message));
setTimeout(async () => {
  try {
    const st = [];
    st.push('L=' + (typeof L));
    const m = (typeof geo !== 'undefined') ? geo : null;
    st.push('geo=' + (m ? 'ada' : 'HILANG'));
    if (m) {
      st.push('ready=' + m.ready);
      st.push('circle=' + (m.circle ? 'ada' : 'HILANG'));
      st.push('storePin=' + (m.storePin ? 'ada' : 'HILANG'));
      st.push('userPin=' + (m.userPin ? 'ada' : 'HILANG'));
      if (m.circle) {
        const b = m.circle.getBounds();
        st.push('circleC=' + m.circle.getLatLng().lat.toFixed(6) + ',' + m.circle.getLatLng().lng.toFixed(6));
        st.push('circleR=' + m.circle.getRadius());
        st.push('bounds=' + b.getSouth().toFixed(4) + ',' + b.getWest().toFixed(4) + '|' + b.getNorth().toFixed(4) + ',' + b.getEast().toFixed(4));
      }
      if (m.map) {
        const c = m.map.getCenter();
        st.push('mapC=' + c.lat.toFixed(4) + ',' + c.lng.toFixed(4) + ' z=' + m.map.getZoom());
        st.push('mapSize=' + m.map.getSize().x + 'x' + m.map.getSize().y);
        let layers = 0;
        m.map.eachLayer(() => layers++);
        st.push('layers=' + layers);
        const panes = {};
        ['tilePane','overlayPane','shadowPane','markerPane'].forEach(p => {
          const el = m.map.getPane(p);
          panes[p] = el ? el.childElementCount + 'child' : 'HILANG';
        });
        st.push('panes=' + JSON.stringify(panes));
        const svg = document.querySelector('#map svg');
        st.push('svg=' + (svg ? ('ada ' + svg.getBoundingClientRect().width.toFixed(0) + 'x' + svg.getBoundingClientRect().height.toFixed(0)) : 'HILANG'));
        const glc = document.querySelector('#map canvas');
        st.push('glcanvas=' + (glc ? ('ada ' + glc.width + 'x' + glc.height) : 'HILANG'));
        const paths = document.querySelectorAll('#map path.leaflet-interactive');
        st.push('svgpaths=' + paths.length);
      }
    }
    st.push('state.store=' + ((typeof state !== 'undefined' && state.store) ? state.store.name : 'null'));
    st.push('state.coords=' + ((typeof state !== 'undefined' && state.coords) ? JSON.stringify(state.coords) : 'null'));
    document.title = 'DBG|' + st.join('~');
  } catch (e) { document.title = 'DBG|EXC:' + e.message; }
}, 9000);
</script>
JS;

// auto: pilih kota pertama > toko pertama > sim dalam zona
$auto = <<<'JS2'
<script>
setTimeout(async () => {
  try {
    const cities = await (await fetch('/api/cities')).json();
    if (!cities.length) { document.title = 'DBG|NOCITIES'; return; }
    const stores = await (await fetch('/api/stores?city_id=' + cities[0].id)).json();
    if (!stores.length) { document.title = 'DBG|NOSTORES'; return; }
    const s = stores[0];
    state.city = cities[0].id;
    state.stores = stores;
    state.store = s;
    showZone(s);
    setUser(s.lat + 0.00003, s.lon + 0.00003, 8);
    geo.map.setView([s.lat, s.lon], 16);
  } catch (e) { document.title = 'DBG|AUTOEXC:' + e.message; }
}, 2500);
</script>
JS2;

$html = str_replace('</body>', $js . "\n" . $auto . "\n</body>", $html);
// taruh di public/ biar dibuka via http://127.0.0.1:8123/__mapdbg.html â€” same origin,
// jadi fetch('/api/...') di script auto nggak kena blokir file://.
$rel = '__mapdbg.html';
file_put_contents(__DIR__ . '/../public/' . $rel, $html);
$url = $base . $rel;

$edge = 'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe';
$png = __DIR__ . '/../storage/app/mapdbg.png';
$userData = sys_get_temp_dir() . '/edge-mapdbg-' . getmypid();
$cmd = '"' . $edge . '" --headless=new --disable-gpu --hide-scrollbars --no-first-run'
  . ' --no-default-browser-check --use-gl=swiftshader --enable-unsafe-swiftshader --allow-insecure-localhost'
  . ' --user-data-dir="' . $userData . '" --window-size=390,1200 --virtual-time-budget=17000'
  . ' --screenshot="' . $png . '" "' . $url . '" 2>NUL';
shell_exec($cmd);
echo is_file($png) ? "png OK " . filesize($png) . " bytes\n" : "png GAGAL\n";
