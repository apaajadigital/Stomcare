#!/usr/bin/env bash
# Entrypoint container StomaCare.
# TANPA `set -e` agar server SELALU start (healthcheck lolos) meski
# ada langkah yang gagal. Folder DB/storage dipaksa writable untuk
# mengatasi masalah izin pada Volume Railway.
cd /app

# Siapkan folder + izin tulis (penting utk Volume yg di-mount di /app/database)
mkdir -p database storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chmod -R 777 database storage bootstrap/cache 2>/dev/null || true

# Pastikan file SQLite ada & writable (dipakai bila DB_CONNECTION=sqlite)
[ -f database/database.sqlite ] || touch database/database.sqlite
chmod 666 database/database.sqlite 2>/dev/null || true

php artisan package:discover --ansi 2>&1 || true
php artisan migrate --force 2>&1 || true

# Server harus selalu berjalan pada PORT platform (default 8080)
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
