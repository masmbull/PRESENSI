@echo off
cd /d "%~dp0"

rem --- HTTP buat laptop (localhost: GPS jalan karena secure context) ---
start "Laravel HTTP 8000" /min php artisan serve --host=0.0.0.0 --port=8000

rem --- HTTPS buat HP (GPS butuh secure context; artisan serve gak bisa TLS) ---
rem sertifikat dari ..\certs (mkcert.py). Kalau IP laptop berubah: python mkcert.py
set PYPATH=python
if exist "..\.venv\Scripts\python.exe" set PYPATH=..\.venv\Scripts\python.exe
start "Laravel HTTPS 8444" /min %PYPATH% tls-proxy.py

echo.
echo   Laptop : http://localhost:8000/
echo   HP     : https://^<ip-laptop^>:8444/     ^(warning sertifikat = tetap lanjut^)
echo   Cek IP : ipconfig
echo.
echo   Tutup server: tutup window "Laravel HTTP 8000" dan "Laravel HTTPS 8444"
