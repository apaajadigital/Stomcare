#!/usr/bin/env bash
# Entrypoint FrankenPHP: migrasi lalu jalankan server pada PORT platform.
set -e
cd /app

mkdir -p database
[ -f database/database.sqlite ] || touch database/database.sqlite

php artisan package:discover --ansi || true
php artisan migrate --force || true

# FrankenPHP menyajikan document root public/ (routing index.php otomatis).
# Dengarkan pada PORT dari platform (Railway/Render), default 8080.
exec frankenphp php-server --listen ":${PORT:-8080}" --root public/
