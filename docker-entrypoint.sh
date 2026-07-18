#!/usr/bin/env bash
# Entrypoint container StomaCare: siapkan DB, jalankan migrasi, serve.
set -e
cd /app

# Pastikan file SQLite ada (dipakai bila DB_CONNECTION=sqlite; abaikan bila pgsql)
mkdir -p database
[ -f database/database.sqlite ] || touch database/database.sqlite

# Registrasi paket & migrasi (aman diulang tiap deploy)
php artisan package:discover --ansi || true
php artisan migrate --force || true

# Jalankan web server pada PORT yang diberikan platform (default 8080)
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
