import json
from pathlib import Path

CITY_COORDS = {
    'BALIKPAPAN': (-1.2694616, 116.8543264),
    'BANDUNG': (-6.914744, 107.60981),
    'BANGKA': (-2.17, 106.12),
    'BANJAR BARU': (-3.442, 114.842),
    'BANJARMASIN': (-3.318, 114.594),
    'BANYUMAS': (-7.46, 109.287),
    'BANYUWANGI': (-8.219, 114.369),
    'BATAM': (1.13, 104.051),
    'BATU': (-7.866, 112.534),
    'BOGOR': (-6.597, 106.806),
    'MALANG': (-7.983, 112.63),
    'BONTANG': (0.133, 117.481),
    'CIREBON': (-6.706, 108.557),
    'DEPOK': (-6.402, 106.794),
    'GARUT': (-7.227, 107.908),
    'JAKARTA': (-6.2088, 106.8456),
    'JEMBER': (-8.184, 113.668),
    'KEDIRI': (-7.816, 112.016),
    'KETAPANG': (-1.849, 109.971),
    'KLATEN': (-7.705, 110.603),
    'KOTAWARINGIN BARAT': (-2.677, 111.616),
    'KUDUS': (-6.804, 110.84),
    'LAMPUNG-METRO': (-5.117, 105.307),
    'LUBUKLINGGAU': (-3.293, 102.851),
    'MAKASSAR': (-5.147, 119.432),
    'MATARAM': (-8.583, 116.118),
    'MEDAN': (3.595, 98.672),
    'MUARA ENIM': (-3.65, 103.782),
    'PADANG': (-0.949, 100.353),
    'PALEMBANG': (-2.99, 104.757),
    'PALU': (-0.891, 119.87),
    'PASER': (-1.741, 116.427),
    'PATI': (-6.756, 111.036),
    'PEKALONGAN': (-6.888, 109.675),
    'PEKANBARU': (0.507, 101.447),
    'PONOROGO': (-7.867, 111.468),
    'PONTIANAK': (-0.026, 109.342),
    'SAMARINDA': (-0.502, 117.153),
    'SAMPIT': (-2.539, 112.949),
    'SEMARANG': (-6.966, 110.414),
    'SURABAYA': (-7.257472, 112.75209),
}

path = Path('stores.json')
rows = json.loads(path.read_text(encoding='utf-8'))

missing = 0
for row in rows:
    city = str(row.get('city', '')).strip()
    lat = row.get('lat')
    lon = row.get('lon')
    if lat is None or lon is None:
        missing += 1
        fallback = CITY_COORDS.get(city, (-2.5489, 118.0149))
        row['lat'] = float(fallback[0])
        row['lon'] = float(fallback[1])
        row['address'] = f'{city}, Indonesia'
    if not row.get('address'):
        row['address'] = f'{city}, Indonesia'

path.write_text(json.dumps(rows, ensure_ascii=False, indent=2), encoding='utf-8')
print(f'total_rows={len(rows)}')
print(f'missing_after_fix={missing}')
print(f'has_missing_latlon={any(row.get("lat") is None or row.get("lon") is None for row in rows)}')
print(f'example={rows[0]}')
