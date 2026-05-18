# apiperjanjiankerjasama-main

API backend untuk aplikasi Perjanjian Kinerja.

## Prasyarat
- PHP tersedia di `PATH`
- MySQL sesuai konfigurasi di `api/2026/library/config.php` (untuk mode local)

## Menjalankan API

### macOS / Linux
- Local:
```bash
./run-local.sh
```
- Prod mode:
```bash
./run-prod.sh
```

### Windows (CMD)
- Local:
```bat
run-local.bat
```
- Prod mode:
```bat
run-prod.bat
```

## Detail Mode
- `local`:
  - Host default: `127.0.0.1`
  - Port default: `8081`
  - Root API: `api/2026`
- `prod`:
  - Host default: `0.0.0.0` (wrapper `run-prod.sh` / `run-prod.bat`)
  - Port default: `8081`
  - Root API: `api`

## Override Host/Port

### macOS / Linux
```bash
API_HOST=127.0.0.1 API_PORT=8091 ./run-local.sh
```

### Windows (CMD)
```bat
set API_HOST=127.0.0.1
set API_PORT=8091
run-local.bat
```
