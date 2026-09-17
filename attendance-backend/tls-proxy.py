"""Proxy HTTPS buat Laravel — PHP built-in server (artisan serve) gak support TLS.

HP butuh HTTPS biar browser izinin GPS (secure context). Proxy ini:
    HP/laptop --TLS 8444--> proxy ini --HTTP--> artisan serve (127.0.0.1:8000)

Sertifikat dipakai dari ../certs/server.pem + key.pem (hasil mkcert.py).
Jalanin ulang `python mkcert.py` di folder induk kalau IP laptop berubah.

Stdlib doang, tanpa dependency.
"""
import asyncio
import ssl
import sys
from pathlib import Path

BASE = Path(__file__).resolve().parent
CERT = BASE.parent / "certs" / "server.pem"
KEY = BASE.parent / "certs" / "key.pem"

LISTEN_HOST = "0.0.0.0"
LISTEN_PORT = 8444
TARGET_HOST = "127.0.0.1"
TARGET_PORT = 8000


async def _pipe(reader: asyncio.StreamReader, writer: asyncio.StreamWriter) -> None:
    try:
        while True:
            data = await reader.read(65536)
            if not data:
                break
            writer.write(data)
            await writer.drain()
    except Exception:
        pass
    finally:
        try:
            writer.close()
            await writer.wait_closed()
        except Exception:
            pass


async def _handle(client_reader: asyncio.StreamReader, client_writer: asyncio.StreamWriter) -> None:
    try:
        upstream_reader, upstream_writer = await asyncio.open_connection(TARGET_HOST, TARGET_PORT)
    except Exception:
        client_writer.close()
        return
    await asyncio.gather(
        _pipe(client_reader, upstream_writer),
        _pipe(upstream_reader, client_writer),
    )


async def main() -> None:
    if not CERT.exists() or not KEY.exists():
        print("cert gak ketemu:", CERT)
        print("jalanin dulu: python mkcert.py  (di folder induk)")
        sys.exit(1)

    ctx = ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER)
    ctx.load_cert_chain(CERT, KEY)

    server = await asyncio.start_server(
        _handle, LISTEN_HOST, LISTEN_PORT, ssl=ctx,
    )
    print(f"https://{LISTEN_HOST}:{LISTEN_PORT}  ->  http://{TARGET_HOST}:{TARGET_PORT}")
    print(f"cert: {CERT}")
    print("buka dari HP: https://<ip-laptop>:8444/   (warning sertifikat = tetap lanjut)")
    async with server:
        await server.serve_forever()


if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        print("\nmati")