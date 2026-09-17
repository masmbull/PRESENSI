import requests, json, time, sys

def search_place(name):
    url = 'https://nominatim.openstreetmap.org/search'
    params = {'q': f'{name}, Indonesia', 'format': 'json', 'limit': 1}
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36'}
    try:
        r = requests.get(url, headers=headers, params=params, timeout=10)
        data = r.json()
        if data and data[0]:
            res = data[0]
            return {
                'lat': float(res['lat']),
                'lon': float(res['lon']),
                'address': res['display_name'],
            }
    except Exception as e:
        print(f"  [ERROR] {name}: {e}", file=sys.stderr)
    return None

# Daftar semua toko dari proyekmu
stores = [
    "NUANSA BALIKPAPAN",
    "NUANSA BALIKPAPAN KILO",
    "SURYA JAYA BALIKPAPAN",
    "TOKO GYS JAYA",
    "MUTIARA SUPER KITCHEN UJUNG BERUNG",
    "BLIBLI ELEKTRONIK MOCH TOHA",
    "HARTONO",
    "LOGIN MEGASTORE (ABC)",
    "MUTIARA SUPER KITCHEN",
    "STEIN HARTONO",
    "TOKO PANTES AGUNG (ONLINE)",
    "MITO - SALES B2B",
    "NUANSA MARTAPURA",
    "CENTRAL PLASTIK PALANGKARAYA",
    "GOLDEN 10",
    "PONDOK ELEKTRONIK",
    "POS ELEKTRONIK",
    "PONDOK ELEKTRONIK BANJARBARU",
    "DEPO PELITA",
    "BARES",
    "BARES ROGOJAMPI",
    "JACKSON HOMEWARE MARKET",
    "UNIVERSAL PASIR PUTIH",
    "TOP 100 SWALAYAN",
    "CAHAYA BARU",
    "OGAN BANDULAN & OGAN BONDOWOSO",
    "NUANSA ELEKTRONIK BONTANG",
    "SINAR JAYA",
    "HARAPAN BARU",
    "TOKO SINAR GUNTUR",
    "Admin National",
    "JABODETABEK",
    "HARTONO PONDOK INDAH",
    "JAWA ELEKTRONIK",
    "SPN HOUSEWARE",
    "STEIN - SPG/SPB MT",
    "TOKO ATRIA KELAPA GADING",
    "TOKO EMETAL ASIA JAYA (ONLINE)",
    "TOKO PALAPA",
    "MALL KELAPA GADING",
    "TOKO RAMAI JAYA JEMBER",
    "TOKO RAMAI JAYA RAMBIPUJI",
    "UFO ELEKTRONIK KEDIRI",
    "OSCAR GEMILANG",
    "LARIS KLATEN",
    "NUANSA PANGKALANBUN",
    "TOKO DEPO MURAH JAYA SENTOSA KUDUS",
    "CV GEMBIRA HOUSEWARE",
    "ANUGERAH SUKSES",
    "MAKASSAR",
    "ALASKA",
    "GRAND TOSERBA PENGAYOMAN",
    "MAXI ALAUDDIN",
    "MAXI PERINTIS",
    "SEMERU PERINTIS",
    "SEMERU SARAPPO",
    "TOKO IWATA",
    "HARTONO MALANG",
    "DAPUR KITA MATARAM",
    "TOKO JAYA KITCHEN LOMBOK",
    "Kota Medan",
    "BALIKADO MENTENG",
    "IRAMA HOUSEWARE",
    "SH MART ELEKTRONIK",
    "WIEGO HOUSEWARE TRITURA",
    "CV ANEKA JAYA ELEKTRONIK",
    "A FAUZI",
    "PALEMBANG",
    "DAPOER MUTIARA (CV PELITA MAS)",
    "MDP SUPERSTORE",
    "ANUGRAH JAYA PALU",
    "HOKKY HOUSE WARE",
    "SMART KITCHEN",
    "STAR KITCHEN",
    "TIARA PALU",
    "NUANSA ELEKTRONIC TANAH GROGOT",
    "PUSAT ELEKTRONIK",
    "BERKAH JAYA ELECTRONICS",
    "BERKAT ELEKTRONIK",
    "GOLDEN DRAGON ELEKTRONIK",
    "CV SAMI JAYA",
    "HIBURAN BARU SINGKAWANG",
    "TOKO PUTRA ELEKTRONIK",
    "MAMASUKA",
    "NUANSA BERAU",
    "NUANSA IMAM BONJOL",
    "NUANSA MATOS SAMARINDA",
    "TOKO SURYA JAYA",
    "HOKKY OMEGA SAMPIT",
    "ADA SILIWANGI",
    "ADA SWALAYAN SETIABUDI",
    "ATLANTA MAJAPAHIT",
    "ATLANTA MATARAM",
    "CANDI ELEKTRONIC 310",
    "HERO HOUSEWARE",
    "PASIFIK PEKOJAN",
    "PIRANTI HOUSEWARE",
    "SURYA TIMUR",
    "WIJAYA ELEKTRONIK",
    "HARTONO SIDOARJO",
    "CANDI ELECTRONIC KARTASURA",
    "SURABAYA",
    "TUNJUNGAN PLAZA",
    "HARTONO BG JUNCTION",
    "HARTONO BUKIT DARMO",
    "HARTONO KERTAJAYA INDAH",
    "MEDIA ELEKTRONIK DENPASAR",
    "PERDANA MANYAR",
    "STEIN BUKIT DARMO",
    "STEIN KERTAJAYA",
    "SUPER MURAH SURABAYA 2",
    "TOENG MARKET JAKSA AGUNG SUPRAPTO",
    "UFO KERTAJAYA",
    "NUANSA ELEKTRONIK TANJUNG",
    "GLOBALMART BATULICIN",
    "TOKO AMAC",
    "NUANSA TENGGARONG",
    "CV OMEGA SEJAHTERA",
    "A TAKRIB LIFE STYLE",
    "ATAKRIB BHAYANGKARA",
    "FORTUNA",
    "PROGO JOGJA",
    "UFO YOGYAKARTA JOKTENG",
    "MANGGA DUA SQUARE",
    "PONDOK ELEKTRONIK",
    "HERO HOUSEWARE PURWOKERTO",
    "STEIN BANDUNG",
    "TIARA PALU",
    "YOGYA RIAU JUNCTION",
    "CANDI ELEKTRONIK"
]

total = len(stores)
output = []

print("🚀 Mulai scraping %d toko..." % total)
for i, name in enumerate(stores):
    print("⏳ [%d/%d] Scraping: %s" % (i + 1, total, name))
    res = search_place(name)
    output.append({'store': name, **(res or {})})
    if res:
        print("   ✅ Ditemukan: %s" % res['address'])
    else:
        print("   ⚠️  Tidak ditemukan")
    time.sleep(1)

with open('stores.json', 'w') as f:
    json.dump(output, f, indent=2)

print("\n✅ Selesai! Hasil disimpan di stores.json")
